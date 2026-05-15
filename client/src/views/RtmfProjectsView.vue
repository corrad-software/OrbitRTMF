<script setup lang="ts">
import { onMounted, ref, reactive } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { FolderKanban, Plus, Pencil, Trash2, Check, X, Users, ChevronRight } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  listRtmfProjects,
  listRtmfProjectMembers,
  createRtmfProject,
  updateRtmfProject,
  deleteRtmfProject,
} from "@/api/rtmf";
import { useRtmfProjectStore } from "@/stores/rtmfProject";
import { useToast } from "@/composables/useToast";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import type { RtmfProject, RtmfProjectMember } from "@/types";

const router = useRouter();
const store = useRtmfProjectStore();
const toast = useToast();
const { confirm } = useConfirmDialog();

const rows = ref<RtmfProject[]>([]);
const membersByProject = ref<Record<number, RtmfProjectMember[]>>({});
const adding = ref(false);
const editingId = ref<number | null>(null);
const saving = ref(false);

const form = reactive({ code: "", name: "", description: "" });

const CARD_ACCENTS = [
  "from-violet-500 to-indigo-500",
  "from-blue-500 to-cyan-500",
  "from-emerald-500 to-teal-500",
  "from-amber-500 to-orange-500",
  "from-rose-500 to-pink-500",
  "from-fuchsia-500 to-purple-500",
];

function accentFor(index: number) {
  return CARD_ACCENTS[index % CARD_ACCENTS.length];
}

function resetForm() {
  form.code = "";
  form.name = "";
  form.description = "";
}

async function load() {
  const r = await listRtmfProjects();
  rows.value = r.data;
  // Fetch members for all projects in parallel
  const results = await Promise.allSettled(
    r.data.map((p) =>
      listRtmfProjectMembers(p.id).then((res) => ({ id: p.id, members: res.data }))
    )
  );
  const map: Record<number, RtmfProjectMember[]> = {};
  for (const result of results) {
    if (result.status === "fulfilled") {
      map[result.value.id] = result.value.members;
    }
  }
  membersByProject.value = map;
}

onMounted(load);

function startAdd() {
  editingId.value = null;
  resetForm();
  adding.value = true;
}

function startEdit(p: RtmfProject) {
  adding.value = false;
  form.code = p.code;
  form.name = p.name;
  form.description = p.description ?? "";
  editingId.value = p.id;
}

function cancelForm() {
  adding.value = false;
  editingId.value = null;
}

async function saveNew() {
  if (!form.code.trim() || !form.name.trim()) return;
  saving.value = true;
  try {
    await createRtmfProject({
      code: form.code.trim(),
      name: form.name.trim(),
      description: form.description.trim() || null,
    });
    toast.success("Project created");
    adding.value = false;
    store.invalidate();
    store.loadProjects();
    await load();
  } catch {
    toast.error("Failed to create project");
  } finally {
    saving.value = false;
  }
}

async function saveEdit() {
  if (!editingId.value || !form.code.trim() || !form.name.trim()) return;
  saving.value = true;
  try {
    await updateRtmfProject(editingId.value, {
      code: form.code.trim(),
      name: form.name.trim(),
      description: form.description.trim() || null,
    });
    toast.success("Project updated");
    editingId.value = null;
    store.invalidate();
    store.loadProjects();
    await load();
  } catch {
    toast.error("Failed to update project");
  } finally {
    saving.value = false;
  }
}

