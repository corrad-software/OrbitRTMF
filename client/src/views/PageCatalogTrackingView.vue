<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { BarChart2, AlertCircle, Search, RefreshCw, ChevronDown, CheckCircle2, Clock, CircleDot, TrendingUp } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  fetchTrackingOverview, fetchTrackingByModule, fetchTrackingPages, fetchTrackingModules, fetchTrackingTrend,
  type TrackingOverview, type TrackingModule, type TrackingPageRow, type TrackingModuleOption, type ReviewStatus, type TrackingTrendRow,
} from "@/api/tracking";

type TabId = "overview" | "modules" | "pages" | "trend";
const activeTab = ref<TabId>("overview");
const tabs: { id: TabId; label: string }[] = [
  { id: "overview", label: "Ikhtisar" },
  { id: "modules",  label: "Mengikut Modul" },
  { id: "pages",    label: "Senarai Halaman" },
  { id: "trend",    label: "Trend Harian" },
];

// ── Loading state ─────────────────────────────────────────────────────────
const loaded  = ref<Record<TabId, boolean>>({ overview: false, modules: false, pages: false, trend: false });
const loading = ref<Record<TabId, boolean>>({ overview: false, modules: false, pages: false, trend: false });
const errors  = ref<Record<TabId, string | null>>({ overview: null, modules: null, pages: null, trend: null });

// ── Module filter (shared across tabs) ───────────────────────────────────
const moduleOpts = ref<TrackingModuleOption[]>([]);
const selectedModuleId = ref<number | null>(null);
const moduleDropdownOpen = ref(false);

const selectedModuleLabel = computed(() =>
  selectedModuleId.value
    ? (moduleOpts.value.find(m => m.id === selectedModuleId.value)?.code ?? "")
    : "Semua Modul"
);

// ── Overview data ─────────────────────────────────────────────────────────
const overview = ref<TrackingOverview | null>(null);

// ── Module breakdown data ─────────────────────────────────────────────────
const modulesData = ref<TrackingModule[]>([]);

// ── Trend state ───────────────────────────────────────────────────────────
const trendRows = ref<TrackingTrendRow[]>([]);
const trendDays = ref<7 | 14 | 30>(14);

// ── Page list state ───────────────────────────────────────────────────────
const pageRows    = ref<TrackingPageRow[]>([]);
const pageTotal   = ref(0);
const pageTotalPg = ref(1);
const pageCurrent = ref(1);
const pageSearch  = ref("");
const pageIsDone  = ref<boolean | null>(null);

const paginationPages = computed((): (number | '...')[] => {
  const total = pageTotalPg.value;
  const cur   = pageCurrent.value;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages: (number | '...')[] = [1];
  if (cur > 3) pages.push('...');
  for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i);
  if (cur < total - 2) pages.push('...');
  pages.push(total);
  return pages;
});

// ── Loaders ───────────────────────────────────────────────────────────────
async function loadOverview() {
  loading.value.overview = true;
  errors.value.overview = null;
  try {
    const r = await fetchTrackingOverview(selectedModuleId.value ?? undefined);
    overview.value = r.data;
    loaded.value.overview = true;
  } catch (e) {
    errors.value.overview = (e as Error).message;
  } finally {
    loading.value.overview = false;
  }
}

async function loadModules() {
  loading.value.modules = true;
  errors.value.modules = null;
  try {
    const r = await fetchTrackingByModule(selectedModuleId.value ?? undefined);
    modulesData.value = r.data;
    loaded.value.modules = true;
  } catch (e) {
    errors.value.modules = (e as Error).message;
  } finally {
    loading.value.modules = false;
  }
}

async function loadPages() {
  loading.value.pages = true;
  errors.value.pages = null;
  try {
    const r = await fetchTrackingPages({
      q:        pageSearch.value,
      limit:    50,
      page:     pageCurrent.value,
      moduleId: selectedModuleId.value ?? undefined,
      isDone:   pageIsDone.value,
    });
    pageRows.value   = r.data;
    pageTotal.value  = (r.meta?.total as number) ?? r.data.length;
    pageTotalPg.value = (r.meta?.totalPages as number) ?? 1;
    loaded.value.pages = true;
  } catch (e) {
    errors.value.pages = (e as Error).message;
  } finally {
    loading.value.pages = false;
  }
}

