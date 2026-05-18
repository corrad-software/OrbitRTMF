<script setup lang="ts">
import { ref, watch } from "vue";
import { Check, Image, Search, Upload, X } from "lucide-vue-next";
import { listAllAttachments } from "@/api/cms";
import type { AllAttachment } from "@/types";

const emit = defineEmits<{
  upload: [file: File];
  select: [item: AllAttachment];
  close: [];
}>();

// ── Tabs ──
const tab = ref<"upload" | "library">("upload");

// ── Upload tab ──
const dragOver = ref(false);
const pendingFile = ref<File | null>(null);

function onDrop(e: DragEvent) {
  dragOver.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (file && file.type.startsWith("image/")) pendingFile.value = file;
}
function onFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) pendingFile.value = file;
}
function confirmUpload() {
  if (pendingFile.value) emit("upload", pendingFile.value);
}

// ── Library tab ──
const q = ref("");
const page = ref(1);
const items = ref<AllAttachment[]>([]);
const totalPages = ref(1);
const loading = ref(false);
const selected = ref<AllAttachment | null>(null);

let seq = 0;
async function load() {
  const cur = ++seq;
  loading.value = true;
  selected.value = null;
  try {
    const params = new URLSearchParams({ page: String(page.value), limit: "30" });
    if (q.value) params.set("q", q.value);
    const res = await listAllAttachments(`?${params}`);
    if (cur !== seq) return;
    items.value = res.data.filter((a) => a.mimeType.startsWith("image/"));
    totalPages.value = (res.meta?.totalPages as number) ?? 1;
  } finally {
    if (cur === seq) loading.value = false;
  }
}

watch(tab, (t) => { if (t === "library") load(); });
watch(q, () => { page.value = 1; load(); });
watch(page, load);

function confirmSelect() {
  if (selected.value) emit("select", selected.value);
}
</script>

