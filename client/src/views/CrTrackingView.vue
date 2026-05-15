<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { GitPullRequest, AlertCircle, Search, RefreshCw, ChevronDown, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  fetchCrLog, fetchCrSummary, fetchCrTrend, fetchCrFilters,
  type CrLogRow, type CrSummaryResponse, type CrTrendRow, type CrFiltersResponse,
} from "@/api/cr";

type TabId = "log" | "summary" | "trend";
const activeTab = ref<TabId>("log");
const tabs: { id: TabId; label: string }[] = [
  { id: "log",     label: "CR Log" },
  { id: "summary", label: "Ringkasan" },
  { id: "trend",   label: "Trend" },
];

// ── Loading state ─────────────────────────────────────────────────────────
const loaded = ref<Record<TabId, boolean>>({ log: false, summary: false, trend: false });
const loading = ref<Record<TabId, boolean>>({ log: false, summary: false, trend: false });
const errors = ref<Record<TabId, string | null>>({ log: null, summary: null, trend: null });

// ── Filter options (loaded once) ──────────────────────────────────────────
const filterOpts = ref<CrFiltersResponse>({ projek: [], kategori: [], priority: [], by: [], assigned: [] });

// ── Log state ─────────────────────────────────────────────────────────────
const logRows = ref<CrLogRow[]>([]);
const logTotal = ref(0);
const logTotalPages = ref(1);
const logPage = ref(1);
const logSearch = ref("");
const openFilter = ref<string | null>(null);

const filters = ref({
  status:   [] as string[],
  priority: [] as string[],
  projek:   [] as string[],
  kategori: [] as string[],
  by:       [] as string[],
  assigned: [] as string[],
});

// Date segment: null = all time, '30'/'60'/'90' = last N days, 'custom' = manual range
const dateSegment = ref<string | null>(null);
const customDateFrom = ref("");
const customDateTo   = ref("");

const STATUS_OPTIONS = ['new', 'feedback', 'acknowledged', 'confirmed', 'assigned', 'resolved'] as const;

const activeFilterCount = computed(() =>
  Object.values(filters.value).reduce((n, arr) => n + arr.length, 0)
  + (dateSegment.value ? 1 : 0)
);

function computedDateRange(): { dateFrom?: string; dateTo?: string } {
  if (!dateSegment.value) return {};
  if (dateSegment.value === 'custom') {
    return {
      dateFrom: customDateFrom.value || undefined,
      dateTo:   customDateTo.value   || undefined,
    };
  }
  const days = parseInt(dateSegment.value, 10);
  const from = new Date();
  from.setDate(from.getDate() - days);
  return { dateFrom: from.toISOString().slice(0, 10) };
}

function setDateSegment(seg: string) {
  if (dateSegment.value === seg) {
    dateSegment.value = null;
  } else {
    dateSegment.value = seg;
    if (seg !== 'custom') {
      customDateFrom.value = "";
      customDateTo.value   = "";
    }
  }
  logPage.value = 1;
  loadLog();
}

function clearAllFilters() {
  filters.value = { status: [], priority: [], projek: [], kategori: [], by: [], assigned: [] };
  dateSegment.value = null;
  customDateFrom.value = "";
  customDateTo.value   = "";
  logPage.value = 1;
  loadLog();
}

const paginationPages = computed((): (number | '...')[] => {
  const total = logTotalPages.value;
  const cur = logPage.value;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages: (number | '...')[] = [1];
  if (cur > 3) pages.push('...');
  for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i);
  if (cur < total - 2) pages.push('...');
  pages.push(total);
  return pages;
});

// ── Summary / Trend state ─────────────────────────────────────────────────
const summary = ref<CrSummaryResponse | null>(null);
const trendRows = ref<CrTrendRow[]>([]);
const trendDays = ref(14);

