<script setup lang="ts">
import { onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { LayoutGrid, Plus } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listRtmfActors } from "@/api/rtmf";
import { useAuthStore } from "@/stores/auth";
import { useRtmfProjectStore } from "@/stores/rtmfProject";
import type { RtmfActor } from "@/types";

const auth = useAuthStore();
const router = useRouter();
const projectStore = useRtmfProjectStore();
const rows = ref<RtmfActor[]>([]);

async function load() {
  const pid = projectStore.activeProjectId;
  const params = pid ? `?project_id=${pid}` : "";
  const r = await listRtmfActors(params);
  rows.value = r.data;
}
onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
            <RouterLink to="/admin/rtmf/frontends" class="hover:text-violet-600 transition-colors">Page Catalog</RouterLink>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700">Actors</span>
          </nav>
          <h1 class="page-title">Actors</h1>
          <p class="mt-1 text-sm text-slate-500">Roles / personas that interact with the frontends.</p>
        </div>
        <button v-if="projectStore.canEdit" class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800" @click="router.push('/admin/rtmf/actors/new')">
          <Plus class="h-4 w-4" />Add Actor
        </button>
      </div>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <LayoutGrid class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">All Actors</h2>
          <span class="ml-1 text-xs text-slate-500">{{ rows.length }}</span>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 text-left">
              <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
              <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Description</th>
              <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Entries</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="a in rows" :key="a.id" class="cursor-pointer transition-colors hover:bg-slate-50" @click="router.push(`/admin/rtmf/actors/${a.id}`)">
              <td class="px-4 py-2 font-medium text-slate-800">{{ a.name }}</td>
              <td class="max-w-md truncate px-4 py-2 text-sm text-slate-500">{{ a.description || '—' }}</td>
              <td class="px-4 py-2 text-center">
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ a.frontendsCount ?? 0 }}</span>
              </td>
            </tr>
            <tr v-if="rows.length === 0"><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">No actors yet.</td></tr>
          </tbody>
        </table>
      </article>
    </div>
  </AdminLayout>
</template>
