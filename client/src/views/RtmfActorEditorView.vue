<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { LayoutGrid, Save, Trash2, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { createRtmfActor, deleteRtmfActor, getRtmfActor, updateRtmfActor } from "@/api/rtmf";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useToast } from "@/composables/useToast";
import { useRtmfProjectStore } from "@/stores/rtmfProject";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const confirmDialog = useConfirmDialog();
const projectStore = useRtmfProjectStore();

const id = computed(() => Number(route.params.id || 0));
const isEdit = computed(() => id.value > 0);

const name = ref("");
const description = ref("");
const sortOrder = ref(0);
const frontendsCount = ref(0);

async function load() {
  if (!isEdit.value) return;
  const r = await getRtmfActor(id.value);
  name.value = r.data.name;
  description.value = r.data.description || "";
  sortOrder.value = r.data.sortOrder ?? 0;
  frontendsCount.value = r.data.frontendsCount ?? 0;
}

async function save() {
  const base = { name: name.value.trim(), description: description.value.trim() || null, sortOrder: sortOrder.value };
  try {
    if (isEdit.value) { await updateRtmfActor(id.value, base); toast.success("Actor updated"); }
    else { await createRtmfActor({ ...base, projectId: projectStore.activeProjectId ?? undefined }); toast.success("Actor created"); }
    router.push("/admin/rtmf/actors");
  } catch (e) { toast.error("Save failed", e instanceof Error ? e.message : ""); }
}

async function remove() {
  const allowed = await confirmDialog.confirm({ title: "Delete actor?", message: `Remove "${name.value}"?`, confirmText: "Delete", destructive: true });
  if (!allowed) return;
  try { await deleteRtmfActor(id.value); toast.success("Actor deleted"); router.push("/admin/rtmf/actors"); }
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
          <RouterLink to="/admin/rtmf/actors" class="hover:text-violet-600 transition-colors">Actors</RouterLink>
          <span class="text-slate-300">/</span>
          <span class="text-slate-700">{{ isEdit ? 'Edit' : 'New' }}</span>
        </nav>
        <h1 class="page-title">{{ isEdit ? 'Edit Actor' : 'New Actor' }}</h1>
      </div>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <LayoutGrid class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">Actor Details</h2>
          <span v-if="isEdit" class="ml-auto text-xs text-slate-500">{{ frontendsCount }} frontend entries</span>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2">
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Name</label>
            <input v-model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Pemohon/Pendaftar" />
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-medium text-slate-700">Sort Order</label>
            <input v-model.number="sortOrder" type="number" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
          </div>
          <div class="space-y-1.5 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Description</label>
            <textarea v-model="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
          </div>
        </div>
      </article>
      <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800" @click="save"><Save class="h-4 w-4" />{{ isEdit ? 'Update' : 'Create' }}</button>
        <button class="flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50" @click="router.push('/admin/rtmf/actors')"><X class="h-4 w-4" />Cancel</button>
        <button v-if="isEdit" class="ml-auto flex items-center gap-2 rounded-lg border border-rose-200 px-5 py-2.5 text-sm font-medium text-rose-600 shadow-sm hover:bg-rose-50" @click="remove"><Trash2 class="h-4 w-4" />Delete</button>
      </div>
    </div>
  </AdminLayout>
</template>
