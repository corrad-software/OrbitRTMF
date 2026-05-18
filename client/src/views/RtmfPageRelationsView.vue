<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { Share2, ArrowRight, RefreshCw, Search, ChevronDown, ChevronRight } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import { fetchRtmfRelations } from "@/api/rtmf";
import { useRtmfProjectStore } from "@/stores/rtmfProject";
import type { RtmfRelationEdge } from "@/types";

const router = useRouter();
const projectStore = useRtmfProjectStore();

const edges = ref<RtmfRelationEdge[]>([]);
const loading = ref(false);
let loadSeq = 0;

async function load() {
  const seq = ++loadSeq;
  loading.value = true;
  try {
    const pid = projectStore.activeProjectId;
    const params = pid ? `?project_id=${pid}` : "";
    const res = await fetchRtmfRelations(params);
    if (seq === loadSeq) {
      edges.value = res.data;
      collapsed.value = {};
    }
  } finally {
    if (seq === loadSeq) loading.value = false;
  }
}

onMounted(load);
watch(() => projectStore.activeProjectId, load);

// ── Filters ──
const search = ref("");
const moduleFilter = ref("");

// Derive unique module prefixes from specId (e.g. "PRF" from "PRF-01-DA")
const moduleOptions = computed(() => {
  const seen = new Set<string>();
  for (const e of edges.value) {
    const prefix = e.fromSpecId.split("-")[0];
    if (prefix) seen.add(prefix);
  }
  return [...seen].sort();
});

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase();
  return edges.value.filter((e) => {
    if (moduleFilter.value && e.fromSpecId.split("-")[0] !== moduleFilter.value) return false;
    if (!q) return true;
    return (
      e.fromTitle.toLowerCase().includes(q) ||
      e.fromSpecId.toLowerCase().includes(q) ||
      e.toTitle.toLowerCase().includes(q) ||
      e.toSpecId.toLowerCase().includes(q) ||
      (e.condition ?? "").toLowerCase().includes(q) ||
      (e.itemLabel ?? "").toLowerCase().includes(q) ||
      (e.itemType ?? "").toLowerCase().includes(q)
    );
  });
});

// Group filtered edges by source page
type Group = { fromId: number; fromSpecId: string; fromTitle: string; edges: RtmfRelationEdge[] };

const groups = computed<Group[]>(() => {
  const map = new Map<number, Group>();
  for (const e of filtered.value) {
    if (!map.has(e.fromId)) {
      map.set(e.fromId, { fromId: e.fromId, fromSpecId: e.fromSpecId, fromTitle: e.fromTitle, edges: [] });
    }
    map.get(e.fromId)!.edges.push(e);
  }
  return [...map.values()].sort((a, b) => a.fromSpecId.localeCompare(b.fromSpecId));
});

// Collapsed state — all expanded by default
const collapsed = ref<Record<number, boolean>>({});

function toggleGroup(id: number) {
  collapsed.value[id] = !collapsed.value[id];
}

function goToPage(id: number) {
  router.push(`/admin/rtmf/frontends/${id}`);
}

// ── Tabs ──
const activeTab = ref<"list" | "diagram">("list");

// ── Diagram — left-to-right level layout ──
const DN_W = 176, DN_H = 56, DG_X = 80, DG_Y = 12, DP = 28;

