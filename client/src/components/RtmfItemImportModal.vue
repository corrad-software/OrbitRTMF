<script setup lang="ts">
import { ref, computed } from "vue";
import * as XLSX from "xlsx";
import { FileSpreadsheet, Upload, Download, X, Check, AlertTriangle, Loader2 } from "lucide-vue-next";
import { createRtmfFrontendItem } from "@/api/rtmf";
import type { RtmfFrontendItem } from "@/types";

const props = defineProps<{ frontendId: number; existingCount: number }>();
const emit = defineEmits<{ close: []; imported: [items: RtmfFrontendItem[]] }>();

// ── Known types ──────────────────────────────────────────────────────────────
const KNOWN_TYPES = [
  "Text", "Label", "Header", "Divider",
  "Input", "Textarea", "Select", "Checkbox", "Radio", "DatePicker", "FileUpload",
  "Button", "Link", "Icon", "Tab", "Component", "Form",
  "Badge", "Image", "Table", "List", "Card", "Chart",
  "Modal", "Alert",
  "Email", "SMS", "Mobile Apps", "Web",
  "Integrasi",
];
const KNOWN_TYPES_LOWER = new Set(KNOWN_TYPES.map(t => t.toLowerCase()));

// ── Template download ─────────────────────────────────────────────────────────
function downloadTemplate() {
  const wb = XLSX.utils.book_new();

  // Data sheet
  const headers = ["Type", "Label", "Field Name", "Condition", "Validation", "Mandatory"];
  const sample = [
    ["Input", "Full Name", "nama_penuh", "", "Max 255", "Yes"],
    ["Select", "Status", "status", "status ≠ DRAFT", "Required", "No"],
    ["Button", "Submit", "", "", "", "No"],
  ];
  const ws = XLSX.utils.aoa_to_sheet([headers, ...sample]);

  // Column widths
  ws["!cols"] = [
    { wch: 14 }, { wch: 24 }, { wch: 20 }, { wch: 10 },
    { wch: 24 }, { wch: 20 },
  ];

  XLSX.utils.book_append_sheet(wb, ws, "Form Items");

  // Type Reference sheet
  const typeRows: string[][] = [["Category", "Type"]];
  const categories: [string, string[]][] = [
    ["Basic",        ["Text", "Label", "Header", "Divider"]],
    ["Input",        ["Input", "Textarea", "Select", "Checkbox", "Radio", "DatePicker", "FileUpload"]],
    ["Action",       ["Button", "Link", "Icon", "Tab", "Component", "Form"]],
    ["Display",      ["Badge", "Image", "Table", "List", "Card", "Chart"]],
    ["Overlay",      ["Modal", "Alert"]],
    ["Notification", ["Email", "SMS", "Mobile Apps", "Web"]],
    ["Other",        ["Integrasi"]],
  ];
  for (const [cat, types] of categories) {
    for (const t of types) typeRows.push([cat, t]);
  }
  const wsRef = XLSX.utils.aoa_to_sheet(typeRows);
  wsRef["!cols"] = [{ wch: 14 }, { wch: 16 }];
  XLSX.utils.book_append_sheet(wb, wsRef, "Type Reference");

  XLSX.writeFile(wb, "form-items-template.xlsx");
}

// ── Parsed row type ───────────────────────────────────────────────────────────
type ParsedRow = {
  include: boolean;
  type: string;
  label: string;
  tableFieldname: string;
  mandatory: boolean;
  condition: string;
  validation: string;
  typeValid: boolean;
  hasWarning: boolean;
};

const rows = ref<ParsedRow[]>([]);
const fileName = ref("");
const isDragging = ref(false);
const importing = ref(false);
const importProgress = ref(0);
const importErrors = ref<string[]>([]);
const importDone = ref(false);

const validRows = computed(() => rows.value.filter(r => r.include && r.typeValid));
const hasErrors = computed(() => rows.value.some(r => !r.typeValid));

