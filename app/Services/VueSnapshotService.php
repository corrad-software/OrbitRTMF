<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class VueSnapshotService
{
    private string $nasFrontendRoot;

    public function __construct(?string $root = null)
    {
        $this->nasFrontendRoot = $root ?? base_path('../nas-frontend');
    }

    /**
     * Render a static HTML snapshot for a Vue SFC at the given relative path.
     *
     * @return array{html: ?string, status: string}
     */
    public function capture(?string $vuePath): array
    {
        if (! $vuePath) {
            return ['html' => null, 'status' => 'not_found'];
        }

        $root = realpath($this->nasFrontendRoot);
        $abs = realpath($root . '/' . ltrim($vuePath, '/'));

        if (! $root || ! $abs || ! str_starts_with($abs, $root) || ! is_file($abs) || ! is_readable($abs)) {
            return ['html' => null, 'status' => 'not_found'];
        }

        try {
            $content = (string) File::get($abs);
            $template = $this->extractBlock($content, 'template');
            $styles = $this->extractAllBlocks($content, 'style');

            if ($template === null) {
                return ['html' => null, 'status' => 'error'];
            }

            $transformed = $this->transformTemplate($template);
            $html = $this->assemble($transformed, $styles, $vuePath);

            return ['html' => $html, 'status' => 'ok'];
        } catch (\Throwable $e) {
            return ['html' => null, 'status' => 'error'];
        }
    }

    /**
     * Extract metadata for a Vue SFC: line_count, file_size_kb, shared_components.
     *
     * @return array{line_count: ?int, file_size_kb: ?int, shared_components: ?array<int, string>}
     */
    public function extractMetadata(?string $vuePath): array
    {
        if (! $vuePath) {
            return ['line_count' => null, 'file_size_kb' => null, 'shared_components' => null];
        }

        $root = realpath($this->nasFrontendRoot);
        $abs = realpath($root . '/' . ltrim($vuePath, '/'));

        if (! $root || ! $abs || ! str_starts_with($abs, $root) || ! is_file($abs) || ! is_readable($abs)) {
            return ['line_count' => null, 'file_size_kb' => null, 'shared_components' => null];
        }

        $content = (string) File::get($abs);

        return [
            'line_count' => substr_count($content, "\n") + 1,
            'file_size_kb' => (int) round(strlen($content) / 1024),
            'shared_components' => $this->extractSharedComponents($content),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractSharedComponents(string $content): array
    {
        $names = [];

        if (preg_match_all('/import\s+([A-Z][A-Za-z0-9_]+)\s+from\s+[\'"][^\'"]+[\'"]/', $content, $m)) {
            $names = array_merge($names, $m[1]);
        }

        if (preg_match_all('/import\s*\{([^}]+)\}\s*from\s*[\'"][^\'"]+[\'"]/', $content, $m)) {
            foreach ($m[1] as $group) {
                foreach (explode(',', $group) as $n) {
                    $n = trim($n);
                    if ($n !== '' && preg_match('/^[A-Z][A-Za-z0-9_]+$/', $n)) {
                        $names[] = $n;
                    }
                }
            }
        }

        if (preg_match_all('/<([A-Z][A-Za-z0-9]+)(\s|\/|>)/', $content, $m)) {
            $names = array_merge($names, $m[1]);
        }

        $names = array_values(array_unique($names));
        sort($names);

        return array_slice($names, 0, 60);
    }

    /**
     * Extract the inner content of the first <tag …>…</tag> block found.
     */
    private function extractBlock(string $source, string $tag): ?string
    {
        if (! preg_match('/<' . $tag . '\b[^>]*>/', $source, $openMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $openStart = $openMatch[0][1];
        $openEnd = $openStart + strlen($openMatch[0][0]);

        // Walk forward counting nested openings until the matching close.
        $depth = 1;
        $pos = $openEnd;
        $len = strlen($source);
        $openRe = '/<' . $tag . '\b[^>]*>/i';
        $closeRe = '/<\/' . $tag . '\s*>/i';

        while ($pos < $len && $depth > 0) {
            $nextOpen = preg_match($openRe, $source, $mo, PREG_OFFSET_CAPTURE, $pos) ? $mo[0][1] : PHP_INT_MAX;
            $nextClose = preg_match($closeRe, $source, $mc, PREG_OFFSET_CAPTURE, $pos) ? $mc[0][1] : PHP_INT_MAX;

            if ($nextClose === PHP_INT_MAX) {
                return null;
            }
            if ($nextOpen < $nextClose) {
                $depth++;
                $pos = $nextOpen + strlen($mo[0][0]);
            } else {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $openEnd, $nextClose - $openEnd);
                }
                $pos = $nextClose + strlen($mc[0][0]);
            }
        }

        return null;
    }

    /**
     * Extract all <style …>…</style> blocks (including wrapping tags), preserving order.
     *
     * @return array<int, string>
     */
    private function extractAllBlocks(string $source, string $tag): array
    {
        $blocks = [];
        $offset = 0;
        while (preg_match('/<' . $tag . '\b[^>]*>/i', $source, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $openStart = $m[0][1];
            $openEnd = $openStart + strlen($m[0][0]);
            if (! preg_match('/<\/' . $tag . '\s*>/i', $source, $cm, PREG_OFFSET_CAPTURE, $openEnd)) {
                break;
            }
            $closeEnd = $cm[0][1] + strlen($cm[0][0]);
            $blocks[] = substr($source, $openStart, $closeEnd - $openStart);
            $offset = $closeEnd;
        }

        return $blocks;
    }

    /**
     * Transform a Vue <template> body into sanitized static HTML.
     */
    private function transformTemplate(string $html): string
    {
        // 1. Strip any nested <script> tags.
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;

        // 2. Replace {{ expr }} interpolations with visible placeholders BEFORE tag rewriting.
        $html = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', function ($m) {
            $expr = htmlspecialchars(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return '<span class="rtmf-mustache">{{ ' . $expr . ' }}</span>';
        }, $html) ?? $html;

        // 3. Remove Vue directive / binding attributes.
        //    Handles: v-xxx="…", :prop="…", @event="…", #slot="…"
        //    Also bare v-else, v-pre with no value.
        $html = preg_replace('/\s+(?:v-[a-z][\w:.-]*|:[^\s=<>"\']+|@[^\s=<>"\']+|#[^\s=<>"\']+)(?:=(?:"[^"]*"|\'[^\']*\'))?/i', '', $html) ?? $html;

        // 4. Rewrite custom component tags → labeled placeholder <div>s.
        $html = preg_replace_callback(
            '/<(\/?)([A-Za-z][A-Za-z0-9]*(?:-[A-Za-z0-9]+)*)\b([^>]*?)(\/?)>/',
            function ($m) {
                $close = $m[1] === '/';
                $name = $m[2];
                $rest = $m[3];
                $selfClose = $m[4] === '/';

                if ($this->isNativeHtml($name)) {
                    return $m[0];
                }

                if ($close) {
                    return '</div>';
                }

                $escapedName = htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $open = '<div class="rtmf-component" data-component="' . $escapedName . '">';

                return $selfClose ? $open . '</div>' : $open;
            },
            $html
        ) ?? $html;

        return $html;
    }

    private function isNativeHtml(string $tag): bool
    {
        static $native = [
            'html', 'head', 'body', 'title', 'meta', 'link', 'style', 'base',
            'div', 'span', 'p', 'a', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'br', 'pre', 'code',
            'blockquote', 'em', 'strong', 'b', 'i', 'u', 's', 'small', 'sub', 'sup',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
            'form', 'input', 'textarea', 'select', 'option', 'optgroup', 'button', 'label',
            'fieldset', 'legend', 'datalist', 'output', 'progress', 'meter',
            'img', 'picture', 'source', 'video', 'audio', 'track', 'canvas', 'svg', 'path',
            'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse', 'g', 'use', 'defs',
            'symbol', 'text', 'tspan', 'foreignobject', 'iframe', 'embed', 'object', 'param',
            'nav', 'header', 'footer', 'main', 'section', 'article', 'aside', 'figure',
            'figcaption', 'details', 'summary', 'dialog', 'menu',
            'abbr', 'address', 'cite', 'q', 'kbd', 'samp', 'var', 'time', 'mark', 'ruby', 'rt', 'rp',
            'bdi', 'bdo', 'wbr', 'data', 'del', 'ins',
            'template', 'slot',
        ];

        return in_array(strtolower($tag), $native, true);
    }

    private function assemble(string $body, array $styleBlocks, string $vuePath): string
    {
        $sfcStyles = implode("\n", $styleBlocks);
        $banner = htmlspecialchars($vuePath, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rtmfCss = <<<'CSS'
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: #0f172a; }
            .rtmf-banner { background: #1e293b; color: #e2e8f0; font-family: ui-monospace, monospace; font-size: 11px; padding: 6px 12px; margin: -16px -16px 16px; }
            .rtmf-component { position: relative; border: 1px dashed #c4b5fd; border-radius: 6px; padding: 18px 10px 10px; margin: 6px 0; background: #faf5ff; }
            .rtmf-component::before { content: attr(data-component); position: absolute; top: 0; left: 0; transform: translateY(-50%); background: #7c3aed; color: white; font-family: ui-monospace, monospace; font-size: 10px; padding: 1px 6px; border-radius: 4px; }
            .rtmf-mustache { color: #7c3aed; background: #f5f3ff; padding: 0 4px; border-radius: 3px; font-family: ui-monospace, monospace; font-size: 0.9em; }
        CSS;

        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <script src="https://cdn.tailwindcss.com"></script>
                <style>{$rtmfCss}</style>
                {$sfcStyles}
            </head>
            <body class="p-4">
                <div class="rtmf-banner">RTMF Snapshot · {$banner}</div>
                {$body}
            </body>
            </html>
            HTML;
    }
}