async function loadTrend() {
  loading.value.trend = true;
  errors.value.trend = null;
  try {
    const r = await fetchTrackingTrend(trendDays.value, selectedModuleId.value ?? undefined);
    trendRows.value = r.data;
    loaded.value.trend = true;
  } catch (e) {
    errors.value.trend = (e as Error).message;
  } finally {
    loading.value.trend = false;
  }
}

function reloadAll() {
  loaded.value = { overview: false, modules: false, pages: false, trend: false };
  const t = activeTab.value;
  if (t === "overview") loadOverview();
  else if (t === "modules") loadModules();
  else if (t === "trend") loadTrend();
  else loadPages();
}

function onModuleChange(id: number | null) {
  selectedModuleId.value = id;
  moduleDropdownOpen.value = false;
  reloadAll();
}

watch(activeTab, (t) => {
  if (!loaded.value[t]) ensureLoaded(t);
});

function ensureLoaded(t: TabId) {
  if (t === "overview") return loadOverview();
  if (t === "modules")  return loadModules();
  if (t === "pages")    return loadPages();
  if (t === "trend")    return loadTrend();
}

watch(pageIsDone, () => { pageCurrent.value = 1; loadPages(); });

let searchTimer: number | null = null;
watch(pageSearch, () => {
  if (searchTimer) clearTimeout(searchTimer);
  pageCurrent.value = 1;
  searchTimer = window.setTimeout(loadPages, 300);
});

onMounted(async () => {
  fetchTrackingModules().then(r => { moduleOpts.value = r.data; }).catch(() => {});
  loadOverview();
});

// ── Role config ───────────────────────────────────────────────────────────
const ROLES: { key: 'ba' | 'qa' | 'tech' | 'dev'; label: string; dataKey: string; color: string }[] = [
  { key: 'ba',   label: 'BA',   dataKey: 'business_analyst', color: 'violet' },
  { key: 'qa',   label: 'QA',   dataKey: 'qa',               color: 'sky'    },
  { key: 'tech', label: 'Tech', dataKey: 'technical',         color: 'amber'  },
  { key: 'dev',  label: 'Dev',  dataKey: 'developer',         color: 'emerald'},
];

function roleCardColor(color: string) {
  return ({
    violet:  'border-violet-200 bg-violet-50',
    sky:     'border-sky-200 bg-sky-50',
    amber:   'border-amber-200 bg-amber-50',
    emerald: 'border-emerald-200 bg-emerald-50',
  } as Record<string, string>)[color] ?? 'border-slate-200 bg-slate-50';
}

function roleBarColor(color: string) {
  return ({
    violet:  'bg-violet-500',
    sky:     'bg-sky-500',
    amber:   'bg-amber-400',
    emerald: 'bg-emerald-500',
  } as Record<string, string>)[color] ?? 'bg-slate-400';
}

function roleTitleColor(color: string) {
  return ({
    violet:  'text-violet-700',
    sky:     'text-sky-700',
    amber:   'text-amber-700',
    emerald: 'text-emerald-700',
  } as Record<string, string>)[color] ?? 'text-slate-700';
}

// ── Review badge helpers ──────────────────────────────────────────────────
function reviewBadgeClass(status: ReviewStatus) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700';
  if (status === 'reviewed') return 'bg-amber-100 text-amber-700';
  if (status === 'open')     return 'bg-slate-100 text-slate-500';
  return 'bg-slate-50 text-slate-300';
}
function reviewBadgeLabel(status: ReviewStatus) {
  if (status === 'approved') return '✓';
  if (status === 'reviewed') return '~';
  if (status === 'open')     return '○';
  return '—';
}
function reviewBadgeTitle(status: ReviewStatus) {
  if (status === 'approved') return 'Approved';
  if (status === 'reviewed') return 'Reviewed';
  if (status === 'open')     return 'Open';
  return 'No feedback';
}

