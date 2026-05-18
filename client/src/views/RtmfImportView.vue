<script setup lang="ts">
import { ref } from "vue";
import { RouterLink } from "vue-router";
import { Upload, CheckCircle2, AlertCircle, BookOpen, Terminal, FileJson, ListChecks } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { importRtmfCatalog } from "@/api/rtmf";
import type { RtmfImportResult } from "@/types";

const activeTab = ref<"run" | "queue" | "manual">("run");

const raw       = ref("");
const loading   = ref(false);
const results   = ref<RtmfImportResult[]>([]);
const error     = ref<string | null>(null);
const jsonError = ref<string | null>(null);

function validateJson() {
  jsonError.value = null;
  if (!raw.value.trim()) return;
  try {
    JSON.parse(raw.value);
  } catch (e) {
    jsonError.value = (e as Error).message;
  }
}

async function runImport() {
  jsonError.value = null;
  error.value = null;
  results.value = [];

  let payload: unknown;
  try {
    payload = JSON.parse(raw.value);
  } catch (e) {
    jsonError.value = (e as Error).message;
    return;
  }

  loading.value = true;
  try {
    const res = await importRtmfCatalog(payload);
    results.value = res.data;
  } catch (e) {
    error.value = (e as Error).message ?? "Import failed.";
  } finally {
    loading.value = false;
  }
}

