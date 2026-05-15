<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { ScrollText, Pencil, Save, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import MarkdownEditor from "@/components/MarkdownEditor.vue";
import { markdownToSafeHtml } from "@/utils/markdown";
import { getChangelog, updateChangelog } from "@/api/cms";
import { useToast } from "@/composables/useToast";

const toast = useToast();

const content         = ref("");
const originalContent = ref("");
const loading         = ref(true);
const saving          = ref(false);
const mode            = ref<"view" | "edit">("view");

const hasChanges = computed(() => content.value !== originalContent.value);

type Release = { version: string; body: string; html: string };

const releases = computed<Release[]>(() => {
  const parts = content.value.split(/^(?=## )/m);
  return parts
    .map(p => p.trim())
    .filter(p => p.startsWith("## "))
    .map(p => {
      const nl      = p.indexOf("\n");
      const version = nl > 0 ? p.slice(3, nl).trim() : p.slice(3).trim();
      const body    = nl > 0 ? p.slice(nl + 1).trim() : "";
      return { version, body, html: markdownToSafeHtml(body) };
    });
});

onMounted(async () => {
  try {
    const res = await getChangelog();
    content.value = originalContent.value = res.data.content;
  } catch {
    toast.error("Failed to load changelog");
  } finally {
    loading.value = false;
  }
});

async function save() {
  saving.value = true;
  try {
    await updateChangelog(content.value);
    originalContent.value = content.value;
    mode.value = "view";
    toast.success("Changelog saved");
  } catch {
    toast.error("Failed to save changelog");
  } finally {
    saving.value = false;
  }
}

function cancelEdit() {
  content.value = originalContent.value;
  mode.value = "view";
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="page-title">Changelog</h1>
          <p class="mt-1 text-sm text-slate-500">Release history and change notes for OrbitRTMF.</p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
          <template v-if="mode === 'view'">
            <button
              @click="mode = 'edit'"
              class="flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
            >
              <Pencil class="h-4 w-4" />
              Edit
            </button>
          </template>
          <template v-else>
            <button
              @click="cancelEdit"
              class="flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50"
            >
              <X class="h-4 w-4" />
              Cancel
            </button>
            <button
              :disabled="saving || !hasChanges"
              @click="save"
              class="flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-violet-700 disabled:opacity-50"
            >
              <Save class="h-4 w-4" />
              {{ saving ? 'Saving…' : 'Save' }}
            </button>
          </template>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="py-16 text-center text-sm text-slate-400">Loading…</div>

      <!-- Edit mode -->
      <article v-else-if="mode === 'edit'" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <ScrollText class="h-4 w-4 text-violet-600" />
          <h2 class="text-sm font-semibold text-slate-900">Edit Changelog</h2>
          <span class="ml-auto text-xs text-slate-400">Markdown supported</span>
        </div>
        <div class="p-4">
          <MarkdownEditor v-model="content" :rows="30" placeholder="# Changelog&#10;&#10;## [YYYY-MM-DD] — Release title&#10;&#10;### Added&#10;- ..." />
        </div>
      </article>

      <!-- View mode -->
      <template v-else-if="releases.length > 0">
        <article
          v-for="release in releases"
          :key="release.version"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <ScrollText class="h-3.5 w-3.5 text-violet-600" />
            <h2 class="text-xs font-semibold text-slate-900">{{ release.version }}</h2>
          </div>
          <div
            class="prose prose-sm max-w-none p-4 text-xs text-slate-700 [&_h1]:text-sm [&_h2]:text-xs [&_h3]:text-xs [&_h4]:text-xs"
            v-html="release.html"
          />
        </article>
      </template>

      <div v-else class="py-16 text-center text-sm text-slate-400">No changelog entries yet.</div>

    </div>
  </AdminLayout>
</template>
