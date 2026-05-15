<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { CheckCircle2, Clock, ClipboardList, Search, RefreshCw } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listRtmfFrontends, listRtmfProjects } from "@/api/rtmf";
import type { RtmfFrontend, RtmfProject } from "@/types";

const router = useRouter();

type TabId = "all" | "pending" | "in-progress" | "completed";
const activeTab = ref<TabId>("all");
const searchQ = ref("");
const loading = ref(false);

const pages = ref<RtmfFrontend[]>([]);
const projects = ref<RtmfProject[]>([]);
const selectedProject = ref<number | null>(null);
const currentPage = ref(1);
const perPage = 10;

const tabs: { id: TabId; label: string }[] = [
  { id: "all",         label: "All" },
  { id: "pending",     label: "Pending" },
  { id: "in-progress", label: "In Progress" },
  { id: "completed",   label: "Completed" },
];

function pageStatus(p: RtmfFrontend): TabId {
  if (p.isDone) return "completed";
  if (p.vuePath) return "in-progress";
  return "pending";
}

const counts = computed(() => ({
  total:       pages.value.length,
  pending:     pages.value.filter(p => pageStatus(p) === "pending").length,
  inProgress:  pages.value.filter(p => pageStatus(p) === "in-progress").length,
  completed:   pages.value.filter(p => pageStatus(p) === "completed").length,
}));

const filtered = computed(() => {
  let list = pages.value;
  if (activeTab.value !== "all") list = list.filter(p => pageStatus(p) === activeTab.value);
  if (searchQ.value.trim()) {
    const q = searchQ.value.toLowerCase();
    list = list.filter(p =>
      p.title.toLowerCase().includes(q) ||
      p.specId.toLowerCase().includes(q) ||
      (p.module?.name ?? "").toLowerCase().includes(q)
    );
  }
  const order: Record<string, number> = { pending: 0, "in-progress": 1, completed: 2 };
  return [...list].sort((a, b) => (order[pageStatus(a)] ?? 9) - (order[pageStatus(b)] ?? 9));
});

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)));
const paginated = computed(() => filtered.value.slice((currentPage.value - 1) * perPage, currentPage.value * perPage));

const paginationPages = computed((): (number | "...")[] => {
  const total = totalPages.value;
  const cur = currentPage.value;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const result: (number | "...")[] = [1];
  if (cur > 3) result.push("...");
  for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) result.push(i);
  if (cur < total - 2) result.push("...");
  result.push(total);
  return result;
});

watch([activeTab, searchQ], () => { currentPage.value = 1; });

