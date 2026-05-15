<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Bug, AlertCircle, Search, RefreshCw, ChevronDown } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  fetchDefectDashboard, fetchDefectLog, fetchDefectSummary,
  fetchDefectCategories, fetchDefectTrend,
  type DashboardResponse, type DefectLogRow,
  type SummaryResponse, type CategoryRow, type TrendRow,
} from "@/api/defects";

type TabId = "dashboard" | "log" | "summary" | "category" | "trend" | "guide";
const activeTab = ref<TabId>("dashboard");
const tabs: { id: TabId; label: string }[] = [
  { id: "dashboard", label: "Perubahan Harini" },
  { id: "log",       label: "Defect Log" },
  { id: "summary",   label: "Ringkasan Harian" },
  { id: "category",  label: "Analisis Kategori" },
  { id: "trend",     label: "Trend Harian" },
  { id: "guide",     label: "Panduan" },
];

// ── Loading state per tab ─────────────────────────────────────────────────
const loaded = ref<Record<TabId, boolean>>({
  dashboard: false, log: false, summary: false, category: false, trend: false, guide: true,
});
const loading = ref<Record<TabId, boolean>>({
  dashboard: false, log: false, summary: false, category: false, trend: false, guide: false,
});
const errors = ref<Record<TabId, string | null>>({
  dashboard: null, log: null, summary: null, category: null, trend: null, guide: null,
});

// ── Tab data ──────────────────────────────────────────────────────────────
const dashboard = ref<DashboardResponse | null>(null);
const logRows = ref<DefectLogRow[]>([]);
const logTotal = ref(0);
const logTotalPages = ref(1);
const logPage = ref(1);
const logSearch = ref("");
const logFilters = ref<{ tahap: string[]; status: string[] }>({ tahap: [], status: [] });
const openFilter = ref<string | null>(null);

const TAHAP_OPTIONS = ['Kritikal', 'High', 'Medium', 'Low'] as const;
const STATUS_OPTIONS = ['new', 'feedback', 'acknowledged', 'confirmed', 'assigned', 'resolved'] as const;

