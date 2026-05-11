const raw = import.meta.env.VITE_API_BASE_URL as string | undefined;

function trimTrailingSlash(s: string): string {
  return s.replace(/\/$/, "");
}

/**
 * Blank / omitted = browser uses same-origin (Coolify nginx + Laravel in one container).
 * Split SPA/API domains: bake `VITE_API_BASE_URL` at build via Dockerfile `SPA_API_BASE_URL`.
 * Local `npm run dev`: undefined empty → Laravel default origin.
 */
export const API_BASE_URL = ((): string => {
  if (raw === undefined || raw === null || String(raw).trim() === "") {
    return import.meta.env.DEV ? "http://localhost:8000" : "";
  }
  return trimTrailingSlash(String(raw));
})();