async function load() {
  loading.value = true;
  try {
    const params = new URLSearchParams({ limit: "500" });
    if (selectedProject.value) params.set("project_id", String(selectedProject.value));
    const res = await listRtmfFrontends(`?${params}`);
    pages.value = res.data;
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  const res = await listRtmfProjects();
  projects.value = res.data;
  if (res.data.length === 1) selectedProject.value = res.data[0].id;
  await load();
});
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h1 class="page-title">My Task</h1>
          <p class="mt-0.5 text-sm text-slate-500">Page catalog tasks from the RTMF project</p>
        </div>
        <div class="flex items-center gap-2">
          <select
            v-if="projects.length > 1"
            v-model="selectedProject"
            @change="load"
            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 outline-none focus:border-slate-400"
          >
            <option :value="null">All Projects</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <button
            @click="load"
            :disabled="loading"
            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 disabled:opacity-50"
          >
            <RefreshCw class="h-3.5 w-3.5" :class="loading ? 'animate-spin' : ''" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
          <ClipboardList class="h-4 w-4 text-slate-400" />
          <div>
            <p class="text-[11px] text-slate-500">Total Pages</p>
            <p class="text-lg font-semibold text-slate-800">{{ counts.total }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
          <ClipboardList class="h-4 w-4 text-amber-400" />
          <div>
            <p class="text-[11px] text-amber-500">Pending</p>
            <p class="text-lg font-semibold text-amber-700">{{ counts.pending }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3">
          <Clock class="h-4 w-4 text-sky-400" />
          <div>
            <p class="text-[11px] text-sky-500">In Progress</p>
            <p class="text-lg font-semibold text-sky-700">{{ counts.inProgress }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
          <CheckCircle2 class="h-4 w-4 text-emerald-400" />
          <div>
            <p class="text-[11px] text-emerald-500">Completed</p>
            <p class="text-lg font-semibold text-emerald-700">{{ counts.completed }}</p>
          </div>
        </div>
      </div>

      <!-- Tab bar -->
      <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-0 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="shrink-0 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="activeTab === tab.id
              ? 'border-b-2 border-violet-600 text-violet-700'
              : 'text-slate-500 hover:text-slate-700'"
          >
            {{ tab.label }}
            <span
              v-if="tab.id !== 'all'"
              class="ml-1.5 rounded-full px-1.5 py-px text-[10px] font-semibold"
              :class="activeTab === tab.id ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-500'"
            >
              {{ pages.filter(p => pageStatus(p) === tab.id).length }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Search + list -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <p class="text-xs text-slate-500">{{ filtered.length }} page{{ filtered.length !== 1 ? 's' : '' }}</p>
          <div class="relative">
            <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQ"
              type="text"
              placeholder="Search title, spec ID, module…"
              class="w-56 rounded-lg border border-slate-200 py-1.5 pl-8 pr-3 text-xs text-slate-700 outline-none focus:border-slate-400"
            />
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div v-if="loading" class="px-4 py-10 text-center text-sm text-slate-400">Loading…</div>
          <div v-else-if="filtered.length === 0" class="px-4 py-10 text-center text-sm text-slate-400">No pages found.</div>
          <ul v-else class="divide-y divide-slate-100">
            <li
              v-for="p in paginated"
              :key="p.id"
              class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition-colors hover:bg-slate-50"
              @click="router.push(`/admin/rtmf/frontends/${p.id}`)"
            >
              <span class="h-2 w-2 flex-shrink-0 rounded-full" :class="{
                'bg-sky-500':     pageStatus(p) === 'in-progress',
                'bg-amber-400':   pageStatus(p) === 'pending',
                'bg-emerald-400': pageStatus(p) === 'completed',
              }" />

              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <p class="truncate text-xs font-medium" :class="p.isDone ? 'text-slate-400 line-through' : 'text-slate-800'">
                    {{ p.title }}
                  </p>
                  <span class="flex-shrink-0 rounded px-1.5 py-px text-[10px] font-medium" :class="{
                    'bg-sky-100 text-sky-700':         pageStatus(p) === 'in-progress',
                    'bg-amber-100 text-amber-700':     pageStatus(p) === 'pending',
                    'bg-emerald-100 text-emerald-700': pageStatus(p) === 'completed',
                  }">
                    {{ pageStatus(p) === 'in-progress' ? 'In Progress' : pageStatus(p) === 'completed' ? 'Completed' : 'Pending' }}
                  </span>
                </div>
                <div class="mt-0.5 flex items-center gap-3 text-[11px] text-slate-400">
                  <span class="font-mono">{{ p.specId }}</span>
                  <span v-if="p.module">{{ p.module.name }}</span>
                  <span v-if="p.vuePath" class="text-sky-500">{{ p.vuePath }}</span>
                </div>
              </div>
            </li>
          </ul>
          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5">
            <p class="text-xs text-slate-400">
              {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filtered.length) }} of {{ filtered.length }}
            </p>
            <div class="flex items-center gap-1">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="rounded px-2 py-1 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30"
              >‹</button>
              <template v-for="pg in paginationPages" :key="pg">
                <span v-if="pg === '...'" class="px-1 text-xs text-slate-400">…</span>
                <button
                  v-else
                  @click="currentPage = pg as number"
                  class="min-w-[28px] rounded px-2 py-1 text-xs font-medium"
                  :class="currentPage === pg ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >{{ pg }}</button>
              </template>
              <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="rounded px-2 py-1 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30"
              >›</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
