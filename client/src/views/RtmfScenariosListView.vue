<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { ChevronLeft, ChevronRight, GitBranch, Plus, Search } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listRtmfScenarios } from "@/api/rtmf";
import { useAuthStore } from "@/stores/auth";
import { useRtmfProjectStore } from "@/stores/rtmfProject";
import type { RtmfScenario } from "@/types";

const auth = useAuthStore();
const router = useRouter();
const projectStore = useRtmfProjectStore();

const scenarios = ref<RtmfScenario[]>([]);
const loading = ref(true);
const q = ref("");
const doneFilter = ref<"" | "1" | "0">("");
const page = ref(1);
const total = ref(0);
const totalPages = ref(1);
const limit = 20;

const rangeStart = computed(() => total.value === 0 ? 0 : (page.value - 1) * limit + 1);
const rangeEnd = computed(() => Math.min(page.value * limit, total.value));

async function load() {
  loading.value = true;
  try {
    const params = new URLSearchParams({ page: String(page.value), limit: String(limit) });
    if (q.value.trim()) params.set("q", q.value.trim());
    if (doneFilter.value !== "") params.set("is_done", doneFilter.value);
    const pid = projectStore.activeProjectId;
    if (pid) params.set("project_id", String(pid));
    const res = await listRtmfScenarios("?" + params.toString());
    scenarios.value = res.data;
    total.value = (res.meta?.total as number) ?? res.data.length;
    totalPages.value = (res.meta?.totalPages as number) ?? 1;
  } finally {
    loading.value = false;
  }
}

function search() {
  page.value = 1;
  load();
}

onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
            <RouterLink to="/admin/rtmf/frontends" class="transition-colors hover:text-violet-600">Page Catalog</RouterLink>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700">Custom Flow</span>
          </nav>
          <h1 class="page-title">Custom Flow</h1>
          <p class="mt-1 text-sm text-slate-500">Build and visualize page flow scenarios.</p>
        </div>
        <button
          v-if="projectStore.canEdit"
          class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800"
          @click="router.push('/admin/rtmf/scenarios/new')"
        >
          <Plus class="h-4 w-4" />
          Add Scenario
        </button>
      </div>

      <!-- Article -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">

        <!-- Toolbar -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-2.5">
          <div class="flex items-center gap-2">
            <GitBranch class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">Scenarios</h2>
            <span class="ml-1 text-xs text-slate-500">{{ total }} entries</span>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <select
              v-model="doneFilter"
              class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
              @change="search"
            >
              <option value="">All status</option>
              <option value="1">Done</option>
              <option value="0">Pending</option>
            </select>
            <div class="relative">
              <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                v-model="q"
                placeholder="Search scenarios…"
                class="w-56 rounded-lg border border-slate-300 py-1.5 pl-9 pr-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
                @keyup.enter="search"
              />
            </div>
            <button
              class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50"
              @click="search"
            >Filter</button>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="px-4 py-10 text-center text-sm text-slate-400">Loading…</div>

        <!-- Empty -->
        <div v-else-if="scenarios.length === 0" class="px-4 py-10 text-center text-sm text-slate-400">
          No scenarios yet.
          <RouterLink v-if="projectStore.canEdit" to="/admin/rtmf/scenarios/new" class="ml-1 text-violet-600 hover:underline">Create one →</RouterLink>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100 text-left">
                <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Title</th>
                <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Description</th>
                <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Steps</th>
                <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Done</th>
                <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Assigned</th>
                <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Created</th>

              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="s in scenarios"
                :key="s.id"
                class="cursor-pointer transition-colors hover:bg-slate-50"
                @click="router.push(`/admin/rtmf/scenarios/${s.id}`)"
              >
                <td class="px-3 py-2 font-medium text-slate-800">
                  <div class="flex items-center gap-2">
                    <GitBranch class="h-3.5 w-3.5 flex-shrink-0 text-violet-400" />
                    {{ s.title }}
                  </div>
                </td>
                <td class="max-w-xs px-3 py-2 text-slate-500">
                  <span class="line-clamp-1">{{ s.description ?? '—' }}</span>
                </td>
                <td class="px-3 py-2 text-center">
                  <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ s.stepsCount ?? 0 }}</span>
                </td>
                <td class="whitespace-nowrap px-3 py-2 text-center" @click.stop>
                  <span
                    :title="s.isDone ? 'Done' : 'Not done'"
                    class="inline-flex h-4 w-4 items-center justify-center rounded-full"
                    :class="s.isDone ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-300'"
                  >
                    <svg viewBox="0 0 12 12" class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="2,6 5,9 10,3" />
                    </svg>
                  </span>
                </td>
                <td class="px-3 py-2">
                  <div class="flex items-center justify-center -space-x-1">
                    <template v-for="(a, i) in (s.assignees ?? []).slice(0, 4)" :key="String(a.id)">
                      <div class="group relative" :style="{ zIndex: 10 - i }">
                        <img v-if="a.photoUrl" :src="a.photoUrl" class="h-6 w-6 rounded-full object-cover ring-2 ring-white" />
                        <div v-else class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-100 text-[10px] font-semibold text-violet-700 ring-2 ring-white">
                          {{ a.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="pointer-events-none absolute bottom-full left-1/2 mb-1.5 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-[11px] text-white opacity-0 transition-opacity group-hover:opacity-100">
                          {{ a.name }}
                          <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                        </div>
                      </div>
                    </template>
                    <div
                      v-if="(s.assignees?.length ?? 0) > 4"
                      class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-500 ring-2 ring-white"
                      style="z-index: 6"
                    >+{{ (s.assignees?.length ?? 0) - 4 }}</div>
                    <span v-if="!(s.assignees?.length)" class="text-xs text-slate-300">—</span>
                  </div>
                </td>
                <td class="whitespace-nowrap px-3 py-2 text-slate-400">{{ new Date(s.createdAt).toLocaleDateString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && total > 0" class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-2.5">
          <div class="text-sm text-slate-500">
            Showing <span class="font-medium text-slate-700">{{ rangeStart }}–{{ rangeEnd }}</span>
            of <span class="font-medium text-slate-700">{{ total }}</span>
          </div>
          <div class="flex items-center gap-2">
            <button
              class="flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 disabled:opacity-40"
              :disabled="page <= 1"
              @click="page--; load()"
            ><ChevronLeft class="h-3.5 w-3.5" />Previous</button>
            <span class="text-sm text-slate-500">Page {{ page }} of {{ totalPages }}</span>
            <button
              class="flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 disabled:opacity-40"
              :disabled="page >= totalPages"
              @click="page++; load()"
            >Next<ChevronRight class="h-3.5 w-3.5" /></button>
          </div>
        </div>

      </article>
    </div>
  </AdminLayout>
</template>