<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-8" @click.self="emit('close')">
    <div class="flex w-full max-w-4xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm" style="min-height: 560px">

      <!-- Header (matches card header style) -->
      <div class="flex shrink-0 items-center gap-2 border-b border-slate-100 px-4 py-3">
        <Image class="h-4 w-4 text-violet-600" />
        <h2 class="text-sm font-semibold text-slate-900">Set Mockup Image</h2>
        <button @click="emit('close')" class="ml-auto rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
          <X class="h-4 w-4" />
        </button>
      </div>

      <!-- Tabs (matches editor tab bar style) -->
      <div class="flex shrink-0 overflow-hidden border-b border-slate-200 bg-slate-50">
        <button
          @click="tab = 'upload'"
          class="flex items-center gap-2 border-b-2 px-5 py-2.5 text-sm font-medium transition-colors"
          :class="tab === 'upload' ? 'border-violet-600 bg-white text-violet-700' : 'border-transparent text-slate-500 hover:bg-white hover:text-slate-700'"
        >
          <Upload class="h-4 w-4" />
          Upload Files
        </button>
        <button
          @click="tab = 'library'"
          class="flex items-center gap-2 border-b-2 px-5 py-2.5 text-sm font-medium transition-colors"
          :class="tab === 'library' ? 'border-violet-600 bg-white text-violet-700' : 'border-transparent text-slate-500 hover:bg-white hover:text-slate-700'"
        >
          <Image class="h-4 w-4" />
          Media Library
        </button>
      </div>

      <!-- ── Upload Files ── -->
      <div v-if="tab === 'upload'" class="flex flex-1 flex-col items-center justify-center p-8">
        <div
          class="flex w-full max-w-lg flex-col items-center justify-center gap-4 rounded-lg border-2 border-dashed py-16 transition-colors"
          :class="dragOver ? 'border-violet-400 bg-violet-50' : pendingFile ? 'border-violet-300 bg-violet-50' : 'border-slate-200 bg-slate-50'"
          @dragover.prevent="dragOver = true"
          @dragleave="dragOver = false"
          @drop.prevent="onDrop"
        >
          <Upload class="h-10 w-10" :class="pendingFile ? 'text-violet-400' : 'text-slate-300'" />
          <div class="text-center">
            <p v-if="pendingFile" class="text-sm font-medium text-slate-700">{{ pendingFile.name }}</p>
            <p v-else class="text-sm font-medium text-slate-600">Drop files to upload</p>
            <p v-if="!pendingFile" class="mt-1 text-xs text-slate-400">or</p>
          </div>
          <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-300 px-4 py-1.5 text-sm font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50">
            {{ pendingFile ? 'Change file' : 'Select Files' }}
            <input type="file" accept="image/*" class="hidden" @change="onFileInput" />
          </label>
          <p class="text-xs text-slate-400">PNG, JPG, GIF, WebP</p>
        </div>

        <div v-if="pendingFile" class="mt-4 w-full max-w-lg">
          <button
            @click="confirmUpload"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800"
          >
            <Upload class="h-4 w-4" />
            Upload &amp; Set as Mockup
          </button>
        </div>
      </div>

      <!-- ── Media Library ── -->
      <div v-else class="flex min-h-0 flex-1 flex-col">

        <!-- Toolbar -->
        <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 px-4 py-2">
          <div class="relative">
            <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="q"
              placeholder="Search media…"
              class="rounded-lg border border-slate-300 py-1.5 pl-8 pr-3 text-sm shadow-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-100"
            />
          </div>
          <span class="text-xs text-slate-400">{{ items.length }} image{{ items.length !== 1 ? 's' : '' }}</span>
        </div>

        <!-- Grid -->
        <div class="min-h-0 flex-1 overflow-y-auto p-4">
          <div v-if="loading" class="flex h-48 items-center justify-center text-sm text-slate-400">Loading…</div>
          <div v-else-if="items.length === 0" class="flex h-48 flex-col items-center justify-center gap-2 text-sm text-slate-400">
            <Image class="h-8 w-8 text-slate-200" />
            No images found.
          </div>
          <div v-else class="grid grid-cols-4 gap-2 sm:grid-cols-5 md:grid-cols-6">
            <button
              v-for="item in items"
              :key="item.id"
              type="button"
              class="group relative aspect-square overflow-hidden rounded-lg border-2 bg-slate-100 transition-all"
              :class="selected?.id === item.id ? 'border-violet-500 shadow-md' : 'border-transparent hover:border-violet-300'"
              :title="item.originalName"
              @click="selected = item"
            >
              <img :src="item.url" :alt="item.originalName" class="h-full w-full object-cover" />
              <div v-if="selected?.id === item.id" class="absolute inset-0 flex items-start justify-end bg-violet-500/10 p-1">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-violet-600 shadow">
                  <Check class="h-3 w-3 text-white" stroke-width="3" />
                </span>
              </div>
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex shrink-0 items-center justify-center gap-2 border-t border-slate-100 px-4 py-2">
          <button :disabled="page === 1" @click="page--" class="rounded px-3 py-1 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30">‹ Prev</button>
          <span class="text-xs text-slate-400">{{ page }} / {{ totalPages }}</span>
          <button :disabled="page === totalPages" @click="page++" class="rounded px-3 py-1 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30">Next ›</button>
        </div>

        <!-- Footer -->
        <div class="flex shrink-0 items-center justify-between border-t border-slate-100 px-4 py-3">
          <div v-if="selected" class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-700">{{ selected.originalName }}</p>
            <p class="text-xs text-slate-400">{{ selected.sourceLabel }}</p>
          </div>
          <p v-else class="text-sm text-slate-400">No image selected</p>
          <button
            :disabled="!selected"
            @click="confirmSelect"
            class="ml-4 shrink-0 rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800 disabled:opacity-40"
          >
            Set as Mockup
          </button>
        </div>
      </div>

    </div>
  </div>
</template>
