<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <!-- Header -->
      <div class="flex items-center gap-3">
        <BotMessageSquare class="h-6 w-6 text-[var(--accent-600)]" />
        <div>
          <h1 class="text-xl font-semibold text-slate-800">AI Settings</h1>
          <p class="text-sm text-slate-500">Configure AIRA, the AI assistant for your Page Catalog</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-1">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="
              activeTab === tab.id
                ? 'border-[var(--accent-600)] text-[var(--accent-600)]'
                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
            "
            @click="activeTab = tab.id"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- ── Tab: Key ─────────────────────────────────────────────────── -->
      <div v-if="activeTab === 'key'" class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-slate-700">API Configuration</h2>

          <div class="space-y-4">
            <!-- Chat toggle -->
            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
              <div>
                <p class="text-sm font-medium text-slate-700">Enable AI Chat</p>
                <p class="text-xs text-slate-500">Allow users to use the AI chat in the top navbar</p>
              </div>
              <button
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                :class="keyForm.chatEnabled ? 'bg-[var(--accent-600)]' : 'bg-slate-300'"
                role="switch"
                :aria-checked="keyForm.chatEnabled"
                @click="keyForm.chatEnabled = !keyForm.chatEnabled"
              >
                <span
                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200"
                  :class="keyForm.chatEnabled ? 'translate-x-5' : 'translate-x-0'"
                />
              </button>
            </div>

            <!-- API Key -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Anthropic API Key
              </label>
              <div class="flex gap-2">
                <input
                  v-model="keyForm.aiApiKey"
                  :type="showKey ? 'text' : 'password'"
                  placeholder="sk-ant-..."
                  class="flex-1 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-[var(--accent-400)] focus:ring-1 focus:ring-[var(--accent-400)]"
                />
                <button
                  class="rounded-md border border-slate-200 px-3 py-2 text-slate-500 transition hover:bg-slate-50"
                  @click="showKey = !showKey"
                >
                  <Eye v-if="!showKey" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                </button>
              </div>
              <p class="mt-1 text-xs text-slate-400">
                Get your key from
                <a href="https://console.anthropic.com" target="_blank" class="text-[var(--accent-600)] underline">console.anthropic.com</a>
              </p>
            </div>

            <!-- Model info (read-only) -->
            <div class="rounded-lg bg-slate-50 p-4">
              <p class="text-xs font-medium text-slate-600 uppercase tracking-wide">Active Model</p>
              <p class="mt-1 text-sm font-semibold text-slate-800">Claude Haiku 4.5</p>
              <p class="text-xs text-slate-500">
                Input: $0.80 / 1M tokens &nbsp;·&nbsp; Output: $4.00 / 1M tokens &nbsp;·&nbsp; Max response: 300 tokens
              </p>
            </div>

            <div class="flex justify-end">
              <button
                class="flex items-center gap-2 rounded-md bg-[var(--accent-600)] px-4 py-2 text-sm font-medium text-white transition hover:bg-[var(--accent-700)] disabled:opacity-50"
                :disabled="savingKey"
                @click="saveKey"
              >
                <Loader2 v-if="savingKey" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Save Settings
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Tab: Transaction ──────────────────────────────────────────── -->
      <div v-if="activeTab === 'transaction'" class="space-y-4">
        <!-- Summary cards -->
        <div class="grid grid-cols-3 gap-4">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Messages</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ txSummary.totalMessages.toLocaleString() }}</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Tokens</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ txSummary.totalTokens.toLocaleString() }}</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Cost (USD)</p>
            <p class="mt-1 text-2xl font-bold text-[var(--accent-600)]">${{ txSummary.totalCostUsd.toFixed(4) }}</p>
          </div>
        </div>

        <!-- Daily table -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-700">Daily Breakdown</h2>
          </div>
          <div v-if="txLoading" class="flex items-center justify-center py-12">
            <Loader2 class="h-5 w-5 animate-spin text-slate-400" />
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3 text-right">Messages</th>
                <th class="px-4 py-3 text-right">Input Tokens</th>
                <th class="px-4 py-3 text-right">Output Tokens</th>
                <th class="px-4 py-3 text-right">Cost (USD)</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="tx in transactions"
                :key="tx.date"
                class="border-b border-slate-50 last:border-0 hover:bg-slate-50"
              >
                <td class="px-4 py-3 font-medium text-slate-800">{{ tx.date }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ tx.messages }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ tx.inputTokens.toLocaleString() }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ tx.outputTokens.toLocaleString() }}</td>
                <td class="px-4 py-3 text-right font-medium text-slate-800">${{ Number(tx.costUsd).toFixed(6) }}</td>
              </tr>
              <tr v-if="transactions.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No transactions yet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Tab: Log ──────────────────────────────────────────────────── -->
      <div v-if="activeTab === 'log'" class="space-y-3">
        <div class="flex items-center gap-3">
          <input
            v-model="logSearch"
            type="text"
            placeholder="Search questions or responses..."
            class="flex-1 rounded-md border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[var(--accent-400)] focus:ring-1 focus:ring-[var(--accent-400)]"
            @input="debouncedLoadLogs"
          />
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div v-if="logLoading" class="flex items-center justify-center py-10">
            <Loader2 class="h-5 w-5 animate-spin text-slate-400" />
          </div>

          <table v-else class="w-full text-xs">
            <thead>
              <tr class="border-b border-slate-100 text-left font-medium text-slate-500 uppercase tracking-wide">
                <th class="px-3 py-2.5 w-28">Time</th>
                <th class="px-3 py-2.5 w-24">User</th>
                <th class="px-3 py-2.5">Question</th>
                <th class="px-3 py-2.5">Response</th>
                <th class="px-3 py-2.5 w-16 text-right">Tokens</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="log in logs"
                :key="log.id"
                class="group border-b border-slate-50 last:border-0 hover:bg-slate-50 cursor-pointer"
                @click="expandedLog = expandedLog === log.id ? null : log.id"
              >
                <template v-if="expandedLog !== log.id">
                  <td class="px-3 py-2 text-slate-400 whitespace-nowrap">{{ formatDate(log.createdAt) }}</td>
                  <td class="px-3 py-2">
                    <span class="rounded-full bg-[var(--accent-50)] px-2 py-0.5 font-medium text-[var(--accent-700)]">
                      {{ log.user?.name ?? 'Unknown' }}
                    </span>
                  </td>
                  <td class="px-3 py-2 text-slate-700 max-w-0">
                    <p class="truncate">{{ log.question }}</p>
                  </td>
                  <td class="px-3 py-2 text-slate-500 max-w-0">
                    <p class="truncate">{{ log.response }}</p>
                  </td>
                  <td class="px-3 py-2 text-right text-slate-400">{{ (log.inputTokens + log.outputTokens).toLocaleString() }}</td>
                </template>

                <!-- Expanded row -->
                <td v-else colspan="5" class="px-3 py-3 bg-slate-50">
                  <div class="space-y-2">
                    <div class="flex items-center gap-2 text-slate-400">
                      <span class="font-medium text-[var(--accent-700)] bg-[var(--accent-50)] rounded-full px-2 py-0.5">{{ log.user?.name ?? 'Unknown' }}</span>
                      <span>{{ formatDate(log.createdAt) }}</span>
                      <span>· {{ (log.inputTokens + log.outputTokens).toLocaleString() }} tokens</span>
                    </div>
                    <div class="rounded border border-slate-200 bg-white p-2.5">
                      <p class="font-medium text-slate-500 mb-0.5">Q</p>
                      <p class="text-slate-800 whitespace-pre-wrap">{{ log.question }}</p>
                    </div>
                    <div class="rounded border border-slate-200 bg-white p-2.5">
                      <p class="font-medium text-slate-500 mb-0.5">A</p>
                      <p class="text-slate-600 whitespace-pre-wrap">{{ log.response }}</p>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="logs.length === 0">
                <td colspan="5" class="px-3 py-8 text-center text-slate-400">No chat logs yet</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="flex items-center justify-between border-t border-slate-100 px-3 py-2.5">
            <span class="text-xs text-slate-400">
              {{ logMeta.total === 0 ? 'No results' : `${((logPage - 1) * 15) + 1}–${Math.min(logPage * 15, logMeta.total)} of ${logMeta.total}` }}
            </span>
            <div class="flex items-center gap-1">
              <button
                class="rounded px-2.5 py-1 text-xs text-slate-600 transition hover:bg-slate-100 disabled:opacity-30"
                :disabled="logPage <= 1"
                @click="logPage--; loadLogs()"
              >
                ‹ Prev
              </button>
              <span class="px-2 text-xs text-slate-500">{{ logPage }} / {{ logMeta.totalPages }}</span>
              <button
                class="rounded px-2.5 py-1 text-xs text-slate-600 transition hover:bg-slate-100 disabled:opacity-30"
                :disabled="logPage >= logMeta.totalPages"
                @click="logPage++; loadLogs()"
              >
                Next ›
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Tab: Balance ──────────────────────────────────────────────── -->
      <div v-if="activeTab === 'balance'" class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-slate-700">API Credit Balance</h2>

          <div v-if="balanceLoading" class="flex items-center gap-2 text-sm text-slate-500">
            <Loader2 class="h-4 w-4 animate-spin" />
            Fetching balance...
          </div>

          <div v-else-if="balance.available && balance.data" class="space-y-3">
            <pre class="rounded-lg bg-slate-50 p-4 text-xs text-slate-700 overflow-auto">{{ JSON.stringify(balance.data, null, 2) }}</pre>
          </div>

          <div v-else class="rounded-lg bg-amber-50 border border-amber-200 p-4">
            <p class="text-sm font-medium text-amber-800">Balance unavailable via API</p>
            <p class="mt-1 text-xs text-amber-700">
              {{ balance.message ?? 'The Anthropic Credits API requires an Admin API key.' }}
            </p>
            <a
              href="https://console.anthropic.com/settings/billing"
              target="_blank"
              class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-800 transition hover:bg-amber-200"
            >
              <ExternalLink class="h-3.5 w-3.5" />
              View balance on Anthropic Console
            </a>
          </div>

          <div class="mt-6 rounded-lg border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Estimated spend from this system</p>
            <p class="text-2xl font-bold text-slate-800">${{ txSummary.totalCostUsd.toFixed(6) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Based on {{ txSummary.totalMessages }} messages logged in this system</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { BotMessageSquare, Key, ReceiptText, ScrollText, Wallet, Eye, EyeOff, Save, Loader2, ExternalLink } from "lucide-vue-next";
import { getAiSettings, saveAiSettings, getAiChatLogs, getAiTransactions, getAiBalance } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AiChatLog, AiTransaction } from "@/types";

const toast = useToast();

const tabs = [
  { id: "key",         label: "Key",         icon: Key },
  { id: "transaction", label: "Transaction",  icon: ReceiptText },
  { id: "log",         label: "Log",          icon: ScrollText },
  { id: "balance",     label: "Balance",      icon: Wallet },
];

const activeTab = ref<"key" | "transaction" | "log" | "balance">("key");

// ── Key tab ──────────────────────────────────────────────────────────────────
const showKey    = ref(false);
const savingKey  = ref(false);
const keyForm    = ref({ aiApiKey: "", chatEnabled: true });

async function loadSettings() {
  const res = await getAiSettings().catch(() => null);
  if (res?.data) {
    keyForm.value.aiApiKey   = res.data.aiApiKey ?? "";
    keyForm.value.chatEnabled = res.data.chatEnabled ?? true;
  }
}

async function saveKey() {
  savingKey.value = true;
  try {
    await saveAiSettings({ aiApiKey: keyForm.value.aiApiKey, chatEnabled: keyForm.value.chatEnabled });
    toast.success("AI settings saved");
  } catch {
    toast.error("Failed to save settings");
  } finally {
    savingKey.value = false;
  }
}

// ── Transaction tab ───────────────────────────────────────────────────────────
const txLoading   = ref(false);
const transactions = ref<AiTransaction[]>([]);
const txSummary   = ref({ totalMessages: 0, totalTokens: 0, totalCostUsd: 0 });

async function loadTransactions() {
  txLoading.value = true;
  try {
    const res = await getAiTransactions("?limit=60");
    transactions.value = (res?.data as AiTransaction[]) ?? [];
    const meta = res?.meta as Record<string, unknown> | undefined;
    const summary = meta?.summary as Record<string, number> | undefined;
    if (summary) {
      txSummary.value = {
        totalMessages: summary.total_messages ?? 0,
        totalTokens:   summary.total_tokens ?? 0,
        totalCostUsd:  Number(summary.total_cost_usd ?? 0),
      };
    }
  } finally {
    txLoading.value = false;
  }
}

// ── Log tab ───────────────────────────────────────────────────────────────────
const logLoading  = ref(false);
const logs        = ref<AiChatLog[]>([]);
const logSearch   = ref("");
const logPage     = ref(1);
const logMeta     = ref({ total: 0, totalPages: 1 });
const expandedLog = ref<number | null>(null);

let logDebounceTimer: ReturnType<typeof setTimeout> | undefined;
function debouncedLoadLogs() {
  clearTimeout(logDebounceTimer);
  logPage.value = 1;
  logDebounceTimer = setTimeout(loadLogs, 400);
}

async function loadLogs() {
  logLoading.value = true;
  try {
    const q = logSearch.value ? `&q=${encodeURIComponent(logSearch.value)}` : "";
    const res = await getAiChatLogs(`?page=${logPage.value}&limit=15${q}`);
    logs.value = (res?.data as AiChatLog[]) ?? [];
    const meta = res?.meta as Record<string, unknown> | undefined;
    logMeta.value = {
      total:      Number(meta?.total ?? 0),
      totalPages: Number(meta?.totalPages ?? 1),
    };
  } finally {
    logLoading.value = false;
  }
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
}

// ── Balance tab ────────────────────────────────────────────────────────────────
const balanceLoading = ref(false);
const balance = ref<{ available: boolean; message?: string; data?: Record<string, unknown> }>({
  available: false,
});

async function loadBalance() {
  balanceLoading.value = true;
  try {
    const res = await getAiBalance();
    balance.value = (res?.data as typeof balance.value) ?? { available: false };
  } finally {
    balanceLoading.value = false;
  }
}

// ── Tab switching lazy-load ────────────────────────────────────────────────────
watch(activeTab, (tab) => {
  if (tab === "transaction") loadTransactions();
  if (tab === "log")         loadLogs();
  if (tab === "balance") {
    loadBalance();
    if (txSummary.value.totalMessages === 0) loadTransactions();
  }
});

onMounted(() => {
  loadSettings();
});
</script>