// ── Loaders ───────────────────────────────────────────────────────────────
async function loadLog() {
  loading.value.log = true;
  errors.value.log = null;
  try {
    const dateRange = computedDateRange();
    const r = await fetchCrLog({
      q:        logSearch.value,
      limit:    50,
      page:     logPage.value,
      status:   filters.value.status,
      priority: filters.value.priority,
      projek:   filters.value.projek,
      kategori: filters.value.kategori,
      by:       filters.value.by,
      assigned: filters.value.assigned,
      ...dateRange,
    });
    logRows.value = r.data;
    logTotal.value = (r.meta?.total as number) ?? r.data.length;
    logTotalPages.value = (r.meta?.totalPages as number) ?? 1;
    loaded.value.log = true;
  } catch (e) {
    errors.value.log = (e as Error).message;
  } finally {
    loading.value.log = false;
  }
}

async function loadSummary() {
  loading.value.summary = true;
  errors.value.summary = null;
  try {
    const r = await fetchCrSummary();
    summary.value = r.data;
    loaded.value.summary = true;
  } catch (e) {
    errors.value.summary = (e as Error).message;
  } finally {
    loading.value.summary = false;
  }
}

async function loadTrend() {
  loading.value.trend = true;
  errors.value.trend = null;
  try {
    const r = await fetchCrTrend(trendDays.value);
    trendRows.value = r.data;
    loaded.value.trend = true;
  } catch (e) {
    errors.value.trend = (e as Error).message;
  } finally {
    loading.value.trend = false;
  }
}

watch(activeTab, (t) => { if (!loaded.value[t]) ensureLoaded(t); });

function ensureLoaded(t: TabId) {
  if (t === "log")     return loadLog();
  if (t === "summary") return loadSummary();
  if (t === "trend")   return loadTrend();
}

function refreshCurrent() {
  const t = activeTab.value;
  if (t === "log")          loadLog();
  else if (t === "summary") loadSummary();
  else if (t === "trend")   loadTrend();
}

let searchTimer: number | null = null;
watch(logSearch, () => {
  if (searchTimer) clearTimeout(searchTimer);
  logPage.value = 1;
  searchTimer = window.setTimeout(loadLog, 300);
});

watch(filters, () => { logPage.value = 1; loadLog(); }, { deep: true });

watch([customDateFrom, customDateTo], () => {
  if (dateSegment.value === 'custom') { logPage.value = 1; loadLog(); }
});

const _closeFilter = () => { openFilter.value = null; };
onMounted(() => {
  fetchCrFilters().then(r => { filterOpts.value = r.data; }).catch(() => {});
  loadLog();
  document.addEventListener('click', _closeFilter);
});
onUnmounted(() => document.removeEventListener('click', _closeFilter));

// ── SVG chart helpers ─────────────────────────────────────────────────────
const chartW = 600;
const chartH = 160;
const chartPad = { top: 10, right: 20, bottom: 28, left: 36 };
const innerW = chartW - chartPad.left - chartPad.right;
const innerH = chartH - chartPad.top - chartPad.bottom;
const maxBaki = computed(() => Math.max(1, ...trendRows.value.map(r => r.baki)));
function cx(i: number, n: number) { return chartPad.left + (n <= 1 ? 0 : (i / (n - 1)) * innerW); }
function cyBaki(v: number) { return chartPad.top + innerH - (v / maxBaki.value) * innerH; }
function cyResolved(v: number) { return chartPad.top + innerH - Math.min(1, v / maxBaki.value * 5) * innerH; }
const bakiPolyline = computed(() =>
  trendRows.value.map((r, i) => `${cx(i, trendRows.value.length)},${cyBaki(r.baki)}`).join(" "));
const resolvedPolyline = computed(() =>
  trendRows.value.map((r, i) => `${cx(i, trendRows.value.length)},${cyResolved(r.resolved)}`).join(" "));
const bakiArea = computed(() => {
  const n = trendRows.value.length;
  if (n === 0) return "";
  const pts = trendRows.value.map((r, i) => `${cx(i, n)},${cyBaki(r.baki)}`).join(" ");
  return `${chartPad.left},${chartPad.top + innerH} ${pts} ${cx(n - 1, n)},${chartPad.top + innerH}`;
});