function parseFile(file: File) {
  fileName.value = file.name;
  importDone.value = false;
  importErrors.value = [];
  const reader = new FileReader();
  reader.onload = (e) => {
    const data = new Uint8Array(e.target!.result as ArrayBuffer);
    const wb = XLSX.read(data, { type: "array" });
    const ws = wb.Sheets[wb.SheetNames[0]];
    const raw: string[][] = XLSX.utils.sheet_to_json(ws, { header: 1, defval: "" });

    // Skip header row
    const dataRows = raw.slice(1).filter(r => r.some(cell => String(cell).trim() !== ""));
    rows.value = dataRows.map(r => {
      const type = String(r[0] ?? "").trim();
      const label = String(r[1] ?? "").trim();
      return {
        include: true,
        type,
        label,
        tableFieldname: String(r[2] ?? "").trim(),
        condition: String(r[3] ?? "").trim(),
        validation: String(r[4] ?? "").trim(),
        mandatory: String(r[5] ?? "").trim().toLowerCase() === "yes",
        typeValid: KNOWN_TYPES_LOWER.has(type.toLowerCase()),
        hasWarning: label === "",
      };
    });
  };
  reader.readAsArrayBuffer(file);
}

function onFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) parseFile(file);
}

function onDrop(e: DragEvent) {
  isDragging.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (file) parseFile(file);
}

function toggleAll(val: boolean) {
  rows.value.forEach(r => { r.include = val; });
}

