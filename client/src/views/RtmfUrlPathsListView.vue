<script setup lang="ts">
import { onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { LayoutGrid, Plus } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listRtmfUrlPaths } from "@/api/rtmf";
import type { RtmfUrlPath } from "@/types";

const router = useRouter();
const rows = ref<RtmfUrlPath[]>([]);

async function load() {
  const r = await listRtmfUrlPaths();
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
            <span class="text-slate-700">URL Paths</span>
          </nav>
          <h1 class="page-title">URL Paths</h1>
          <p class="mt-1 text-sm text-slate-500">Vue file paths + live URLs referenced by frontend entries. One file may back many entries.</p>
        </div>
        <button class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800" @click="router.push('/admin/rtmf/url-paths/new')">
          <Plus class="h-4 w-4" />Add URL Path
        </button>
      </div>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <LayoutGrid class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">All URL Paths</h2>
          <span class="ml-1 text-xs text-slate-500">{{ rows.length }}</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100 text-left">
                <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Vue Path</th>
                <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Live URL</th>
                <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Lines</th>
                <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Snapshot</th>
                <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Entries</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="u in rows" :key="u.id" class="cursor-pointer transition-colors hover:bg-slate-50" @click="router.push(`/admin/rtmf/url-paths/${u.id}`)">
                <td class="max-w-md truncate px-4 py-2 font-mono text-xs text-slate-700">{{ u.vuePath || '—' }}</td>
                <td class="max-w-xs truncate px-4 py-2 font-mono text-xs text-slate-500">{{ u.liveUrl || '—' }}</td>
                <td class="px-4 py-2 text-xs text-slate-500">
                  <template v-if="u.lineCount">{{ u.lineCount.toLocaleString() }} <span v-if="u.fileSizeKb" class="text-slate-400">({{ u.fileSizeKb }} KB)</span></template>
                  <template v-else>—</template>
                </td>
                <td class="px-4 py-2 text-center">
                  <span
                    :title="u.snapshotStatus ?? 'not captured'"
                    class="inline-block h-2.5 w-2.5 rounded-full"
                    :class="{
                      'bg-emerald-500': u.snapshotStatus === 'ok',
                      'bg-amber-400': u.snapshotStatus === 'not_found',
                      'bg-rose-500': u.snapshotStatus === 'error',
                      'bg-slate-300': !u.snapshotStatus,
                    }"
                  />
                </td>
                <td class="px-4 py-2 text-center">
                  <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ u.frontendsCount ?? 0 }}</span>
                </td>
              </tr>
              <tr v-if="rows.length === 0"><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">No URL paths yet.</td></tr>
            </tbody>
          </table>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