// ── Style helpers ─────────────────────────────────────────────────────────
function statusClass(status: string) {
  const s = (status || "").toLowerCase();
  if (s === "resolved" || s === "closed") return "bg-emerald-100 text-emerald-700";
  if (s === "feedback")  return "bg-amber-100 text-amber-700";
  if (s === "assigned")  return "bg-indigo-100 text-indigo-700";
  return "bg-blue-100 text-blue-700";
}
function priorityClass(p: string) {
  if (p === "urgent" || p === "immediate") return "bg-rose-100 text-rose-700";
  if (p === "high")   return "bg-orange-100 text-orange-700";
  return "bg-slate-100 text-slate-500";
}
function resolveRateClass(v: number) {
  if (v >= 70) return "text-emerald-600 font-semibold";
  if (v >= 40) return "text-amber-600 font-semibold";
  return "text-rose-600 font-semibold";
}
function trenClass(tren: string) {
  if (tren.startsWith("↑")) return "text-rose-600 font-semibold";
  if (tren.startsWith("↓")) return "text-emerald-600 font-semibold";
  return "text-slate-500";
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h1 class="page-title">CR Tracking</h1>
          <p class="mt-0.5 text-sm text-slate-500">Senarai Change Request — live dari MantisBT</p>
        </div>
        <button
          @click="refreshCurrent"
          :disabled="loading[activeTab]"
          class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 disabled:opacity-50"
        >
          <RefreshCw class="h-3.5 w-3.5" :class="loading[activeTab] ? 'animate-spin' : ''" />
          Refresh
        </button>
      </div>

      <!-- Tab Bar -->
      <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-0 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="shrink-0 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === tab.id
              ? 'border-b-2 border-violet-600 text-violet-700'
              : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Error banner -->
      <div v-if="errors[activeTab]" class="flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <AlertCircle class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
        <div>
          <p class="font-medium">Gagal load data</p>
          <p class="mt-0.5 text-xs text-rose-600">{{ errors[activeTab] }}</p>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 1: CR Log                                                      -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'log'" class="space-y-3">

        <!-- ── Filter bar ───────────────────────────────────────────────── -->
        <div class="space-y-2">

          <!-- Row 1: Search + Date segments -->
          <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-[220px] flex-1">
              <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="logSearch"
                type="text"
                placeholder="Cari ID, ringkasan, projek…"
                class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm text-slate-700 shadow-sm outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-200"
              />
            </div>

            <!-- Date segments -->
            <div class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
              <button
                v-for="seg in ['30', '60', '90']" :key="seg"
                @click="setDateSegment(seg)"
                class="rounded px-3 py-1 text-xs font-medium transition-colors"
                :class="dateSegment === seg
                  ? 'bg-violet-600 text-white'
                  : 'text-slate-500 hover:bg-slate-100'"
              >{{ seg }}h</button>
              <button
                @click="setDateSegment('custom')"
                class="rounded px-3 py-1 text-xs font-medium transition-colors"
                :class="dateSegment === 'custom'
                  ? 'bg-violet-600 text-white'
                  : 'text-slate-500 hover:bg-slate-100'"
              >Pilih Tarikh</button>
            </div>
          </div>

          <!-- Custom date range (shown when 'custom' selected) -->
          <div v-if="dateSegment === 'custom'" class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-3 py-2">
              <span class="text-xs font-medium text-violet-700">Dari</span>
              <input
                v-model="customDateFrom"
                type="date"
                class="rounded border border-violet-200 bg-white px-2 py-0.5 text-xs text-slate-700 outline-none focus:border-violet-400"
              />
              <span class="text-xs font-medium text-violet-700">Hingga</span>
              <input
                v-model="customDateTo"
                type="date"
                class="rounded border border-violet-200 bg-white px-2 py-0.5 text-xs text-slate-700 outline-none focus:border-violet-400"
              />
            </div>
          </div>

          <!-- Row 2: Dropdown filters -->
          <div class="flex flex-wrap items-center gap-2">

            <!-- Status -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'status' ? null : 'status'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.status.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Status
                <span v-if="filters.status.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.status.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'status'" class="absolute left-0 top-full z-20 mt-1 w-44 rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <label v-for="opt in STATUS_OPTIONS" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.status" class="rounded accent-violet-600" />
                  <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium capitalize" :class="statusClass(opt)">{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Priority -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'priority' ? null : 'priority'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.priority.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Priority
                <span v-if="filters.priority.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.priority.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'priority'" class="absolute left-0 top-full z-20 mt-1 w-40 rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <div v-if="!filterOpts.priority.length" class="px-2 py-2 text-xs text-slate-400">Loading…</div>
                <label v-for="opt in filterOpts.priority" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.priority" class="rounded accent-violet-600" />
                  <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium capitalize" :class="priorityClass(opt)">{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Projek -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'projek' ? null : 'projek'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.projek.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Projek
                <span v-if="filters.projek.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.projek.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'projek'" class="absolute left-0 top-full z-20 mt-1 max-h-52 w-56 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <div v-if="!filterOpts.projek.length" class="px-2 py-2 text-xs text-slate-400">Loading…</div>
                <label v-for="opt in filterOpts.projek" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.projek" class="rounded accent-violet-600" />
                  <span class="truncate" :title="opt">{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Kategori -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'kategori' ? null : 'kategori'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.kategori.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Kategori
                <span v-if="filters.kategori.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.kategori.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'kategori'" class="absolute left-0 top-full z-20 mt-1 max-h-52 w-56 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <div v-if="!filterOpts.kategori.length" class="px-2 py-2 text-xs text-slate-400">Loading…</div>
                <label v-for="opt in filterOpts.kategori" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.kategori" class="rounded accent-violet-600" />
                  <span class="truncate" :title="opt">{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Oleh -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'by' ? null : 'by'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.by.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Oleh
                <span v-if="filters.by.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.by.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'by'" class="absolute left-0 top-full z-20 mt-1 max-h-52 w-48 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <div v-if="!filterOpts.by.length" class="px-2 py-2 text-xs text-slate-400">Loading…</div>
                <label v-for="opt in filterOpts.by" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.by" class="rounded accent-violet-600" />
                  <span>{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Assigned -->
            <div class="relative" @click.stop>
              <button
                @click="openFilter = openFilter === 'assigned' ? null : 'assigned'"
                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors"
                :class="filters.assigned.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              >
                Assigned
                <span v-if="filters.assigned.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ filters.assigned.length }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </button>
              <div v-show="openFilter === 'assigned'" class="absolute left-0 top-full z-20 mt-1 max-h-52 w-48 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
                <div v-if="!filterOpts.assigned.length" class="px-2 py-2 text-xs text-slate-400">Loading…</div>
                <label v-for="opt in filterOpts.assigned" :key="opt"
                  class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                  <input type="checkbox" :value="opt" v-model="filters.assigned" class="rounded accent-violet-600" />
                  <span>{{ opt }}</span>
                </label>
              </div>
            </div>

            <!-- Clear all -->
            <button
              v-if="activeFilterCount > 0"
              @click="clearAllFilters"
              class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-500 shadow-sm hover:bg-slate-50"
            >
              <X class="h-3 w-3" />
              Reset ({{ activeFilterCount }})
            </button>
          </div>
        </div>

        <!-- ── Table ─────────────────────────────────────────────────────── -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
            <div class="flex items-center gap-2">
              <GitPullRequest class="h-4 w-4 text-violet-500" />
              <h2 class="text-sm font-semibold text-slate-800">CR Log</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
              {{ logRows.length }} / {{ logTotal }} rekod
            </span>
          </div>

          <div v-if="loading.log && logRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Loading…</div>
          <div v-else-if="logRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Tiada rekod CR ditemui</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-3 py-2 text-left font-medium">ID</th>
                  <th class="px-3 py-2 text-left font-medium">Projek</th>
                  <th class="px-3 py-2 text-left font-medium">Kategori</th>
                  <th class="px-3 py-2 text-left font-medium">Ringkasan</th>
                  <th class="px-3 py-2 text-left font-medium">Priority</th>
                  <th class="px-3 py-2 text-left font-medium">Status</th>
                  <th class="px-3 py-2 text-left font-medium">Oleh</th>
                  <th class="px-3 py-2 text-left font-medium">Assigned</th>
                  <th class="px-3 py-2 text-left font-medium">Tarikh</th>
                  <th class="px-3 py-2 text-right font-medium">Umur</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="row in logRows" :key="row.id" class="hover:bg-slate-50">
                  <td class="px-3 py-2 font-mono font-semibold text-violet-700">#{{ row.id }}</td>
                  <td class="max-w-[130px] truncate px-3 py-2 text-slate-600" :title="row.projek || ''">{{ row.projek || '—' }}</td>
                  <td class="max-w-[140px] truncate px-3 py-2 text-slate-500" :title="row.kategori || ''">{{ row.kategori || '—' }}</td>
                  <td class="max-w-[240px] truncate px-3 py-2 text-slate-700" :title="row.ringkasan">{{ row.ringkasan }}</td>
                  <td class="px-3 py-2">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="priorityClass(row.priority)">{{ row.priority }}</span>
                  </td>
                  <td class="px-3 py-2">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="statusClass(row.status)">{{ row.status }}</span>
                  </td>
                  <td class="px-3 py-2 text-slate-500">{{ row.by || '—' }}</td>
                  <td class="px-3 py-2 text-slate-600">{{ row.assigned || '—' }}</td>
                  <td class="px-3 py-2 text-slate-400">{{ row.tarikh }}</td>
                  <td class="px-3 py-2 text-right font-mono text-slate-500">{{ row.umur }}h</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="logTotalPages > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
            <p class="text-xs text-slate-500">
              {{ logRows.length === 0 ? 0 : (logPage - 1) * 50 + 1 }}–{{ (logPage - 1) * 50 + logRows.length }}
              daripada {{ logTotal }} rekod
            </p>
            <div class="flex items-center gap-1">
              <button @click="logPage--; loadLog()" :disabled="logPage <= 1 || loading.log"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40">‹ Prev</button>
              <template v-for="p in paginationPages" :key="typeof p === 'number' ? p : `e${p}`">
                <span v-if="p === '...'" class="px-1 text-xs text-slate-400">…</span>
                <button v-else @click="logPage = p; loadLog()" :disabled="loading.log"
                  class="min-w-[28px] rounded px-2 py-1 text-xs font-medium transition-colors"
                  :class="logPage === p ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >{{ p }}</button>
              </template>
              <button @click="logPage++; loadLog()" :disabled="logPage >= logTotalPages || loading.log"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40">Next ›</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 2: Ringkasan                                                   -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'summary'" class="space-y-4">

        <div v-if="!summary && loading.summary" class="grid grid-cols-3 gap-3">
          <div v-for="i in 3" :key="i" class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
            <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200" />
            <div class="mx-auto mt-2 h-8 w-16 animate-pulse rounded bg-slate-200" />
          </div>
        </div>

        <div v-if="summary" class="grid grid-cols-3 gap-3">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Jumlah CR</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ summary.totals.total }}</p>
          </div>
          <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Sedang Terbuka</p>
            <p class="mt-1 text-3xl font-bold text-rose-700">{{ summary.totals.open }}</p>
          </div>
          <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Diselesaikan</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ summary.totals.resolved }}</p>
          </div>
        </div>

        <div v-if="summary" class="grid gap-4 lg:grid-cols-2">
          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
              <h2 class="text-sm font-semibold text-slate-800">Top Assignee (Open)</h2>
            </div>
            <div v-if="summary.assignees.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">Tiada data</div>
            <table v-else class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Nama</th>
                  <th class="px-3 py-2 text-right font-medium">CR Terbuka</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="a in summary.assignees" :key="a.name" class="hover:bg-slate-50">
                  <td class="px-4 py-2 text-slate-700">{{ a.name }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-rose-600">{{ a.open }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
              <h2 class="text-sm font-semibold text-slate-800">Pecahan Modul (Open)</h2>
            </div>
            <div v-if="summary.modules.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">Tiada data</div>
            <table v-else class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Modul</th>
                  <th class="px-3 py-2 text-right font-medium">CR Terbuka</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="m in summary.modules" :key="m.name" class="hover:bg-slate-50">
                  <td class="max-w-[220px] truncate px-4 py-2 text-slate-700" :title="m.name">{{ m.name }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-rose-600">{{ m.open }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 3: Trend                                                       -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'trend'" class="space-y-4">

        <div class="flex items-center justify-between">
          <h2 class="text-sm font-medium text-slate-600">Tempoh:</h2>
          <select v-model.number="trendDays" @change="loadTrend"
            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 shadow-sm outline-none focus:border-violet-400">
            <option :value="7">7 hari</option>
            <option :value="14">14 hari</option>
            <option :value="30">30 hari</option>
          </select>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-slate-800">Trend CR Harian</h2>
          </div>
          <div class="p-4">
            <div v-if="loading.trend && trendRows.length === 0" class="py-10 text-center text-xs text-slate-400">Loading chart…</div>
            <svg v-else-if="trendRows.length > 0" :viewBox="`0 0 ${chartW} ${chartH}`" class="w-full" preserveAspectRatio="xMidYMid meet">
              <defs>
                <linearGradient id="cr-trend-fill" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.12" />
                  <stop offset="100%" stop-color="#7c3aed" stop-opacity="0" />
                </linearGradient>
              </defs>
              <line v-for="n in 4" :key="n"
                :x1="chartPad.left" :x2="chartPad.left + innerW"
                :y1="chartPad.top + (innerH / 4) * (n - 1)" :y2="chartPad.top + (innerH / 4) * (n - 1)"
                stroke="#e2e8f0" stroke-width="1" />
              <polygon :points="bakiArea" fill="url(#cr-trend-fill)" />
              <polyline :points="bakiPolyline" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linejoin="round" />
              <polyline :points="resolvedPolyline" fill="none" stroke="#10b981" stroke-width="1.5" stroke-dasharray="4 3" stroke-linejoin="round" />
              <circle v-for="(r, i) in trendRows" :key="'pt-' + i"
                :cx="cx(i, trendRows.length)" :cy="cyBaki(r.baki)" r="3" fill="white" stroke="#7c3aed" stroke-width="2" />
              <text v-for="(r, i) in trendRows" :key="'lbl-' + i"
                :x="cx(i, trendRows.length)" :y="chartPad.top + innerH + 18"
                text-anchor="middle" font-size="9" fill="#94a3b8">{{ r.tarikh.slice(0, 5) }}</text>
              <text v-for="n in 3" :key="'ylbl-' + n"
                :x="chartPad.left - 4" :y="chartPad.top + (innerH / 3) * (n - 1) + 3"
                text-anchor="end" font-size="9" fill="#94a3b8">{{ Math.round(maxBaki * (1 - (n - 1) / 3)) }}</text>
            </svg>
            <div class="mt-2 flex gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1.5"><span class="inline-block h-0.5 w-5 bg-violet-600" />Jumlah Terbuka (baki)</span>
              <span class="flex items-center gap-1.5"><span class="inline-block h-0.5 w-5 border-t-2 border-dashed border-emerald-500" />Resolved</span>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-slate-800">Data Trend Harian</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-3 py-2 text-left font-medium">Tarikh</th>
                  <th class="px-3 py-2 text-right font-medium">Baru</th>
                  <th class="px-3 py-2 text-right font-medium">Resolved</th>
                  <th class="px-3 py-2 text-right font-medium">Reopened</th>
                  <th class="px-3 py-2 text-right font-medium">Baki</th>
                  <th class="px-3 py-2 text-right font-medium">Selesai %</th>
                  <th class="px-3 py-2 text-right font-medium">Reopen %</th>
                  <th class="px-3 py-2 text-center font-medium">Tren</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="r in trendRows" :key="r.tarikh" class="hover:bg-slate-50">
                  <td class="px-3 py-2 font-mono text-slate-600">{{ r.tarikh }}</td>
                  <td class="px-3 py-2 text-right font-mono text-slate-700">{{ r.baru }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-emerald-600">{{ r.resolved }}</td>
                  <td class="px-3 py-2 text-right font-mono" :class="r.reopened > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400'">{{ r.reopened }}</td>
                  <td class="px-3 py-2 text-right font-mono font-bold text-violet-700">{{ r.baki }}</td>
                  <td class="px-3 py-2 text-right font-mono" :class="resolveRateClass(r.kadarResolve)">{{ r.kadarResolve.toFixed(1) }}%</td>
                  <td class="px-3 py-2 text-right font-mono" :class="r.kadarReopen >= 10 ? 'text-rose-600 font-semibold' : 'text-slate-500'">{{ r.kadarReopen.toFixed(1) }}%</td>
                  <td class="px-3 py-2 text-center font-semibold" :class="trenClass(r.tren)">{{ r.tren }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