async function runImport() {
  importing.value = true;
  importProgress.value = 0;
  importErrors.value = [];
  const toImport = validRows.value;
  const imported: RtmfFrontendItem[] = [];
  const baseOrder = props.existingCount;

  for (let i = 0; i < toImport.length; i++) {
    const row = toImport[i];
    try {
      const res = await createRtmfFrontendItem(props.frontendId, {
        type: row.type || null,
        label: row.label || null,
        tableFieldname: row.tableFieldname || null,
        mandatory: row.mandatory,
        condition: row.condition || null,
        validation: row.validation || null,
        sortOrder: baseOrder + i,
      });
      imported.push(res.data);
    } catch {
      importErrors.value.push(`Row ${i + 1} (${row.label || row.type}): failed to save`);
    }
    importProgress.value = Math.round(((i + 1) / toImport.value.length) * 100);
  }

  importing.value = false;
  importDone.value = true;
  if (imported.length > 0) emit("imported", imported);
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
    <div class="flex w-full max-w-3xl flex-col rounded-xl border border-slate-200 bg-white shadow-2xl" style="max-height: 90vh">

      <!-- Header -->
      <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
        <FileSpreadsheet class="h-5 w-5 text-violet-600" />
        <div>
          <h2 class="text-sm font-semibold text-slate-900">Import Form Items from Excel</h2>
          <p class="text-xs text-slate-500">Upload an Excel file to bulk-add form items. Review before saving.</p>
        </div>
        <button class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="emit('close')">
          <X class="h-4 w-4" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-5 space-y-4">

        <!-- Step 1: Download template -->
        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
          <div>
            <p class="text-sm font-medium text-slate-700">Step 1 — Download the template</p>
            <p class="text-xs text-slate-500">Fill in your form items using the provided Excel template.</p>
          </div>
          <button
            class="flex items-center gap-2 rounded-lg border border-violet-300 bg-white px-3 py-1.5 text-sm font-medium text-violet-600 shadow-sm transition-colors hover:bg-violet-50"
            @click="downloadTemplate"
          >
            <Download class="h-4 w-4" />
            Download Template
          </button>
        </div>

        <!-- Step 2: Upload -->
        <div>
          <p class="mb-2 text-sm font-medium text-slate-700">Step 2 — Upload your file</p>
          <label
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-8 transition-colors"
            :class="isDragging ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-slate-50 hover:border-violet-300 hover:bg-violet-50/50'"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
          >
            <Upload class="h-8 w-8" :class="isDragging ? 'text-violet-500' : 'text-slate-300'" />
            <span class="text-sm text-slate-500">
              <span class="font-medium text-violet-600">Browse</span> or drag & drop an Excel / CSV file
            </span>
            <span v-if="fileName" class="text-xs font-medium text-slate-700">{{ fileName }}</span>
            <input type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="onFileInput" />
          </label>
        </div>

        <!-- Step 3: Preview -->
        <div v-if="rows.length > 0">
          <div class="mb-2 flex items-center gap-3">
            <p class="text-sm font-medium text-slate-700">Step 3 — Review & import</p>
            <span class="text-xs text-slate-500">{{ validRows.length }} valid · {{ rows.length - validRows.length }} skipped</span>
            <div class="ml-auto flex items-center gap-2">
              <button class="text-xs text-violet-500 hover:underline" @click="toggleAll(true)">Select all</button>
              <span class="text-slate-300">·</span>
              <button class="text-xs text-slate-400 hover:underline" @click="toggleAll(false)">None</button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-xs">
              <thead class="border-b border-slate-100 bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="w-8 px-3 py-2"></th>
                  <th class="px-3 py-2">Type</th>
                  <th class="px-3 py-2">Label</th>
                  <th class="px-3 py-2">Field Name</th>
                  <th class="px-3 py-2">Condition</th>
                  <th class="px-3 py-2">Validation</th>
                  <th class="px-2 py-2 text-center">Mand.</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(row, i) in rows"
                  :key="i"
                  :class="[
                    !row.typeValid ? 'bg-rose-50' : row.hasWarning ? 'bg-amber-50' : '',
                    !row.include ? 'opacity-40' : '',
                  ]"
                >
                  <td class="px-3 py-2">
                    <input
                      type="checkbox"
                      v-model="row.include"
                      :disabled="!row.typeValid"
                      class="rounded border-slate-300 accent-violet-600"
                    />
                  </td>
                  <td class="px-3 py-2">
                    <span class="flex items-center gap-1">
                      <AlertTriangle v-if="!row.typeValid" class="h-3 w-3 flex-shrink-0 text-rose-500" />
                      <span :class="!row.typeValid ? 'text-rose-600 font-medium' : 'text-slate-700'">{{ row.type || '—' }}</span>
                    </span>
                  </td>
                  <td class="px-3 py-2">
                    <span class="flex items-center gap-1">
                      <AlertTriangle v-if="row.hasWarning && row.typeValid" class="h-3 w-3 flex-shrink-0 text-amber-500" />
                      <span class="text-slate-700">{{ row.label || '—' }}</span>
                    </span>
                  </td>
                  <td class="px-3 py-2 text-slate-600">{{ row.tableFieldname || '—' }}</td>
                  <td class="px-3 py-2 text-slate-500">{{ row.condition || '—' }}</td>
                  <td class="px-3 py-2 text-slate-500">{{ row.validation || '—' }}</td>
                  <td class="px-2 py-2 text-center">
                    <Check v-if="row.mandatory" class="mx-auto h-3.5 w-3.5 text-emerald-500" />
                    <span v-else class="text-slate-300">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Validation notes -->
          <div v-if="hasErrors" class="mt-2 flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
            <AlertTriangle class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" />
            <span>Rows highlighted in red have an unrecognised type and will not be imported. Check the <strong>Type Reference</strong> sheet in the template for valid values.</span>
          </div>

          <!-- Progress bar -->
          <div v-if="importing" class="mt-3">
            <div class="mb-1 flex justify-between text-xs text-slate-500">
              <span>Saving…</span><span>{{ importProgress }}%</span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div class="h-full rounded-full bg-violet-500 transition-all" :style="{ width: importProgress + '%' }"></div>
            </div>
          </div>

          <!-- Import errors -->
          <div v-if="importErrors.length" class="mt-2 space-y-1">
            <p v-for="e in importErrors" :key="e" class="text-xs text-rose-600">{{ e }}</p>
          </div>

          <!-- Done -->
          <div v-if="importDone && !importing" class="mt-2 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            <Check class="h-3.5 w-3.5" />
            Import complete. Items added to the page.
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
        <button
          class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
          @click="emit('close')"
        >
          {{ importDone ? 'Close' : 'Cancel' }}
        </button>
        <button
          v-if="rows.length > 0 && !importDone"
          :disabled="importing || validRows.length === 0"
          class="flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-40"
          @click="runImport"
        >
          <Loader2 v-if="importing" class="h-4 w-4 animate-spin" />
          <Upload v-else class="h-4 w-4" />
          Import {{ validRows.length }} item{{ validRows.length !== 1 ? 's' : '' }}
        </button>
      </div>

    </div>
  </div>
</template>