// ── Item bar helpers ──────────────────────────────────────────────────────
function itemPctClass(pct: number | null) {
  if (pct === null) return 'text-slate-300';
  if (pct >= 80) return 'text-emerald-600 font-semibold';
  if (pct >= 40) return 'text-amber-600 font-semibold';
  return 'text-rose-600 font-semibold';
}

function overviewRolePct(role: typeof ROLES[number]) {
  if (!overview.value) return 0;
  const r = overview.value.byReview[role.dataKey];
  if (!r) return 0;
  const total = overview.value.totals.pages;
  return total > 0 ? Math.round(r.approved / total * 100) : 0;
}

function moduleRolePct(module: TrackingModule, dataKey: string) {
  const r = module.review[dataKey];
  if (!r || module.pages === 0) return 0;
  return Math.round(r.approved / module.pages * 100);
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="page-title">Page Catalog Tracking</h1>
          <p class="mt-0.5 text-sm text-slate-500">Kemajuan pembangunan dan semakan halaman OrbitRTMF</p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Module filter -->
          <div class="relative" @click.stop>
            <button
              @click="moduleDropdownOpen = !moduleDropdownOpen"
              class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50"
              :class="selectedModuleId ? 'border-violet-400 bg-violet-50 text-violet-700' : ''"
            >
              {{ selectedModuleLabel }}
              <ChevronDown class="h-3 w-3 opacity-60" />
            </button>
            <div
              v-show="moduleDropdownOpen"
              class="absolute right-0 top-full z-20 mt-1 max-h-64 w-56 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg"
              @click.stop
            >
              <button
                @click="onModuleChange(null)"
                class="w-full rounded px-2 py-1.5 text-left text-xs transition-colors hover:bg-slate-50"
                :class="!selectedModuleId ? 'font-semibold text-violet-700' : 'text-slate-600'"
              >Semua Modul</button>
              <button
                v-for="m in moduleOpts" :key="m.id"
                @click="onModuleChange(m.id)"
                class="w-full rounded px-2 py-1.5 text-left text-xs transition-colors hover:bg-slate-50"
                :class="selectedModuleId === m.id ? 'font-semibold text-violet-700' : 'text-slate-600'"
              >
                <span class="font-mono text-violet-500">{{ m.code }}</span>
                <span class="ml-1.5 text-slate-600">{{ m.name }}</span>
              </button>
            </div>
          </div>

          <button
            @click="reloadAll"
            :disabled="loading[activeTab]"
            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 disabled:opacity-50"
          >
            <RefreshCw class="h-3.5 w-3.5" :class="loading[activeTab] ? 'animate-spin' : ''" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Tab Bar -->
      <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-0 overflow-x-auto">
          <button
            v-for="tab in tabs" :key="tab.id"
            class="shrink-0 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === tab.id
              ? 'border-b-2 border-violet-600 text-violet-700'
              : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = tab.id"
          >{{ tab.label }}</button>
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
      <!-- Tab 1: Ikhtisar                                                    -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'overview'" class="space-y-5">

        <!-- Skeleton -->
        <div v-if="!overview && loading.overview" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div v-for="i in 4" :key="i" class="animate-pulse rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
            <div class="h-3 w-20 rounded bg-slate-200" />
            <div class="mt-2 h-8 w-14 rounded bg-slate-200" />
          </div>
        </div>

        <!-- KPI Cards -->
        <div v-if="overview" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Jumlah Halaman</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ overview.totals.pages }}</p>
          </div>
          <div class="rounded-lg border border-violet-200 bg-violet-50 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Selesai</p>
            <p class="mt-1 text-3xl font-bold text-violet-700">{{ overview.totals.done }}</p>
            <p class="mt-0.5 text-xs text-violet-500">{{ overview.totals.donePct }}% daripada jumlah</p>
          </div>
          <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Semua Lulus</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ overview.totals.approvedAll }}</p>
            <p class="mt-0.5 text-xs text-emerald-600">{{ overview.totals.approvedPct }}% lulus 4 peranan</p>
          </div>
          <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Belum Selesai</p>
            <p class="mt-1 text-3xl font-bold text-amber-700">{{ overview.totals.pending }}</p>
            <p class="mt-0.5 text-xs text-amber-600">{{ 100 - overview.totals.donePct }}% masih dalam proses</p>
          </div>
        </div>

        <!-- Review Progress per Role -->
        <div v-if="overview" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="role in ROLES" :key="role.key"
            class="rounded-lg border p-4 shadow-sm"
            :class="roleCardColor(role.color)"
          >
            <div class="flex items-center justify-between">
              <p class="text-sm font-semibold" :class="roleTitleColor(role.color)">{{ role.label }}</p>
              <span class="text-lg font-bold" :class="roleTitleColor(role.color)">{{ overviewRolePct(role) }}%</span>
            </div>
            <div class="mt-2 h-1.5 w-full rounded-full bg-white/60">
              <div class="h-1.5 rounded-full transition-all" :class="roleBarColor(role.color)"
                :style="{ width: overviewRolePct(role) + '%' }" />
            </div>
            <div class="mt-2.5 grid grid-cols-3 gap-1 text-center text-[10px]">
              <div>
                <p class="font-bold text-emerald-700">{{ overview.byReview[role.dataKey]?.approved ?? 0 }}</p>
                <p class="text-slate-500">Approved</p>
              </div>
              <div>
                <p class="font-bold text-amber-700">{{ overview.byReview[role.dataKey]?.reviewed ?? 0 }}</p>
                <p class="text-slate-500">Reviewed</p>
              </div>
              <div>
                <p class="font-bold text-slate-600">{{ overview.byReview[role.dataKey]?.open ?? 0 }}</p>
                <p class="text-slate-500">Open</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Item Implementation -->
        <div v-if="overview" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">Implementasi Item</h2>
            <span class="text-xs text-slate-500">{{ overview.items.total }} item jumlah</span>
          </div>

          <!-- Stacked bar -->
          <div class="mt-3 flex h-4 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full bg-emerald-500 transition-all"
              :style="{ width: overview.items.total > 0 ? (overview.items.implemented / overview.items.total * 100) + '%' : '0%' }" />
            <div class="h-full bg-amber-400 transition-all"
              :style="{ width: overview.items.total > 0 ? (overview.items.partial / overview.items.total * 100) + '%' : '0%' }" />
            <div class="h-full bg-rose-400 transition-all"
              :style="{ width: overview.items.total > 0 ? (overview.items.missing / overview.items.total * 100) + '%' : '0%' }" />
          </div>

          <div class="mt-3 flex flex-wrap gap-4 text-xs">
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500" />
              Implemented <strong class="text-slate-700">{{ overview.items.implemented }}</strong>
              <span class="text-slate-400">({{ overview.items.total > 0 ? Math.round(overview.items.implemented / overview.items.total * 100) : 0 }}%)</span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-400" />
              Partial <strong class="text-slate-700">{{ overview.items.partial }}</strong>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-rose-400" />
              Missing <strong class="text-slate-700">{{ overview.items.missing }}</strong>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-slate-200" />
              Unset <strong class="text-slate-700">{{ overview.items.unset }}</strong>
            </span>
          </div>
        </div>

      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 2: Mengikut Modul                                             -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'modules'" class="space-y-3">

        <div v-if="loading.modules && modulesData.length === 0" class="py-8 text-center text-xs text-slate-400">Loading…</div>
        <div v-else-if="modulesData.length === 0" class="py-8 text-center text-xs text-slate-400">Tiada modul</div>

        <div v-else class="space-y-3">
          <div
            v-for="mod in modulesData" :key="mod.id"
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
          >
            <!-- Module header -->
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div>
                <div class="flex items-center gap-2">
                  <span class="rounded bg-violet-100 px-2 py-0.5 font-mono text-xs font-semibold text-violet-700">{{ mod.code }}</span>
                  <h3 class="text-sm font-semibold text-slate-800">{{ mod.name }}</h3>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-slate-500">
                <span>
                  <strong class="text-slate-800">{{ mod.done }}</strong>/{{ mod.pages }} selesai
                </span>
                <span>
                  Item impl: <strong :class="itemPctClass(mod.items.pct)">{{ mod.items.pct ?? '—' }}%</strong>
                </span>
                <span>
                  Semua lulus: <strong class="text-emerald-700">{{ mod.approvedAll }}</strong>
                </span>
              </div>
            </div>

            <!-- Page completion bar -->
            <div class="mt-3 space-y-1">
              <div class="flex items-center gap-2">
                <p class="w-20 shrink-0 text-[10px] text-slate-400">Halaman</p>
                <div class="flex-1">
                  <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-violet-500 transition-all" :style="{ width: mod.donePct + '%' }" />
                  </div>
                </div>
                <span class="w-8 text-right text-[10px] font-semibold text-violet-700">{{ mod.donePct }}%</span>
              </div>
              <div v-if="mod.items.total > 0" class="flex items-center gap-2">
                <p class="w-20 shrink-0 text-[10px] text-slate-400">Item Impl.</p>
                <div class="flex h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-2 bg-emerald-500 transition-all"
                    :style="{ width: (mod.items.implemented / mod.items.total * 100) + '%' }" />
                  <div class="h-2 bg-amber-400 transition-all"
                    :style="{ width: (mod.items.partial / mod.items.total * 100) + '%' }" />
                  <div class="h-2 bg-rose-400 transition-all"
                    :style="{ width: (mod.items.missing / mod.items.total * 100) + '%' }" />
                </div>
                <span class="w-8 text-right text-[10px] font-semibold" :class="itemPctClass(mod.items.pct)">{{ mod.items.pct }}%</span>
              </div>
            </div>

            <!-- Review role pills -->
            <div class="mt-3 flex flex-wrap gap-2">
              <div
                v-for="role in ROLES" :key="role.key"
                class="flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px]"
                :class="roleCardColor(role.color)"
              >
                <span class="font-semibold" :class="roleTitleColor(role.color)">{{ role.label }}</span>
                <span class="text-emerald-700">{{ mod.review[role.dataKey]?.approved ?? 0 }}✓</span>
                <span class="text-amber-600">{{ mod.review[role.dataKey]?.reviewed ?? 0 }}~</span>
                <span class="text-slate-500">{{ mod.review[role.dataKey]?.open ?? 0 }}○</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 4: Trend Harian                                                -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'trend'" class="space-y-4">

        <!-- Days selector -->
        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-500">Tempoh:</span>
          <div class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
            <button
              v-for="d in ([7, 14, 30] as const)" :key="d"
              @click="trendDays = d; loaded.trend = false; loadTrend()"
              class="rounded px-3 py-1 text-xs font-medium transition-colors"
              :class="trendDays === d ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100'"
            >{{ d }} hari</button>
          </div>
        </div>

        <!-- Summary strip -->
        <div v-if="trendRows.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div class="rounded-lg border border-violet-200 bg-violet-50 p-3 shadow-sm">
            <p class="text-[10px] text-slate-500">Halaman Selesai</p>
            <p class="mt-1 text-2xl font-bold text-violet-700">
              {{ trendRows.reduce((s, r) => s + r.halamanSelesai, 0) }}
            </p>
            <p class="mt-0.5 text-[10px] text-violet-500">dalam {{ trendDays }} hari</p>
          </div>
          <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
            <p class="text-[10px] text-slate-500">Review Diluluskan</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">
              {{ trendRows.reduce((s, r) => s + r.reviewLulus, 0) }}
            </p>
            <p class="mt-0.5 text-[10px] text-emerald-600">dalam {{ trendDays }} hari</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-[10px] text-slate-500">Jumlah Selesai (kini)</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ trendRows.at(-1)?.jumlahSelesai ?? 0 }}</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-[10px] text-slate-500">Jumlah Lulus (kini)</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ trendRows.at(-1)?.jumlahLulus ?? 0 }}</p>
          </div>
        </div>

        <!-- Trend table -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <TrendingUp class="h-4 w-4 text-violet-500" />
            <h2 class="text-sm font-semibold text-slate-800">Aktiviti Harian</h2>
            <span class="ml-auto text-xs text-slate-400">updated_at digunakan sebagai proksi tarikh</span>
          </div>

          <div v-if="loading.trend && trendRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Loading…</div>
          <div v-else-if="trendRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Tiada data</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Tarikh</th>
                  <th class="px-4 py-2 text-right font-medium">Halaman Selesai</th>
                  <th class="px-4 py-2 text-right font-medium">Review Lulus</th>
                  <th class="px-4 py-2 text-right font-medium">Kumulatif Selesai</th>
                  <th class="px-4 py-2 text-right font-medium">Kumulatif Lulus</th>
                  <th class="px-4 py-2 text-left font-medium w-32">Aktiviti</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr
                  v-for="row in [...trendRows].reverse()" :key="row.tarikh"
                  class="hover:bg-slate-50"
                  :class="(row.halamanSelesai > 0 || row.reviewLulus > 0) ? '' : 'opacity-50'"
                >
                  <td class="px-4 py-2 font-mono text-slate-700">{{ row.tarikh }}</td>
                  <td class="px-4 py-2 text-right">
                    <span v-if="row.halamanSelesai > 0" class="font-semibold text-violet-700">+{{ row.halamanSelesai }}</span>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <td class="px-4 py-2 text-right">
                    <span v-if="row.reviewLulus > 0" class="font-semibold text-emerald-700">+{{ row.reviewLulus }}</span>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <td class="px-4 py-2 text-right font-medium text-slate-700">{{ row.jumlahSelesai }}</td>
                  <td class="px-4 py-2 text-right font-medium text-slate-700">{{ row.jumlahLulus }}</td>
                  <td class="px-4 py-2">
                    <div v-if="row.halamanSelesai > 0 || row.reviewLulus > 0" class="flex gap-1">
                      <div
                        v-if="row.halamanSelesai > 0"
                        class="h-2 rounded-full bg-violet-500"
                        :style="{ width: Math.min(row.halamanSelesai * 8, 64) + 'px' }"
                        :title="`${row.halamanSelesai} halaman selesai`"
                      />
                      <div
                        v-if="row.reviewLulus > 0"
                        class="h-2 rounded-full bg-emerald-400"
                        :style="{ width: Math.min(row.reviewLulus * 4, 64) + 'px' }"
                        :title="`${row.reviewLulus} review lulus`"
                      />
                    </div>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- Tab 3: Senarai Halaman                                             -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'pages'" class="space-y-3">

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative min-w-[220px] flex-1">
            <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="pageSearch"
              type="text"
              placeholder="Cari spec ID, tajuk halaman…"
              class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm text-slate-700 shadow-sm outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-200"
            />
          </div>

          <!-- Done/Pending filter -->
          <div class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
            <button
              @click="pageIsDone = null"
              class="rounded px-3 py-1 text-xs font-medium transition-colors"
              :class="pageIsDone === null ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100'"
            >Semua</button>
            <button
              @click="pageIsDone = true"
              class="flex items-center gap-1 rounded px-3 py-1 text-xs font-medium transition-colors"
              :class="pageIsDone === true ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100'"
            >
              <CheckCircle2 class="h-3 w-3" />Selesai
            </button>
            <button
              @click="pageIsDone = false"
              class="flex items-center gap-1 rounded px-3 py-1 text-xs font-medium transition-colors"
              :class="pageIsDone === false ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100'"
            >
              <Clock class="h-3 w-3" />Pending
            </button>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
            <div class="flex items-center gap-2">
              <BarChart2 class="h-4 w-4 text-violet-500" />
              <h2 class="text-sm font-semibold text-slate-800">Senarai Halaman</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
              {{ pageRows.length }} / {{ pageTotal }} halaman
            </span>
          </div>

          <div v-if="loading.pages && pageRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Loading…</div>
          <div v-else-if="pageRows.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Tiada halaman ditemui</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-slate-500">
                <tr>
                  <th class="px-3 py-2 text-left font-medium">Spec ID</th>
                  <th class="px-3 py-2 text-left font-medium">Tajuk Halaman</th>
                  <th class="px-3 py-2 text-left font-medium">Modul</th>
                  <th class="px-3 py-2 text-center font-medium">Selesai</th>
                  <th class="px-2 py-2 text-center font-medium">BA</th>
                  <th class="px-2 py-2 text-center font-medium">QA</th>
                  <th class="px-2 py-2 text-center font-medium">Tech</th>
                  <th class="px-2 py-2 text-center font-medium">Dev</th>
                  <th class="px-3 py-2 text-center font-medium">Item Impl.</th>
                  <th class="px-3 py-2 text-left font-medium">Assignee</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="row in pageRows" :key="row.id" class="hover:bg-slate-50">
                  <td class="px-3 py-2 font-mono text-xs font-semibold text-violet-700">{{ row.specId }}</td>
                  <td class="max-w-[200px] truncate px-3 py-2 text-slate-700" :title="row.title">{{ row.title }}</td>
                  <td class="px-3 py-2">
                    <span v-if="row.module" class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-600">{{ row.module.code }}</span>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <CheckCircle2 v-if="row.isDone" class="inline h-4 w-4 text-emerald-500" />
                    <CircleDot v-else class="inline h-4 w-4 text-slate-300" />
                  </td>
                  <!-- Review badges -->
                  <td v-for="roleKey in (['ba','qa','tech','dev'] as const)" :key="roleKey" class="px-2 py-2 text-center">
                    <span
                      class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold"
                      :class="reviewBadgeClass(row.review[roleKey])"
                      :title="reviewBadgeTitle(row.review[roleKey])"
                    >{{ reviewBadgeLabel(row.review[roleKey]) }}</span>
                  </td>
                  <!-- Item implementation -->
                  <td class="px-3 py-2 text-center">
                    <template v-if="row.items.total > 0">
                      <div class="mx-auto w-16">
                        <div class="flex h-1.5 overflow-hidden rounded-full bg-slate-100">
                          <div class="h-1.5 bg-emerald-500"
                            :style="{ width: (row.items.implemented / row.items.total * 100) + '%' }" />
                          <div class="h-1.5 bg-amber-400"
                            :style="{ width: (row.items.partial / row.items.total * 100) + '%' }" />
                          <div class="h-1.5 bg-rose-400"
                            :style="{ width: (row.items.missing / row.items.total * 100) + '%' }" />
                        </div>
                        <p class="mt-0.5 text-[10px]" :class="itemPctClass(row.items.pct)">{{ row.items.pct ?? 0 }}%</p>
                      </div>
                    </template>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <!-- Assignees -->
                  <td class="max-w-[120px] px-3 py-2">
                    <div v-if="row.assignees.length" class="flex flex-wrap gap-1">
                      <span
                        v-for="name in row.assignees.slice(0,3)" :key="name"
                        class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600"
                        :title="name"
                      >{{ name.split(' ')[0] }}</span>
                      <span v-if="row.assignees.length > 3" class="text-[10px] text-slate-400">+{{ row.assignees.length - 3 }}</span>
                    </div>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pageTotalPg > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
            <p class="text-xs text-slate-500">
              {{ pageRows.length === 0 ? 0 : (pageCurrent - 1) * 50 + 1 }}–{{ (pageCurrent - 1) * 50 + pageRows.length }}
              daripada {{ pageTotal }} halaman
            </p>
            <div class="flex items-center gap-1">
              <button @click="pageCurrent--; loadPages()" :disabled="pageCurrent <= 1 || loading.pages"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40">‹ Prev</button>
              <template v-for="p in paginationPages" :key="typeof p === 'number' ? p : `e${p}`">
                <span v-if="p === '...'" class="px-1 text-xs text-slate-400">…</span>
                <button v-else @click="pageCurrent = p; loadPages()" :disabled="loading.pages"
                  class="min-w-[28px] rounded px-2 py-1 text-xs font-medium transition-colors"
                  :class="pageCurrent === p ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >{{ p }}</button>
              </template>
              <button @click="pageCurrent++; loadPages()" :disabled="pageCurrent >= pageTotalPg || loading.pages"
                class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-40">Next ›</button>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
