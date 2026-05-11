<script setup lang="ts">
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { FileSpreadsheet, Download, Filter } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listRtmfModules } from "@/api/rtmf";
import { API_BASE_URL } from "@/env";
import type { RtmfModule } from "@/types";

const modules = ref<RtmfModule[]>([]);
const moduleFilter = ref<number | "">("");
const doneFilter = ref<"" | "1" | "0">("");
const downloading = ref(false);

onMounted(async () => {
  const res = await listRtmfModules();
  modules.value = res.data;
});

async function download() {
  downloading.value = true;
  try {
    const params = new URLSearchParams();
    if (moduleFilter.value) params.set("module_id", String(moduleFilter.value));
    if (doneFilter.value !== "") params.set("is_done", doneFilter.value);

    const url = `${API_BASE_URL}/api/rtmf-frontends/export/csv${params.size ? "?" + params.toString() : ""}`;
    const res = await fetch(url, { credentials: "include" });

    if (!res.ok) throw new Error("Export failed");

    const blob = await res.blob();
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = `page-catalog-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
  } finally {
    downloading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <div>
        <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
          <RouterLink to="/admin/rtmf/frontends" class="hover:text-violet-600 transition-colors">Page Catalog</RouterLink>
          <span class="text-slate-300">/</span>
          <span class="text-slate-700">Export</span>
        </nav>
        <h1 class="page-title">Export</h1>
        <p class="mt-1 text-sm text-slate-500">Download the page catalog as a CSV file.</p>
      </div>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
          <h2 class="text-sm font-semibold text-slate-900">CSV Export</h2>
        </div>
        <div class="space-y-5 p-6">

          <!-- Filters -->
          <div class="space-y-3">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
              <Filter class="h-3.5 w-3.5" />
              Filters <span class="font-normal normal-case text-slate-400">(optional — leave blank to export all)</span>
            </div>
            <div class="flex flex-wrap gap-3">
              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-600">Module</label>
                <select
                  v-model="moduleFilter"
                  class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-100"
                >
                  <option :value="''">All modules</option>
                  <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.code }} — {{ m.name }}</option>
                </select>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-600">Status</label>
                <select
                  v-model="doneFilter"
                  class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-100"
                >
                  <option value="">All</option>
                  <option value="1">Done only</option>
                  <option value="0">Pending only</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Columns preview -->
          <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Columns included</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="col in ['Page ID', 'Title', 'Module', 'Sub-module', 'Actors', 'Vue Path', 'URL Dev', 'URL Staging', 'URL Prod', 'Done', 'Business Requirement', 'Stakeholder Requirement', 'Description']"
                :key="col"
                class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600"
              >{{ col }}</span>
            </div>
          </div>

          <!-- Download button -->
          <button
            :disabled="downloading"
            class="flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:opacity-60"
            @click="download"
          >
            <Download class="h-4 w-4" :class="downloading ? 'animate-bounce' : ''" />
            {{ downloading ? 'Preparing…' : 'Download CSV' }}
          </button>

        </div>
      </article>
    </div>
  </AdminLayout>
</template>