async function remove(p: RtmfProject) {
  const accepted = await confirm({
    title: "Delete project?",
    message: `"${p.name}" will be permanently deleted. Modules, actors, and scenarios linked to this project will have their project cleared.`,
    destructive: true,
  });
  if (!accepted) return;
  try {
    await deleteRtmfProject(p.id);
    toast.success("Project deleted");
    if (store.activeProjectId === p.id) store.activeProjectId = null;
    store.invalidate();
    store.loadProjects();
    await load();
  } catch {
    toast.error("Failed to delete project");
  }
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="page-title">Projects</h1>
          <p class="mt-1 text-sm text-slate-500">Manage Page Catalog projects. Switch the active project from the sidebar.</p>
        </div>
        <button
          v-if="!adding"
          class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800"
          @click="startAdd"
        >
          <Plus class="h-4 w-4" /> Add Project
        </button>
      </div>

      <!-- Card grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

        <!-- Add new card -->
        <article v-if="adding" class="flex flex-col rounded-xl border border-violet-200 bg-white shadow-sm ring-1 ring-violet-300">
          <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-violet-500 to-indigo-500" />
          <div class="flex flex-1 flex-col p-4">
            <p class="mb-3 text-sm font-semibold text-slate-800">New Project</p>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Code <span class="text-red-500">*</span></label>
                <input
                  v-model="form.code"
                  type="text"
                  placeholder="e.g. nas"
                  maxlength="32"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Name <span class="text-red-500">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="e.g. NAS"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                <input
                  v-model="form.description"
                  type="text"
                  placeholder="Optional"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <button
                :disabled="saving"
                class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                @click="saveNew"
              >
                <Check class="h-3.5 w-3.5" /> Save
              </button>
              <button
                class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                @click="cancelForm"
              >
                <X class="h-3.5 w-3.5" /> Cancel
              </button>
            </div>
          </div>
        </article>

        <!-- Project cards -->
        <article
          v-for="(p, index) in rows"
          :key="p.id"
          class="group flex flex-col rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md"
        >
          <!-- Accent bar -->
          <div :class="['h-1.5 w-full rounded-t-xl bg-gradient-to-r', accentFor(index)]" />

          <!-- Edit mode -->
          <div v-if="editingId === p.id" class="flex flex-1 flex-col p-4">
            <p class="mb-3 text-sm font-semibold text-slate-800">Edit Project</p>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Code <span class="text-red-500">*</span></label>
                <input
                  v-model="form.code"
                  type="text"
                  maxlength="32"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Name <span class="text-red-500">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                <input
                  v-model="form.description"
                  type="text"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                />
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <button
                :disabled="saving"
                class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                @click="saveEdit"
              >
                <Check class="h-3.5 w-3.5" /> Save
              </button>
              <button
                class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                @click="cancelForm"
              >
                <X class="h-3.5 w-3.5" /> Cancel
              </button>
            </div>
          </div>

          <!-- Display mode -->
          <div v-else class="flex flex-1 flex-col p-4">
            <!-- Top row: code + actions -->
            <div class="mb-2 flex items-start justify-between gap-2">
              <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-600">{{ p.code }}</span>
              <div class="flex items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                <button
                  class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                  title="Edit"
                  @click="startEdit(p)"
                >
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button
                  class="rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-500"
                  title="Delete"
                  @click="remove(p)"
                >
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <!-- Project name + active badge -->
            <div class="flex items-center gap-2">
              <h2 class="text-base font-semibold text-slate-900 leading-tight">{{ p.name }}</h2>
              <span
                v-if="store.activeProjectId === p.id"
                class="shrink-0 rounded-full bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold text-violet-700"
              >active</span>
            </div>

            <!-- Description -->
            <p class="mt-1 line-clamp-2 text-xs text-slate-500 leading-relaxed min-h-[2rem]">
              {{ p.description || '—' }}
            </p>

            <!-- Members row -->
            <div class="mt-auto pt-4">
              <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center">
                  <!-- Avatar stack -->
                  <template v-if="membersByProject[p.id]?.length">
                    <div
                      v-for="(m, mi) in membersByProject[p.id].slice(0, 5)"
                      :key="m.id"
                      :class="['group/tip relative', mi > 0 ? '-ml-2' : '']"
                    >
                      <img
                        v-if="m.photoUrl"
                        :src="m.photoUrl"
                        class="h-7 w-7 rounded-full object-cover ring-2 ring-white"
                      />
                      <div
                        v-else
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-600 ring-2 ring-white"
                      >
                        {{ m.name.charAt(0).toUpperCase() }}
                      </div>
                      <!-- Tooltip -->
                      <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-800 px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-md transition-opacity group-hover/tip:opacity-100">
                        {{ m.name }}
                        <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-800" />
                      </div>
                    </div>
                    <span
                      v-if="membersByProject[p.id].length > 5"
                      class="-ml-2 flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-500 ring-2 ring-white"
                    >+{{ membersByProject[p.id].length - 5 }}</span>
                  </template>
                  <span v-else class="text-xs text-slate-400">No members</span>
                </div>
                <span v-if="membersByProject[p.id]?.length" class="text-xs text-slate-400">
                  {{ membersByProject[p.id].length }} member{{ membersByProject[p.id].length === 1 ? '' : 's' }}
                </span>
              </div>

              <!-- Footer action -->
              <RouterLink
                :to="`/admin/rtmf/projects/${p.id}/members`"
                class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700"
              >
                <Users class="h-3.5 w-3.5" />
                Manage Members
                <ChevronRight class="h-3 w-3" />
              </RouterLink>
            </div>
          </div>
        </article>

        <!-- Empty state placeholder card -->
        <div
          v-if="rows.length === 0 && !adding"
          class="col-span-full flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 py-16 text-center"
        >
          <FolderKanban class="mb-3 h-10 w-10 text-slate-300" />
          <p class="text-sm font-medium text-slate-500">No projects yet</p>
          <p class="mt-1 text-xs text-slate-400">Click "Add Project" to create your first one.</p>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>
