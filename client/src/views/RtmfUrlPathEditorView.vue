<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { LayoutGrid, Monitor, RefreshCw, Save, Trash2, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  captureRtmfUrlPathSnapshot,
  createRtmfUrlPath,
  deleteRtmfUrlPath,
  getRtmfUrlPath,
  updateRtmfUrlPath,
} from "@/api/rtmf";
import type { RtmfSnapshotStatus } from "@/types";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useToast } from "@/composables/useToast";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const confirmDialog = useConfirmDialog();

const id = computed(() => Number(route.params.id || 0));
const isEdit = computed(() => id.value > 0);

const vuePath = ref("");
const liveUrl = ref("");
const description = ref("");
const lineCount = ref<number | null>(null);
const fileSizeKb = ref<number | null>(null);
const snapshotStatus = ref<RtmfSnapshotStatus>(null);
const snapshotCapturedAt = ref<string | null>(null);
const snapshotHtml = ref<string | null>(null);
const frontendsCount = ref(0);

const capturing = ref(false);

async function load() {
  if (!isEdit.value) return;
  const r = await getRtmfUrlPath(id.value);
  const d = r.data;
  vuePath.value = d.vuePath || "";
  liveUrl.value = d.liveUrl || "";
  description.value = d.description || "";
  lineCount.value = d.lineCount ?? null;
  fileSizeKb.value = d.fileSizeKb ?? null;
  snapshotStatus.value = d.snapshotStatus ?? null;
  snapshotCapturedAt.value = d.snapshotCapturedAt ?? null;
  snapshotHtml.value = d.snapshotHtml ?? null;
  frontendsCount.value = d.frontendsCount ?? 0;
}

async function save() {
  const payload = { vuePath: vuePath.value.trim() || null, liveUrl: liveUrl.value.trim() || null, description: description.value.trim() || null };
  try {
    if (isEdit.value) { await updateRtmfUrlPath(id.value, payload); toast.success("URL Path updated"); }
    else { await createRtmfUrlPath(payload); toast.success("URL Path created"); }
    router.push("/admin/rtmf/url-paths");
  } catch (e) { toast.error("Save failed", e instanceof Error ? e.message : ""); }
}

async function recapture() {
  if (!isEdit.value) return;
  capturing.value = true;
  try {
    const r = await captureRtmfUrlPathSnapshot(id.value);
    const d = r.data;
    lineCount.value = d.lineCount ?? null;
    fileSizeKb.value = d.fileSizeKb ?? null;
    snapshotStatus.value = d.snapshotStatus ?? null;
    snapshotCapturedAt.value = d.snapshotCapturedAt ?? null;
    snapshotHtml.value = d.snapshotHtml ?? null;
    toast.success("Snapshot recaptured");
  } catch (e) { toast.error("Capture failed", e instanceof Error ? e.message : ""); }
  finally { capturing.value = false; }
}

async function remove() {
  const allowed = await confirmDialog.confirm({ title: "Delete URL path?", message: `Remove "${vuePath.value || 'this path'}"?`, confirmText: "Delete", destructive: true });
  if (!allowed) return;
  try { await deleteRtmfUrlPath(id.value); toast.success("URL Path deleted"); router.push("/admin/rtmf/url-paths"); }
  catch (e) { toast.error("Delete failed", e instanceof Error ? e.message : ""); }
}

onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <div>
        <nav class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
          <RouterLink to="/admin/rtmf/frontends" class="hover:text-violet-600 transition-colors">Page Catalog</RouterLink>
          <span class="text-slate-300">/</span>
          <RouterLink to="/admin/rtmf/url-paths" class="hover:text-violet-600 transition-colors">URL Paths</RouterLink>
          <span class="text-slate-300">/</span>
          <span class="text-slate-700">{{ isEdit ? 'Edit' : 'New' }}</span>
        </nav>
        <h1 class="page-title">{{ isEdit ? 'Edit URL Path' : 'New URL Path' }}</h1>
      </div>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <LayoutGrid class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">URL Path Details</h2>
          <span v-if="isEdit" class="ml-auto text-xs text-slate-500">{{ frontendsCount }} frontend entries</span>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2">
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Vue Path</label>
            <input v-model="vuePath" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="pages/profiling/asnaf/…/index.vue" />
          </div>
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Live URL</label>
            <input v-model="liveUrl" type="url" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="http://localhost:3000/…" />
          </div>
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Description</label>
            <textarea v-model="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
          </div>
        </div>
      </article>

      <!-- Snapshot / metadata -->
      <article v-if="isEdit" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <Monitor class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">UI Snapshot</h2>
          <span
            v-if="snapshotStatus"
            class="rounded-full px-2 py-0.5 text-xs font-semibold"
            :class="{ 'bg-emerald-100 text-emerald-700': snapshotStatus === 'ok', 'bg-amber-100 text-amber-700': snapshotStatus === 'not_found', 'bg-rose-100 text-rose-700': snapshotStatus === 'error' }"
          >{{ snapshotStatus }}</span>
          <span v-if="snapshotCapturedAt" class="text-xs text-slate-400">· captured {{ new Date(snapshotCapturedAt).toLocaleString() }}</span>
          <span v-if="lineCount" class="text-xs text-slate-500">· {{ lineCount.toLocaleString() }} lines · {{ fileSizeKb }} KB</span>
          <button type="button" class="ml-auto flex items-center gap-1.5 rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50 disabled:opacity-50" :disabled="capturing" @click="recapture">
            <RefreshCw class="h-3.5 w-3.5" :class="{ 'animate-spin': capturing }" />
            {{ snapshotHtml ? 'Re-capture' : 'Capture' }}
          </button>
        </div>
        <div class="p-4">
          <iframe v-if="snapshotHtml" :srcdoc="snapshotHtml" class="h-[600px] w-full rounded-lg border border-slate-200 bg-white" sandbox="allow-same-origin" title="UI Snapshot"></iframe>
          <div v-else class="flex h-[200px] items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-500">
            No snapshot yet. Click <span class="mx-1 font-semibold">Capture</span>.
          </div>
        </div>
      </article>

      <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800" @click="save"><Save class="h-4 w-4" />{{ isEdit ? 'Update' : 'Create' }}</button>
        <button class="flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50" @click="router.push('/admin/rtmf/url-paths')"><X class="h-4 w-4" />Cancel</button>
        <button v-if="isEdit" class="ml-auto flex items-center gap-2 rounded-lg border border-rose-200 px-5 py-2.5 text-sm font-medium text-rose-600 shadow-sm hover:bg-rose-50" @click="remove"><Trash2 class="h-4 w-4" />Delete</button>
      </div>
    </div>
  </AdminLayout>
</template>