const diagramData = computed(() => {
  if (filtered.value.length === 0) return null;

  const pageMap = new Map<number, { id: number; specId: string; title: string; level: number }>();
  for (const e of filtered.value) {
    if (!pageMap.has(e.fromId)) pageMap.set(e.fromId, { id: e.fromId, specId: e.fromSpecId, title: e.fromTitle, level: 0 });
    if (!pageMap.has(e.toId))   pageMap.set(e.toId,   { id: e.toId,   specId: e.toSpecId,   title: e.toTitle,   level: 0 });
  }

  // Propagate depth levels via relaxation (handles chains and branches)
  for (let i = 0; i < pageMap.size; i++) {
    for (const e of filtered.value) {
      const src = pageMap.get(e.fromId)!;
      const tgt = pageMap.get(e.toId)!;
      if (src.id !== tgt.id && tgt.level <= src.level) tgt.level = src.level + 1;
    }
  }

  // Group by level into columns
  const cols = new Map<number, { id: number; specId: string; title: string; level: number }[]>();
  for (const p of pageMap.values()) {
    if (!cols.has(p.level)) cols.set(p.level, []);
    cols.get(p.level)!.push(p);
  }

  // Position nodes — vertically centre-align each column
  const sortedCols = [...cols.entries()].sort((a, b) => a[0] - b[0]);
  const maxRows = Math.max(...sortedCols.map(([, ps]) => ps.length));
  const totalColH = maxRows * (DN_H + DG_Y) - DG_Y;

  const nodes: { id: number; specId: string; title: string; x: number; y: number }[] = [];
  for (const [level, pages] of sortedCols) {
    const colH = pages.length * (DN_H + DG_Y) - DG_Y;
    const startY = DP + (totalColH - colH) / 2;
    pages.sort((a, b) => a.specId.localeCompare(b.specId)).forEach((p, idx) => {
      nodes.push({ id: p.id, specId: p.specId, title: p.title, x: DP + level * (DN_W + DG_X), y: startY + idx * (DN_H + DG_Y) });
    });
  }

  // Build deduplicated SVG edge paths
  const pos = new Map(nodes.map(n => [n.id, n]));
  const seenEdge = new Set<string>();
  const svgEdges: { path: string; lx: number; ly: number }[] = [];
  for (const e of filtered.value) {
    const key = `${e.fromId}→${e.toId}`;
    if (seenEdge.has(key)) continue;
    seenEdge.add(key);
    const s = pos.get(e.fromId), t = pos.get(e.toId);
    if (!s || !t) continue;
    const x1 = s.x + DN_W, y1 = s.y + DN_H / 2;
    const x2 = t.x,        y2 = t.y + DN_H / 2;
    const cx = (x1 + x2) / 2;
    svgEdges.push({ path: `M ${x1} ${y1} C ${cx} ${y1} ${cx} ${y2} ${x2} ${y2}`, lx: cx, ly: (y1 + y2) / 2 - 6 });
  }

  const maxLevel = Math.max(...[...pageMap.values()].map(p => p.level));
  const svgW = DP * 2 + (maxLevel + 1) * DN_W + maxLevel * DG_X;
  const svgH = DP * 2 + totalColH;
  return { nodes, edges: svgEdges, svgW, svgH };
});
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
            <RouterLink to="/admin/rtmf/frontends" class="transition-colors hover:text-violet-600">Page Catalog</RouterLink>
            <span class="text-slate-300">/</span>
            <RouterLink to="/admin/rtmf/scenarios" class="transition-colors hover:text-violet-600">Flow Scenarios</RouterLink>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700">Page Relations</span>
          </nav>
          <h1 class="page-title">Page Relations</h1>
          <p class="mt-0.5 text-sm text-slate-500">All wired-up page connections via form item actions — auto-generated from the Page Catalog.</p>
        </div>
        <button
          @click="load"
          :disabled="loading"
          class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 disabled:opacity-50"
        >
          <RefreshCw class="h-3.5 w-3.5" :class="loading ? 'animate-spin' : ''" />
          Refresh
        </button>
      </div>

      <!-- Filter bar -->
      <div class="flex flex-wrap items-center gap-2">
        <select
          v-model="moduleFilter"
          class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200"
        >
          <option value="">All modules</option>
          <option v-for="m in moduleOptions" :key="m" :value="m">{{ m }}</option>
        </select>

        <div class="relative min-w-48 flex-1">
          <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
          <input
            v-model="search"
            type="text"
            placeholder="Search pages, items, conditions…"
            class="w-full rounded-lg border border-slate-300 bg-white py-1.5 pl-8 pr-3 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200"
          />
        </div>

        <span class="ml-auto text-xs text-slate-500">
          <span class="font-semibold text-slate-700">{{ filtered.length }}</span>
          connection{{ filtered.length !== 1 ? 's' : '' }}
        </span>
      </div>

      <!-- Tab switcher -->
      <div class="flex gap-1 rounded-lg border border-slate-200 bg-slate-50 p-0.5 w-fit">
        <button
          v-for="tab in [{ id: 'list', label: 'List' }, { id: 'diagram', label: 'Diagram' }]"
          :key="tab.id"
          @click="activeTab = (tab.id as 'list' | 'diagram')"
          :class="activeTab === tab.id ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'"
          class="rounded-md px-3 py-1 text-xs font-medium transition-colors"
        >{{ tab.label }}</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="py-16 text-center text-sm text-slate-400">Loading…</div>

      <!-- Empty -->
      <div v-else-if="groups.length === 0" class="rounded-lg border border-slate-200 bg-white py-16 text-center shadow-sm">
        <Share2 class="mx-auto mb-3 h-8 w-8 text-slate-300" />
        <p class="text-sm font-medium text-slate-500">No page connections found.</p>
        <p class="mt-1 text-xs text-slate-400">Add Action-type form items with a page condition in the Page Catalog editor.</p>
      </div>

      <!-- Groups table (List tab) -->
      <div v-else-if="activeTab === 'list'" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div
          v-for="(group, gi) in groups"
          :key="group.fromId"
          :class="gi > 0 ? 'border-t border-slate-100' : ''"
        >
          <!-- Group header — source page -->
          <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50">
            <button
              class="flex flex-1 items-center gap-3 text-left"
              @click="toggleGroup(group.fromId)"
            >
              <component :is="collapsed[group.fromId] ? ChevronRight : ChevronDown" class="h-4 w-4 shrink-0 text-slate-400" />
              <span class="rounded border border-violet-200 bg-violet-50 px-2 py-0.5 font-mono text-[11px] font-semibold text-violet-700">
                {{ group.fromSpecId }}
              </span>
              <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800">{{ group.fromTitle }}</span>
              <span class="shrink-0 rounded-full bg-white border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-500">
                {{ group.edges.length }} connection{{ group.edges.length !== 1 ? 's' : '' }}
              </span>
            </button>
            <button
              class="shrink-0 rounded px-2 py-1 text-xs font-medium text-slate-400 transition-colors hover:bg-violet-50 hover:text-violet-600"
              @click="goToPage(group.fromId)"
            >
              Open →
            </button>
          </div>

          <!-- Connection rows -->
          <div v-if="!collapsed[group.fromId]" class="divide-y divide-slate-50">
            <div
              v-for="edge in group.edges"
              :key="edge.itemId"
              class="flex items-center gap-3 py-2 pl-12 pr-4"
            >
              <!-- Item type + label -->
              <div class="flex w-52 shrink-0 items-center gap-1.5 overflow-hidden">
                <span
                  v-if="edge.itemType"
                  class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600"
                >{{ edge.itemType }}</span>
                <span class="truncate text-xs text-slate-500">{{ edge.itemLabel ?? '—' }}</span>
              </div>

              <!-- Condition -->
              <div class="w-44 shrink-0">
                <span v-if="edge.condition" class="truncate text-xs italic text-slate-600">{{ edge.condition }}</span>
                <span v-else class="text-xs text-slate-300">—</span>
              </div>

              <!-- Arrow -->
              <ArrowRight class="h-3.5 w-3.5 shrink-0 text-slate-300" />

              <!-- Target page -->
              <button
                class="group flex min-w-0 flex-1 items-center gap-2 text-left"
                @click="goToPage(edge.toId)"
              >
                <span class="shrink-0 rounded border border-sky-200 bg-sky-50 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-sky-700 transition-colors group-hover:bg-sky-100">
                  {{ edge.toSpecId }}
                </span>
                <span class="min-w-0 truncate text-xs text-slate-600 transition-colors group-hover:text-violet-600">
                  {{ edge.toTitle }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Diagram tab -->
      <div v-else-if="!loading && activeTab === 'diagram'" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div v-if="!diagramData" class="py-16 text-center text-sm text-slate-400">No connections to display.</div>
        <div v-else class="overflow-auto p-4">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            :width="diagramData.svgW"
            :height="diagramData.svgH"
            class="block"
          >
            <defs>
              <marker id="rel-arrow" markerWidth="8" markerHeight="8" refX="8" refY="3" orient="auto">
                <path d="M0,0 L0,6 L8,3 z" fill="#a78bfa" />
              </marker>
            </defs>

            <!-- Edges (behind nodes) -->
            <path
              v-for="(edge, ei) in diagramData.edges"
              :key="`edge-${ei}`"
              :d="edge.path"
              fill="none"
              stroke="#a78bfa"
              stroke-width="1.5"
              stroke-linecap="round"
              marker-end="url(#rel-arrow)"
            />

            <!-- Nodes -->
            <g
              v-for="n in diagramData.nodes"
              :key="`node-${n.id}`"
              class="cursor-pointer"
              @click="goToPage(n.id)"
            >
              <rect
                :x="n.x" :y="n.y"
                :width="DN_W" :height="DN_H"
                rx="6"
                fill="#ede9fe"
                stroke="#a78bfa"
                stroke-width="1.5"
                class="hover:fill-violet-200 transition-colors"
              />
              <text
                :x="n.x + DN_W / 2" :y="n.y + 22"
                text-anchor="middle"
                font-size="10"
                font-family="monospace"
                font-weight="700"
                fill="#6d28d9"
              >{{ n.specId }}</text>
              <text
                :x="n.x + DN_W / 2" :y="n.y + 39"
                text-anchor="middle"
                font-size="11"
                font-family="sans-serif"
                fill="#7c3aed"
              >{{ n.title.length > 20 ? n.title.slice(0, 19) + '…' : n.title }}</text>
            </g>
          </svg>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