function clearLogFilters() {
  logFilters.value = { tahap: [], status: [] };
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

const summary = ref<SummaryResponse | null>(null);
const categories = ref<CategoryRow[]>([]);
const trendRows = ref<TrendRow[]>([]);
const trendDays = ref(14);

// ── Loaders ───────────────────────────────────────────────────────────────
async function loadDashboard() {
  loading.value.dashboard = true;
  errors.value.dashboard = null;
  try {
    const r = await fetchDefectDashboard();
    dashboard.value = r.data;
    loaded.value.dashboard = true;
  } catch (e) {
    errors.value.dashboard = (e as Error).message;
  } finally {
    loading.value.dashboard = false;
  }
}

async function loadLog() {
  loading.value.log = true;
  errors.value.log = null;
  try {
    const r = await fetchDefectLog({
      q: logSearch.value,
      limit: 50,
      page: logPage.value,
      tahap: logFilters.value.tahap,
      status: logFilters.value.status,
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
    const r = await fetchDefectSummary();
    summary.value = r.data;
    loaded.value.summary = true;
  } catch (e) {
    errors.value.summary = (e as Error).message;
  } finally {
    loading.value.summary = false;
  }
}

async function loadCategories() {
  loading.value.category = true;
  errors.value.category = null;
  try {
    const r = await fetchDefectCategories();
    categories.value = r.data;
    loaded.value.category = true;
  } catch (e) {
    errors.value.category = (e as Error).message;
  } finally {
    loading.value.category = false;
  }
}

async function loadTrend() {
  loading.value.trend = true;
  errors.value.trend = null;
  try {
    const r = await fetchDefectTrend(trendDays.value);
    trendRows.value = r.data;
    loaded.value.trend = true;
  } catch (e) {
    errors.value.trend = (e as Error).message;
  } finally {
    loading.value.trend = false;
  }
}

// Auto-load when tab is activated
watch(activeTab, (t) => {
  if (!loaded.value[t]) ensureLoaded(t);
}, { immediate: false });

function ensureLoaded(t: TabId) {
  if (t === "dashboard") return loadDashboard();
  if (t === "log") return loadLog();
  if (t === "summary") return loadSummary();
  if (t === "category") return loadCategories();
  if (t === "trend") return loadTrend();
}

function refreshCurrent() {
  const t = activeTab.value;
  if (t === "dashboard") loadDashboard();
  else if (t === "log") loadLog();
  else if (t === "summary") loadSummary();
  else if (t === "category") loadCategories();
  else if (t === "trend") loadTrend();
}

let logSearchTimer: number | null = null;
watch(logSearch, () => {
  if (logSearchTimer) clearTimeout(logSearchTimer);
  logPage.value = 1;
  logSearchTimer = window.setTimeout(loadLog, 300);
});

watch(logFilters, () => { logPage.value = 1; loadLog(); }, { deep: true });

const _closeFilter = () => { openFilter.value = null; };
onMounted(() => {
  loadDashboard();
  document.addEventListener('click', _closeFilter);
});
onUnmounted(() => document.removeEventListener('click', _closeFilter));

// ── Static reference data (Panduan) ───────────────────────────────────────
const panduan = [
  { severity: "block",        mapped: "Kritikal", takrifan: "Sistem tidak boleh digunakan langsung" },
  { severity: "crash",        mapped: "Kritikal", takrifan: "Sistem crash / data rosak" },
  { severity: "major",        mapped: "High",     takrifan: "Fungsi utama terjejas, ada workaround terhad" },
  { severity: "minor",        mapped: "Medium",   takrifan: "Fungsi terjejas sebahagian, ada workaround" },
  { severity: "text / tweak", mapped: "Low",      takrifan: "Isu kosmetik atau paparan kecil" },
  { severity: "feature",      mapped: "Low",      takrifan: "Permintaan fungsi baru" },
];

// ── SVG chart helpers ─────────────────────────────────────────────────────
const chartW = 600;
const chartH = 180;
const chartPad = { top: 10, right: 20, bottom: 30, left: 40 };
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

// ── Helpers ───────────────────────────────────────────────────────────────
function severityClass(tahap: string) {
  if (tahap === "Kritikal") return "bg-rose-100 text-rose-700";
  if (tahap === "High")     return "bg-orange-100 text-orange-700";
  if (tahap === "Medium")   return "bg-amber-100 text-amber-700";
  return "bg-slate-100 text-slate-600";
}
function statusClass(status: string) {
  const s = (status || "").toLowerCase();
  if (s === "resolved" || s === "closed") return "bg-emerald-100 text-emerald-700";
  if (s === "feedback") return "bg-amber-100 text-amber-700";
  return "bg-blue-100 text-blue-700";
}
function severityBadgeColor(color: string) {
  return ({
    rose: "bg-rose-500", orange: "bg-orange-500", amber: "bg-amber-400", slate: "bg-slate-400",
  } as Record<string, string>)[color] ?? "bg-slate-400";
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
function todayLabel() {
  const d = new Date();
  return d.toLocaleDateString("ms-MY", { day: "numeric", month: "long", year: "numeric" });
}
const kpis = computed(() => {
  const k = dashboard.value?.kpi;
  if (!k) return [];
  return [
    { label: "Defect Baru",          value: k.newToday,          color: "rose"    },
    { label: "Baru Resolved",        value: k.resolvedToday,     color: "emerald" },
    { label: "Baru Reopened",        value: k.reopenedToday,     color: "slate"   },
    { label: "Kritikal Terbuka",     value: k.kritikalOpen,      color: "slate"   },
    { label: "High Terbuka",         value: k.highOpen,          color: "orange"  },
    { label: "Feedback Tertunggak",  value: k.feedbackOpen,      color: "amber"   },
    { label: "Jumlah Aktif",         value: k.totalActive,       color: "violet"  },
  ];
});
function kpiColorClass(color: string) {
  return ({
    rose: "border-rose-200 bg-rose-50",
    emerald: "border-emerald-200 bg-emerald-50",
    orange: "border-orange-200 bg-orange-50",
    amber: "border-amber-200 bg-amber-50",
    violet: "border-violet-200 bg-violet-50",
    slate: "border-slate-200 bg-slate-50",
  } as Record<string, string>)[color] ?? "border-slate-200 bg-slate-50";
}
function kpiValueClass(color: string) {
  return ({
    rose: "text-rose-700", emerald: "text-emerald-700", orange: "text-orange-700",
    amber: "text-amber-700", violet: "text-violet-700", slate: "text-slate-600",
  } as Record<string, string>)[color] ?? "text-slate-700";
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h1 class="page-title">Defect Reporting</h1>
          <p class="mt-0.5 text-sm text-slate-500">Laporan Defect QA — LZS NAS (live dari MantisBT)</p>
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
      <!-- Tab 1: Perubahan Harini                                           -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'dashboard'" class="space-y-4">
        <p v-if="dashboard" class="text-xs text-slate-400">{{ todayLabel() }}</p>

        <!-- Loading skeleton -->
        <div v-if="!dashboard && loading.dashboard" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
          <div v-for="i in 7" :key="i" class="rounded-lg border border-slate-200 bg-slate-50 p-3 shadow-sm">
            <div class="h-3 w-20 animate-pulse rounded bg-slate-200" />
            <div class="mt-2 h-7 w-12 animate-pulse rounded bg-slate-200" />
          </div>
        </div>

        <!-- KPI Cards -->
        <div v-if="dashboard" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
          <div v-for="kpi in kpis" :key="kpi.label"
            class="rounded-lg border p-3 shadow-sm"
            :class="kpiColorClass(kpi.color)">
            <p class="text-[11px] font-medium text-slate-500">{{ kpi.label }}</p>
            <p class="mt-1 text-2xl font-bold" :class="kpiValueClass(kpi.color)">{{ kpi.value }}</p>
          </div>
        </div>

        <!-- Insight -->
        <div v-if="dashboard" class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          <AlertCircle class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
          <p>
            Hari ini: <strong>{{ dashboard.kpi.newToday }}</strong> defect baru,
            <strong>{{ dashboard.kpi.resolvedToday }}</strong> resolved,
            <strong>{{ dashboard.kpi.reopenedToday }}</strong> reopened.
            Jumlah aktif:
            <strong :class="dashboard.kpi.deltaVsYesterday > 0 ? 'text-rose-700' : dashboard.kpi.deltaVsYesterday < 0 ? 'text-emerald-700' : ''">
              {{ dashboard.kpi.totalActive }}
            </strong>
            ({{ dashboard.kpi.deltaVsYesterday > 0 ? '+' : '' }}{{ dashboard.kpi.deltaVsYesterday }} dari semalam {{ dashboard.kpi.activeYesterday }}).
            Kritikal terbuka: {{ dashboard.kpi.kritikalOpen }} · High terbuka: {{ dashboard.kpi.highOpen }} · Feedback tertunggak: {{ dashboard.kpi.feedbackOpen }}.
          </p>
        </div>

        <!-- New / Resolved / Reopened tables -->
        <div v-if="dashboard" class="grid gap-4 lg:grid-cols-3">

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm lg:col-span-3">
            <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
              <Bug class="h-4 w-4 text-rose-500" />
              <h2 class="text-sm font-semibold text-slate-800">🆕 Defect Baru ({{ dashboard.newDefects.length }})</h2>
            </div>
            <div v-if="dashboard.newDefects.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
              Tiada defect baru hari ini
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium">ID</th>
                    <th class="px-3 py-2 text-left font-medium">Ringkasan</th>
                    <th class="px-3 py-2 text-left font-medium">Modul</th>
                    <th class="px-3 py-2 text-left font-medium">Tahap</th>
                    <th class="px-3 py-2 text-left font-medium">Priority</th>
                    <th class="px-3 py-2 text-left font-medium">Assigned</th>
                    <th class="px-3 py-2 text-left font-medium">Tarikh</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="d in dashboard.newDefects" :key="d.id" class="hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono font-semibold text-violet-700">#{{ d.id }}</td>
                    <td class="max-w-[320px] truncate px-3 py-2 text-slate-700" :title="d.ringkasan">{{ d.ringkasan }}</td>
                    <td class="max-w-[200px] truncate px-3 py-2 text-slate-500" :title="d.modul || ''">{{ d.modul }}</td>
                    <td class="px-3 py-2">
                      <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="severityClass(d.tahap)">{{ d.tahap }}</span>
                    </td>
                    <td class="px-3 py-2 text-slate-500">{{ d.priority }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ d.assigned || '—' }}</td>
                    <td class="px-3 py-2 text-slate-400">{{ d.tarikh }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
              <span class="h-2 w-2 rounded-full bg-emerald-500" />
              <h2 class="text-sm font-semibold text-slate-800">✅ Baru Resolved ({{ dashboard.resolvedTodayList.length }})</h2>
            </div>
            <div v-if="dashboard.resolvedTodayList.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
              Tiada defect diselesaikan hari ini
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium">ID</th>
                    <th class="px-3 py-2 text-left font-medium">Ringkasan</th>
                    <th class="px-3 py-2 text-left font-medium">Modul</th>
                    <th class="px-3 py-2 text-left font-medium">Tahap</th>
                    <th class="px-3 py-2 text-left font-medium">Resolver</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="d in dashboard.resolvedTodayList" :key="d.id" class="hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono font-semibold text-violet-700">#{{ d.id }}</td>
                    <td class="max-w-[260px] truncate px-3 py-2 text-slate-700" :title="d.ringkasan">{{ d.ringkasan }}</td>
                    <td class="max-w-[180px] truncate px-3 py-2 text-slate-500" :title="d.modul || ''">{{ d.modul }}</td>
                    <td class="px-3 py-2">
                      <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="severityClass(d.tahap)">{{ d.tahap }}</span>
                    </td>
                    <td class="px-3 py-2 text-slate-600">{{ d.resolver || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
              <span class="h-2 w-2 rounded-full bg-rose-400" />
              <h2 class="text-sm font-semibold text-slate-800">🔄 Baru Reopened ({{ dashboard.reopenedTodayList.length }})</h2>
            </div>
            <div v-if="dashboard.reopenedTodayList.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
              Tiada defect dibuka semula hari ini
            </div>
            <div v-else class="divide-y divide-slate-50">
              <div v-for="d in dashboard.reopenedTodayList" :key="d.id" class="px-4 py-2 text-xs">
                <div class="flex items-baseline justify-between">
                  <span class="font-mono font-semibold text-violet-700">#{{ d.id }}</span>
                  <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="severityClass(d.tahap)">{{ d.tahap }}</span>
                </div>
                <p class="mt-1 truncate text-slate-700" :title="d.ringkasan">{{ d.ringkasan }}</p>
                <p class="text-slate-400">{{ d.modul }} · {{ d.tarikh }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 2: Defect Log                                                 -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'log'" class="space-y-3">

        <!-- Filter bar -->
        <div class="flex flex-wrap items-center gap-2">

          <!-- Search -->
          <div class="relative min-w-[200px]">
            <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="logSearch"
              type="text"
              placeholder="Cari ID, ringkasan, modul…"
              class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm text-slate-700 shadow-sm outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-200"
            />
          </div>

          <!-- Tahap filter -->
          <div class="relative" @click.stop>
            <button
              @click="openFilter = openFilter === 'tahap' ? null : 'tahap'"
              class="flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium shadow-sm transition-colors"
              :class="logFilters.tahap.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
            >
              Tahap
              <span v-if="logFilters.tahap.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ logFilters.tahap.length }}</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </button>
            <div v-show="openFilter === 'tahap'" class="absolute left-0 top-full z-20 mt-1 w-36 rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
              <label
                v-for="opt in TAHAP_OPTIONS" :key="opt"
                class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
              >
                <input type="checkbox" :value="opt" v-model="logFilters.tahap" class="rounded accent-violet-600" />
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium" :class="severityClass(opt)">{{ opt }}</span>
              </label>
            </div>
          </div>

          <!-- Status filter -->
          <div class="relative" @click.stop>
            <button
              @click="openFilter = openFilter === 'status' ? null : 'status'"
              class="flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium shadow-sm transition-colors"
              :class="logFilters.status.length ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
            >
              Status
              <span v-if="logFilters.status.length" class="rounded-full bg-violet-600 px-1.5 py-px text-[10px] leading-none text-white">{{ logFilters.status.length }}</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </button>
            <div v-show="openFilter === 'status'" class="absolute left-0 top-full z-20 mt-1 w-44 rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg">
              <label
                v-for="opt in STATUS_OPTIONS" :key="opt"
                class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
              >
                <input type="checkbox" :value="opt" v-model="logFilters.status" class="rounded accent-violet-600" />
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium capitalize" :class="statusClass(opt)">{{ opt }}</span>
              </label>
            </div>
          </div>

          <!-- Clear filters -->
          <button
            v-if="logFilters.tahap.length || logFilters.status.length"
            @click="clearLogFilters"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-500 shadow-sm hover:bg-slate-50"
          >
            Clear filters
          </button>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-slate-800">Defect Log</h2>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
              {{ logRows.length }} / {{ logTotal }} rekod
            </span>
          </div>
          <div v-if="loading.log && logRows.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">Loading…</div>
          <div v-else-if="logRows.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">Tiada rekod</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-3 py-2 text-left font-medium">ID</th>
                  <th class="px-3 py-2 text-left font-medium">Projek/Modul</th>
                  <th class="px-3 py-2 text-left font-medium">Kategori</th>
                  <th class="px-3 py-2 text-left font-medium">Ringkasan</th>
                  <th class="px-3 py-2 text-left font-medium">Severity</th>
                  <th class="px-3 py-2 text-left font-medium">Tahap</th>
                  <th class="px-3 py-2 text-left font-medium">Status</th>
                  <th class="px-3 py-2 text-left font-medium">Dilaporkan</th>
                  <th class="px-3 py-2 text-left font-medium">Diagihkan</th>
                  <th class="px-3 py-2 text-left font-medium">Tarikh</th>
                  <th class="px-3 py-2 text-left font-medium">Kemaskini</th>
                  <th class="px-3 py-2 text-right font-medium">Umur</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="d in logRows" :key="d.id" class="hover:bg-slate-50">
                  <td class="px-3 py-2 font-mono font-semibold text-violet-700">#{{ d.id }}</td>
                  <td class="max-w-[150px] truncate px-3 py-2 text-slate-600" :title="d.projek || ''">{{ d.projek }}</td>
                  <td class="max-w-[160px] truncate px-3 py-2 text-slate-500" :title="d.kategori || ''">{{ d.kategori }}</td>
                  <td class="max-w-[220px] truncate px-3 py-2 text-slate-700" :title="d.ringkasan">{{ d.ringkasan }}</td>
                  <td class="px-3 py-2 text-slate-500">{{ d.severity }}</td>
                  <td class="px-3 py-2">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="severityClass(d.tahap)">{{ d.tahap }}</span>
                  </td>
                  <td class="px-3 py-2">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="statusClass(d.status)">{{ d.status }}</span>
                  </td>
                  <td class="px-3 py-2 text-slate-500">{{ d.by }}</td>
                  <td class="px-3 py-2 text-slate-600">{{ d.assigned || '—' }}</td>
                  <td class="px-3 py-2 text-slate-400">{{ d.tarikh }}</td>
                  <td class="px-3 py-2 text-slate-400">{{ d.kemaskini }}</td>
                  <td class="px-3 py-2 text-right font-mono text-slate-500">{{ d.umur }}h</td>
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
              <button
                @click="logPage--; loadLog()"
                :disabled="logPage <= 1 || loading.log"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40"
              >‹ Prev</button>
              <template v-for="p in paginationPages" :key="typeof p === 'number' ? p : `e${p}`">
                <span v-if="p === '...'" class="px-1 text-xs text-slate-400">…</span>
                <button
                  v-else
                  @click="logPage = p; loadLog()"
                  :disabled="loading.log"
                  class="min-w-[28px] rounded px-2 py-1 text-xs font-medium transition-colors"
                  :class="logPage === p ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >{{ p }}</button>
              </template>
              <button
                @click="logPage++; loadLog()"
                :disabled="logPage >= logTotalPages || loading.log"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40"
              >Next ›</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 3: Ringkasan Harian                                           -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'summary'" class="space-y-4">

        <p class="text-xs text-slate-400">{{ todayLabel() }} — Projek: LZS NAS</p>

        <div v-if="!summary && loading.summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
            <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200" />
            <div class="mx-auto mt-2 h-8 w-16 animate-pulse rounded bg-slate-200" />
          </div>
        </div>

        <div v-if="summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Jumlah Keseluruhan</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ summary.totals.total }}</p>
          </div>
          <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Defect Terbuka</p>
            <p class="mt-1 text-3xl font-bold text-rose-700">{{ summary.totals.open }}</p>
          </div>
          <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Diselesaikan</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ summary.totals.resolved }}</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm text-center">
            <p class="text-xs text-slate-500">Ditutup</p>
            <p class="mt-1 text-3xl font-bold text-slate-600">{{ summary.totals.closed }}</p>
          </div>
        </div>

        <div v-if="summary" class="grid gap-4 lg:grid-cols-3">

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
              <h2 class="text-sm font-semibold text-slate-800">Pecahan Severity (Open)</h2>
            </div>
            <div class="space-y-3 p-4">
              <div v-for="s in summary.severity" :key="s.label" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-medium text-slate-700">{{ s.label }}</span>
                  <span class="font-mono font-semibold text-slate-700">{{ s.count }}</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100">
                  <div class="h-2 rounded-full transition-all" :class="severityBadgeColor(s.color)"
                    :style="{ width: summary.totals.open ? (s.count / summary.totals.open * 100).toFixed(1) + '%' : '0%' }" />
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
              <h2 class="text-sm font-semibold text-slate-800">Top Assignee</h2>
            </div>
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Nama</th>
                  <th class="px-3 py-2 text-right font-medium">Open</th>
                  <th class="px-3 py-2 text-right font-medium">Resolved</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="a in summary.assignees" :key="a.name" class="hover:bg-slate-50">
                  <td class="px-4 py-2 text-slate-700">{{ a.name }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-rose-600">{{ a.open }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-emerald-600">{{ a.resolved }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
              <h2 class="text-sm font-semibold text-slate-800">Pecahan Modul</h2>
            </div>
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Modul</th>
                  <th class="px-3 py-2 text-right font-medium">Open</th>
                  <th class="px-3 py-2 text-right font-medium">Resolved</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="m in summary.modules" :key="m.name" class="hover:bg-slate-50">
                  <td class="max-w-[200px] truncate px-4 py-2 text-slate-700" :title="m.name">{{ m.name }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-rose-600">{{ m.open }}</td>
                  <td class="px-3 py-2 text-right font-mono font-semibold text-emerald-600">{{ m.resolved }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 4: Analisis Kategori                                          -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'category'" class="space-y-3">
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-slate-800">Analisis Defect Mengikut Kategori Ujian</h2>
          </div>
          <div v-if="loading.category && categories.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">Loading…</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Kategori Ujian</th>
                  <th class="px-3 py-2 text-right font-medium">Jumlah</th>
                  <th class="px-3 py-2 text-right font-medium">Open</th>
                  <th class="px-3 py-2 text-right font-medium">Resolved</th>
                  <th class="px-3 py-2 text-right font-medium">Reopened</th>
                  <th class="px-3 py-2 text-right font-medium">Kritikal+High</th>
                  <th class="px-4 py-2 text-left font-medium w-40">% Open</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="c in categories" :key="c.label" class="hover:bg-slate-50">
                  <td class="max-w-[300px] truncate px-4 py-2.5 text-slate-700" :title="c.label">{{ c.label }}</td>
                  <td class="px-3 py-2.5 text-right font-mono text-slate-600">{{ c.jumlah }}</td>
                  <td class="px-3 py-2.5 text-right font-mono" :class="c.open > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400'">{{ c.open }}</td>
                  <td class="px-3 py-2.5 text-right font-mono" :class="c.resolved > 0 ? 'text-emerald-600 font-semibold' : 'text-slate-400'">{{ c.resolved }}</td>
                  <td class="px-3 py-2.5 text-right font-mono" :class="c.reopened > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400'">{{ c.reopened }}</td>
                  <td class="px-3 py-2.5 text-right font-mono" :class="c.kritikalHigh > 0 ? 'text-rose-700 font-semibold' : 'text-slate-400'">{{ c.kritikalHigh }}</td>
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      <div class="h-1.5 w-24 rounded-full bg-slate-100">
                        <div class="h-1.5 rounded-full"
                          :class="c.pctOpen >= 80 ? 'bg-rose-500' : c.pctOpen >= 40 ? 'bg-amber-400' : 'bg-emerald-500'"
                          :style="{ width: Math.min(100, c.pctOpen) + '%' }" />
                      </div>
                      <span class="text-slate-600">{{ c.pctOpen }}%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 5: Trend Harian                                               -->
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
            <h2 class="text-sm font-semibold text-slate-800">Trend Defect Harian</h2>
          </div>
          <div class="p-4">
            <div v-if="loading.trend && trendRows.length === 0" class="py-12 text-center text-xs text-slate-400">Loading chart…</div>
            <svg v-else-if="trendRows.length > 0" :viewBox="`0 0 ${chartW} ${chartH}`" class="w-full" preserveAspectRatio="xMidYMid meet">
              <defs>
                <linearGradient id="trend-fill" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.15" />
                  <stop offset="100%" stop-color="#7c3aed" stop-opacity="0" />
                </linearGradient>
              </defs>
              <line v-for="n in 4" :key="n"
                :x1="chartPad.left" :x2="chartPad.left + innerW"
                :y1="chartPad.top + (innerH / 4) * (n - 1)" :y2="chartPad.top + (innerH / 4) * (n - 1)"
                stroke="#e2e8f0" stroke-width="1" />
              <polygon :points="bakiArea" fill="url(#trend-fill)" />
              <polyline :points="bakiPolyline" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linejoin="round" />
              <polyline :points="resolvedPolyline" fill="none" stroke="#10b981" stroke-width="1.5" stroke-dasharray="4 3" stroke-linejoin="round" />
              <circle v-for="(r, i) in trendRows" :key="'pt-' + i"
                :cx="cx(i, trendRows.length)" :cy="cyBaki(r.baki)" r="3" fill="white" stroke="#7c3aed" stroke-width="2" />
              <text v-for="(r, i) in trendRows" :key="'lbl-' + i"
                :x="cx(i, trendRows.length)" :y="chartPad.top + innerH + 18"
                text-anchor="middle" font-size="9" fill="#94a3b8">{{ r.tarikh.slice(0, 5) }}</text>
              <text v-for="n in 3" :key="'ylbl-' + n"
                :x="chartPad.left - 6" :y="chartPad.top + (innerH / 3) * (n - 1) + 3"
                text-anchor="end" font-size="9" fill="#94a3b8">{{ Math.round(maxBaki * (1 - (n - 1) / 3)) }}</text>
            </svg>
            <div class="mt-2 flex gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1.5"><span class="inline-block h-0.5 w-5 bg-violet-600" />Jumlah Terbuka (baki)</span>
              <span class="flex items-center gap-1.5"><span class="inline-block h-0.5 w-5 border-t-2 border-dashed border-emerald-500" />Resolved (Hari Ini)</span>
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
                  <th class="px-3 py-2 text-left font-medium">Catatan</th>
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
                  <td class="max-w-[180px] truncate px-3 py-2 text-slate-400" :title="r.catatan">{{ r.catatan }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="border-t border-slate-50 px-4 py-2 text-[11px] text-slate-400">
            Sasaran: Kadar Penyelesaian &gt; 70% · Kadar Reopened &lt; 10% · Tren ↓ = baki defect berkurang
          </p>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 6: Panduan                                                    -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'guide'" class="space-y-3">
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-slate-800">Panduan Rujukan — Nilai &amp; Takrifan (Mantis LZS NAS)</h2>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide">Nilai Severity (Mantis)</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide">Mapped Ke</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide">Takrifan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="p in panduan" :key="p.severity" class="hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-slate-700">{{ p.severity }}</td>
                <td class="px-4 py-3">
                  <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="severityClass(p.mapped)">{{ p.mapped }}</span>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ p.takrifan }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