const EXAMPLE = JSON.stringify({
  module:     { code: "ADN", name: "Aduan", sort_order: 20 },
  sub_module: { code: "DA", name: "Daftar Aduan", sort_order: 10 },
  frontends: [
    {
      spec_id:                 "ADN-DA-01",
      tab_code:                "ADN-DA-01",
      title:                   "Daftar Aduan",
      vue_path:                "pages/pengurusan-aduan/daftar-aduan/index.vue",
      business_requirement:    "Sistem membenarkan pengguna mendaftar aduan baharu.",
      stakeholder_requirement: "IC divalidasi melalui JPN. Poskod mencari negeri/daerah secara automatik.",
      description:             "Borang pendaftaran aduan asnaf zakat.",
      actors:                  ["Pengguna", "Orang Awam"],
      items: [
        {
          id_fr:           "ADN-DA-01-FR-001",
          type:            "Text",
          label:           "Nama Penuh",
          mandatory:       true,
          screen_name:     "Maklumat Individu",
          table_fieldname: "adn_aduan_asnaf.nama_penuh",
          condition:       null,
          validation:      "required|max:100|uppercase",
          status:          "missing",
        },
      ],
      api_endpoints: [
        { method: "GET",  endpoint: "/kod/public",                         description: "Get lookup codes" },
        { method: "GET",  endpoint: "/aduan/daftar-aduan/aduan-asnaf/public/validate", description: "Validate IC via JPN" },
        { method: "POST", endpoint: "/aduan/daftar-aduan/aduan-asnaf",     description: "Submit aduan asnaf" },
      ],
    },
  ],
}, null, 2);
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div>
        <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
          <RouterLink to="/admin/rtmf/frontends" class="transition-colors hover:text-violet-600">Page Catalog</RouterLink>
          <span class="text-slate-300">/</span>
          <span class="text-slate-700">Import</span>
        </nav>
        <h1 class="page-title">Catalog Import</h1>
        <p class="mt-1 text-sm text-slate-500">
          Paste a JSON payload extracted from
          <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">nas-frontend</code> /
          <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">nas-backend</code>
          to bulk-populate catalog entries.
        </p>
      </div>

      <!-- Tab bar -->
      <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-0 overflow-x-auto">
          <button
            class="flex shrink-0 items-center gap-2 border-b-2 px-5 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === 'run' ? 'border-violet-600 text-violet-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'run'"
          >
            <Upload class="h-4 w-4" />
            Run Payload
          </button>
          <button
            class="flex shrink-0 items-center gap-2 border-b-2 px-5 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === 'queue' ? 'border-violet-600 text-violet-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'queue'"
          >
            <ListChecks class="h-4 w-4" />
            Module Queue
          </button>
          <button
            class="flex shrink-0 items-center gap-2 border-b-2 px-5 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === 'manual' ? 'border-violet-600 text-violet-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'manual'"
          >
            <BookOpen class="h-4 w-4" />
            User Manual
          </button>
        </nav>
      </div>

      <!-- ── Tab: Run Payload ─────────────────────────────────────────────── -->
      <div v-show="activeTab === 'run'" class="space-y-4">

        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="space-y-4 p-4">

            <!-- Textarea -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="text-xs font-medium text-slate-600">JSON payload</label>
                <button
                  class="text-xs text-violet-500 hover:text-violet-700 hover:underline"
                  @click="raw = EXAMPLE"
                >Load example</button>
              </div>
              <textarea
                v-model="raw"
                rows="22"
                spellcheck="false"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 p-3 font-mono text-xs leading-relaxed text-slate-800 shadow-sm transition focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-100"
                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': jsonError }"
                placeholder='{ "module": { "code": "..." }, "sub_module": { ... }, "frontends": [ ... ] }'
                @blur="validateJson"
              />
              <p v-if="jsonError" class="text-xs text-red-600">{{ jsonError }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
              <button
                :disabled="loading || !raw.trim() || !!jsonError"
                class="flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-violet-700 disabled:opacity-50"
                @click="runImport"
              >
                <Upload class="h-4 w-4" :class="loading ? 'animate-pulse' : ''" />
                {{ loading ? 'Importing…' : 'Run Import' }}
              </button>
              <button
                v-if="raw"
                class="text-sm text-slate-400 hover:text-slate-600"
                @click="raw = ''; results = []; error = null; jsonError = null"
              >Clear</button>
            </div>
          </div>
        </article>

        <!-- Error -->
        <div v-if="error" class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
          <div>
            <p class="font-semibold">Import failed</p>
            <p class="mt-0.5 text-xs">{{ error }}</p>
          </div>
        </div>

        <!-- Result -->
        <article
          v-for="(result, idx) in results"
          :key="`${result.module}-${result.subModule}-${idx}`"
          class="rounded-lg border border-emerald-200 bg-emerald-50 shadow-sm"
        >
          <div class="flex items-center gap-2 border-b border-emerald-200 px-4 py-2.5">
            <CheckCircle2 class="h-4 w-4 text-emerald-600" />
            <h2 class="text-sm font-semibold text-emerald-800">Import successful</h2>
          </div>
          <div class="space-y-3 p-4">
            <div class="flex flex-wrap gap-6 text-sm">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Module</p>
                <p class="mt-0.5 font-mono text-xs text-emerald-800">{{ result.module }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Sub-module</p>
                <p class="mt-0.5 font-mono text-xs text-emerald-800">{{ result.subModule }}</p>
              </div>
            </div>
            <table class="w-full text-xs">
              <thead>
                <tr class="border-b border-emerald-200 text-left text-xs font-semibold uppercase tracking-wider text-emerald-700">
                  <th class="pb-1.5 pr-6">Spec ID</th>
                  <th class="pb-1.5 pr-6">Action</th>
                  <th class="pb-1.5 pr-6">FR Items</th>
                  <th class="pb-1.5">API Endpoints</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-emerald-100">
                <tr v-for="fe in result.frontends" :key="fe.specId">
                  <td class="py-1.5 pr-6 font-mono text-emerald-900">{{ fe.specId }}</td>
                  <td class="py-1.5 pr-6">
                    <span
                      class="rounded px-1.5 py-0.5 text-xs font-medium"
                      :class="fe.action === 'created' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                    >{{ fe.action }}</span>
                  </td>
                  <td class="py-1.5 pr-6 text-emerald-700">{{ fe.items }}</td>
                  <td class="py-1.5 text-emerald-700">{{ fe.endpoints }}</td>
                </tr>
              </tbody>
            </table>
            <RouterLink
              to="/admin/rtmf/frontends"
              class="text-xs font-medium text-emerald-700 hover:underline"
            >View in Page Catalog →</RouterLink>
          </div>
        </article>

      </div>

      <!-- ── Tab: Module Queue ─────────────────────────────────────────────── -->
      <div v-show="activeTab === 'queue'" class="space-y-4">
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <ListChecks class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">Module queue</h2>
          </div>
          <div class="p-4">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                  <th class="pb-2 pr-6">Code</th>
                  <th class="pb-2 pr-6">Module</th>
                  <th class="pb-2 pr-6">Status</th>
                  <th class="pb-2">Last Seeded</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 text-xs">
                <tr v-for="mod in [
                  { code: 'PRF', name: 'Profiling',  done: true,  seededAt: '2026-05-13' },
                  { code: 'ADN', name: 'Aduan',      done: true,  seededAt: '2026-05-13' },
                  { code: 'BNT', name: 'Bantuan',    done: false, seededAt: null },
                  { code: 'AGH', name: 'Agihan',     done: false, seededAt: null },
                ]" :key="mod.code">
                  <td class="py-2 pr-6 font-mono font-semibold text-slate-700">{{ mod.code }}</td>
                  <td class="py-2 pr-6 text-slate-600">{{ mod.name }}</td>
                  <td class="py-2 pr-6">
                    <span
                      class="rounded px-2 py-0.5 text-xs font-medium"
                      :class="mod.done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                    >{{ mod.done ? '✓ Done' : 'Pending' }}</span>
                  </td>
                  <td class="py-2 text-slate-400">{{ mod.seededAt ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>
      </div>

      <!-- ── Tab: User Manual ────────────────────────────────────────────── -->
      <div v-show="activeTab === 'manual'" class="space-y-4">

        <!-- How it works -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <Upload class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">How it works</h2>
          </div>
          <div class="p-4 text-sm text-slate-600">
            <ol class="list-decimal space-y-2 pl-5">
              <li>You ask Claude Code locally: <em class="text-slate-700">"seed the RTMF catalog for Bantuan module"</em></li>
              <li>Claude reads the Vue components in <code class="rounded bg-slate-100 px-1 text-xs">nas-frontend</code> and DTOs in <code class="rounded bg-slate-100 px-1 text-xs">nas-backend</code> — <strong class="text-slate-700">source code never leaves your machine</strong></li>
              <li>Claude produces a structured JSON payload (field labels, types, validations — no raw source code)</li>
              <li>You paste that JSON in the <strong class="text-slate-700">Run Payload</strong> tab and click <strong class="text-slate-700">Run Import</strong></li>
              <li>The server saves the catalog entries and FR line items. Safe to run multiple times — same payload updates existing records, never duplicates</li>
            </ol>
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
              <strong class="text-amber-800">Idempotent:</strong> Entries are matched by <code class="rounded bg-amber-100 px-1">spec_id</code>. Re-running the same payload <strong>updates</strong> the existing entry and replaces its FR items — no duplicates created.
            </div>
          </div>
        </article>

        <!-- Payload structure -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <FileJson class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">Payload structure</h2>
          </div>
          <div class="p-4">
            <pre class="overflow-x-auto rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-700">{{
`{
  "module": {
    "code":       "ADN",          // short uppercase, max 16 chars
    "name":       "Aduan",
    "sort_order": 20
  },
  "sub_module": {
    "code":       "DA",           // max 32 chars
    "name":       "Daftar Aduan",
    "sort_order": 10
  },
  "frontends": [
    {
      "spec_id":                 "ADN-DA-01",      // unique — used as match key
      "tab_code":                "ADN-DA-01",
      "title":                   "Daftar Aduan",
      "vue_path":                "pages/pengurusan-aduan/daftar-aduan/index.vue",
      "business_requirement":    "...",
      "stakeholder_requirement": "...",
      "description":             "...",
      "actors":                  ["Pengguna", "Orang Awam"],
      "items": [
        {
          "id_fr":           "ADN-DA-01-FR-001",
          "type":            "Text",               // Text | Select | Email | Textarea | etc.
          "label":           "Nama Penuh",
          "mandatory":       true,
          "screen_name":     "Maklumat Individu",  // section grouping
          "table_fieldname": "adn_aduan_asnaf.nama_penuh",
          "condition":       null,
          "validation":      "required|max:100|uppercase",
          "status":          "missing"             // missing | partial | implemented
        }
      ],
      "api_endpoints": [
        {
          "method":      "GET",                    // GET | POST | PUT | PATCH | DELETE
          "endpoint":    "/kod/public",
          "description": "Get lookup codes"
        },
        {
          "method":      "POST",
          "endpoint":    "/aduan/daftar-aduan/aduan-asnaf",
          "description": "Submit aduan asnaf"
        }
      ]
    }
  ]
}`
            }}</pre>
            <div class="mt-3 space-y-1 text-xs text-slate-500">
              <p><strong>spec_id naming:</strong> <code class="rounded bg-slate-100 px-1">{MOD}-{SUB}-{NN}</code> e.g. <code class="rounded bg-slate-100 px-1">ADN-DA-01</code> · Variants: <code class="rounded bg-slate-100 px-1">ADN-DA-01_01</code>, <code class="rounded bg-slate-100 px-1">ADN-DA-01_02</code></p>
              <p><strong>One sub-module per call.</strong> To import multiple sub-modules, make one POST per sub-module.</p>
            </div>
          </div>
        </article>

        <!-- Using with Claude Code -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <Terminal class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">Using with Claude Code (local)</h2>
          </div>
          <div class="space-y-4 p-4 text-sm text-slate-600">
            <div>
              <p class="mb-2">Tell Claude Code in your local terminal:</p>
              <div class="rounded-lg bg-slate-900 px-4 py-3">
                <code class="text-xs text-emerald-300">"seed the RTMF catalog for Bantuan module"</code>
              </div>
              <p class="mt-2">Claude reads the source files, produces the JSON payload, and either gives it to you to paste here or POSTs it directly via curl.</p>
            </div>
            <div>
              <p class="mb-2">Or via curl (for CI / automated runs):</p>
              <div class="rounded-lg bg-slate-900 px-4 py-3">
                <pre class="text-xs leading-relaxed text-emerald-300">{{
`curl -X POST https://your-server.com/api/rtmf-frontends/import \\
  -H "Authorization: Bearer {your-sanctum-token}" \\
  -H "Content-Type: application/json" \\
  -d @payload.json`
                }}</pre>
              </div>
              <p class="mt-2 text-xs text-slate-500">The Bearer token is your OrbitRTMF login session token. Generate one from the API settings or use your active session cookie.</p>
            </div>
          </div>
        </article>


      </div>
    </div>
  </AdminLayout>
</template>
