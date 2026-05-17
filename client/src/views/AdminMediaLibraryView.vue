<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import {
  AlertCircle,
  Database,
  ExternalLink,
  FileText,
  Filter,
  FolderOpen,
  Image,
  LayoutGrid,
  LayoutList,
  RefreshCw,
  Search,
} from "lucide-vue-next";

import { API_BASE_URL } from "@/env";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listAllAttachments } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AllAttachment } from "@/types";

type ViewMode = "grid" | "list";
type SourceFilter = "all" | AllAttachment["source"];

const toast = useToast();

const rows = ref<AllAttachment[]>([]);
const loading = ref(false);
const error = ref("");
const q = ref("");
const sourceFilter = ref<SourceFilter>("all");
const viewMode = ref<ViewMode>("grid");
const page = ref(1);
const totalItems = ref(0);
const LIMIT = 60;

const sourceOptions: { value: SourceFilter; label: string }[] = [
  { value: "all", label: "All Sources" },
  { value: "media", label: "CMS Media" },
  { value: "frontend_attachment", label: "Page Attachments" },
  { value: "module_photo", label: "Module Photos" },
  { value: "submodule_photo", label: "Sub-Module Photos" },
  { value: "scenario_attachment", label: "Scenario Attachments" },
];

const sourceBadgeClass: Record<AllAttachment["source"], string> = {
  media: "bg-violet-100 text-violet-700",
  frontend_attachment: "bg-blue-100 text-blue-700",
  module_photo: "bg-emerald-100 text-emerald-700",
  submodule_photo: "bg-teal-100 text-teal-700",
  scenario_attachment: "bg-amber-100 text-amber-700",
};

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(LIMIT),
    });
    if (q.value) params.set("q", q.value);
    if (sourceFilter.value !== "all") params.set("source", sourceFilter.value);

    const res = await listAllAttachments(`?${params.toString()}`);
    rows.value = res.data;
    totalItems.value = (res.meta?.total as number) ?? res.data.length;
  } catch (e) {
    error.value = e instanceof Error ? e.message : "Failed to load attachments";
    toast.error("Load failed", error.value);
  } finally {
    loading.value = false;
  }
}

function onSearch() {
  page.value = 1;
  load();
}

watch(sourceFilter, () => {
  page.value = 1;
  load();
});

function isImage(mimeType: string) {
  return mimeType.startsWith("image/");
}

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

const totalPages = computed(() => Math.ceil(totalItems.value / LIMIT));

function prevPage() {
  if (page.value > 1) { page.value--; load(); }
}

function nextPage() {
  if (page.value < totalPages.value) { page.value++; load(); }
}

onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="page-title">Media Library</h1>
          <p class="mt-0.5 text-sm text-slate-500">All uploaded files across the system</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            :title="viewMode === 'grid' ? 'Switch to list' : 'Switch to grid'"
            class="rounded-md border border-slate-300 p-1.5 text-slate-600 hover:bg-slate-50"
            @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
          >
            <LayoutList v-if="viewMode === 'grid'" class="h-4 w-4" />
            <LayoutGrid v-else class="h-4 w-4" />
          </button>
          <button
            class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
            :disabled="loading"
            @click="load"
          >
            <RefreshCw class="h-3.5 w-3.5" :class="loading && 'animate-spin'" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Filters -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center gap-3 px-4 py-3">
          <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
            <Filter class="h-4 w-4" />
            Filter
          </div>

          <div class="relative flex-1 min-w-40">
            <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="q"
              type="text"
              placeholder="Search by filename…"
              class="w-full rounded-md border border-slate-300 bg-white py-1.5 pl-8 pr-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"
              @keyup.enter="onSearch"
            />
          </div>

          <select
            v-model="sourceFilter"
            class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"
          >
            <option v-for="opt in sourceOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>

          <button
            class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700"
            @click="onSearch"
          >
            Search
          </button>
        </div>
      </article>

      <!-- Error -->
      <div v-if="error" class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700">
        <AlertCircle class="h-4 w-4 shrink-0" />
        {{ error }}
      </div>

      <!-- Stats bar -->
      <div class="flex items-center gap-1.5 text-sm text-slate-500">
        <Database class="h-4 w-4" />
        <span>{{ totalItems }} file{{ totalItems !== 1 ? "s" : "" }} total</span>
        <span v-if="loading" class="ml-2 text-violet-500">Loading…</span>
      </div>

      <!-- Empty state -->
      <div v-if="!loading && rows.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-white py-16 text-center shadow-sm">
        <FolderOpen class="mb-3 h-10 w-10 text-slate-300" />
        <p class="text-sm font-medium text-slate-500">No files found</p>
        <p class="mt-1 text-xs text-slate-400">Try adjusting your filters.</p>
      </div>

      <!-- Grid view -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <div
          v-for="item in rows"
          :key="item.id"
          class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <!-- Thumbnail -->
          <div class="relative aspect-square bg-slate-100">
            <img
              v-if="isImage(item.mimeType)"
              :src="`${API_BASE_URL}${item.url}`"
              :alt="item.originalName"
              class="absolute inset-0 h-full w-full object-cover"
              loading="lazy"
            />
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center gap-1">
              <FileText class="h-8 w-8 text-slate-400" />
              <span class="text-[10px] font-medium uppercase text-slate-400">
                {{ item.mimeType.split("/").pop() }}
              </span>
            </div>
            <!-- Open link -->
            <a
              :href="`${API_BASE_URL}${item.url}`"
              target="_blank"
              rel="noopener"
              class="absolute right-1.5 top-1.5 flex h-6 w-6 items-center justify-center rounded-md bg-white/90 text-slate-500 opacity-0 shadow-sm transition-opacity hover:text-violet-600 group-hover:opacity-100"
              title="Open file"
              @click.stop
            >
              <ExternalLink class="h-3.5 w-3.5" />
            </a>
          </div>

          <!-- Info -->
          <div class="px-2.5 py-2 space-y-1">
            <p class="truncate text-xs font-medium text-slate-800" :title="item.originalName">
              {{ item.originalName }}
            </p>
            <span
              class="inline-block rounded-full px-1.5 py-0.5 text-[10px] font-medium"
              :class="sourceBadgeClass[item.source]"
            >
              {{ item.sourceLabel }}
            </span>
            <p v-if="item.context" class="truncate text-[10px] text-slate-400" :title="item.context">
              {{ item.context }}
            </p>
            <p class="text-[10px] text-slate-400">{{ formatSize(item.size) }} · {{ formatDate(item.createdAt) }}</p>
          </div>
        </div>
      </div>

      <!-- List view -->
      <article v-else class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
              <th class="px-4 py-2.5">File</th>
              <th class="px-4 py-2.5">Source</th>
              <th class="px-4 py-2.5">Context</th>
              <th class="px-4 py-2.5">Type</th>
              <th class="px-4 py-2.5">Size</th>
              <th class="px-4 py-2.5">Uploaded</th>
              <th class="px-4 py-2.5 text-right">Open</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in rows" :key="item.id" class="hover:bg-slate-50">
              <td class="max-w-xs px-4 py-2.5">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-100">
                    <img
                      v-if="isImage(item.mimeType)"
                      :src="`${API_BASE_URL}${item.url}`"
                      :alt="item.originalName"
                      class="h-full w-full object-cover"
                      loading="lazy"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                      <FileText class="h-4 w-4 text-slate-400" />
                    </div>
                  </div>
                  <span class="truncate text-xs font-medium text-slate-800">{{ item.originalName }}</span>
                </div>
              </td>
              <td class="px-4 py-2.5">
                <span
                  class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="sourceBadgeClass[item.source]"
                >
                  {{ item.sourceLabel }}
                </span>
              </td>
              <td class="max-w-[140px] px-4 py-2.5">
                <span class="truncate block text-xs text-slate-500">{{ item.context ?? "—" }}</span>
              </td>
              <td class="px-4 py-2.5 text-xs text-slate-500">{{ item.mimeType }}</td>
              <td class="px-4 py-2.5 text-xs text-slate-500">{{ formatSize(item.size) }}</td>
              <td class="px-4 py-2.5 text-xs text-slate-500">{{ formatDate(item.createdAt) }}</td>
              <td class="px-4 py-2.5 text-right">
                <a
                  :href="`${API_BASE_URL}${item.url}`"
                  target="_blank"
                  rel="noopener"
                  class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 hover:text-violet-600"
                >
                  <ExternalLink class="h-3 w-3" />
                  Open
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </article>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between text-sm text-slate-600">
        <span>Page {{ page }} of {{ totalPages }}</span>
        <div class="flex gap-2">
          <button
            class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium disabled:opacity-40 hover:bg-slate-50"
            :disabled="page <= 1"
            @click="prevPage"
          >
            Previous
          </button>
          <button
            class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium disabled:opacity-40 hover:bg-slate-50"
            :disabled="page >= totalPages"
            @click="nextPage"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
