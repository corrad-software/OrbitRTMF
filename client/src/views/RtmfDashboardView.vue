<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import {
  AppWindow, Users, Layers, TableProperties, ClipboardList,
  ArrowRight, CheckCircle2, AlertCircle, XCircle, MinusCircle, CheckCheck,
} from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import { fetchRtmfDashboard } from "@/api/rtmf";
import type { RtmfDashboardSummary } from "@/types";

const router = useRouter();

const summary = ref<RtmfDashboardSummary | null>(null);

onMounted(async () => {
  const res = await fetchRtmfDashboard();
  summary.value = res.data;
});

const totalItemsForBar = computed(() => {
  if (!summary.value) return 1;
  const s = summary.value.itemsByStatus;
  return (s.implemented + s.partial + s.missing + s.unset) || 1;
});

function pct(n: number) {
  return ((n / totalItemsForBar.value) * 100).toFixed(1);
}

const donePct = computed(() => {
  if (!summary.value) return 0;
  const { frontends, done } = summary.value.totals;
  return frontends ? Math.round((done / frontends) * 100) : 0;
});

function donePctMod(done: number, total: number) {
  if (!total) return 0;
  return Math.round((done / total) * 100);
}

function implPct(implemented: number, total: number) {
  if (!total) return "—";
  return ((implemented / total) * 100).toFixed(0) + "%";
}

function implColor(implemented: number, total: number) {
  if (!total) return "text-slate-400";
  const p = (implemented / total) * 100;
  if (p >= 80) return "text-emerald-600 font-semibold";
  if (p >= 40) return "text-amber-600 font-semibold";
  return "text-rose-600 font-semibold";
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="page-title">Page Catalog Dashboard</h1>
          <p class="mt-0.5 text-sm text-slate-500">Page catalog overview &amp; implementation status</p>
        </div>
        <button
          class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50"
          @click="router.push('/admin/rtmf/frontends')"
        >
          <AppWindow class="h-3.5 w-3.5" />
          View Catalog
        </button>
      </div>

      <!-- Stat Cards -->
      <div v-if="summary" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <div class="rounded-lg border border-violet-200 bg-violet-50 p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-violet-100">
            <AppWindow class="h-3.5 w-3.5 text-violet-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.frontends }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Pages</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-100">
            <CheckCheck class="h-3.5 w-3.5 text-emerald-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.done }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Completed <span class="text-emerald-600 font-medium">({{ donePct }}%)</span></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100">
            <Layers class="h-3.5 w-3.5 text-blue-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.modules }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Modules</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-100">
            <Users class="h-3.5 w-3.5 text-amber-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.actors }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Actors</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100">
            <TableProperties class="h-3.5 w-3.5 text-slate-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.items }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Form Items</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-teal-100">
            <ClipboardList class="h-3.5 w-3.5 text-teal-600" />
          </div>
          <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.totals.scenarios }}</p>
          <p class="mt-0.5 text-xs text-slate-500">Scenario Groups</p>
        </div>
      </div>
      <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <div v-for="i in 6" :key="i" class="h-20 animate-pulse rounded-lg bg-slate-100" />
      </div>

      <!-- Completion banner -->
      <div v-if="summary" class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <div class="mb-2 flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-700">Overall Completion</span>
          <span class="text-xs font-semibold" :class="donePct >= 80 ? 'text-emerald-600' : donePct >= 40 ? 'text-amber-600' : 'text-slate-500'">
            {{ summary.totals.done }} / {{ summary.totals.frontends }} pages done ({{ donePct }}%)
          </span>
        </div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
          <div
            class="h-full rounded-full transition-all"
            :class="donePct >= 80 ? 'bg-emerald-500' : donePct >= 40 ? 'bg-amber-400' : 'bg-violet-400'"
            :style="{ width: donePct + '%' }"
          />
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">

        <!-- Form Items Status -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <TableProperties class="h-4 w-4 text-violet-600" />
            <h2 class="text-sm font-semibold text-slate-900">Form Items — Implementation Status</h2>
          </div>
          <div v-if="summary" class="space-y-3 p-4">
            <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
              <div class="h-full bg-emerald-500 transition-all" :style="{ width: pct(summary.itemsByStatus.implemented) + '%' }" />
              <div class="h-full bg-amber-400 transition-all" :style="{ width: pct(summary.itemsByStatus.partial) + '%' }" />
              <div class="h-full bg-rose-400 transition-all" :style="{ width: pct(summary.itemsByStatus.missing) + '%' }" />
            </div>
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" /><span class="text-slate-600">Implemented</span></div>
                <span class="font-semibold text-slate-800">{{ summary.itemsByStatus.implemented }} <span class="font-normal text-slate-400">({{ pct(summary.itemsByStatus.implemented) }}%)</span></span>
              </div>
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><AlertCircle class="h-3.5 w-3.5 text-amber-500" /><span class="text-slate-600">Partial</span></div>
                <span class="font-semibold text-slate-800">{{ summary.itemsByStatus.partial }} <span class="font-normal text-slate-400">({{ pct(summary.itemsByStatus.partial) }}%)</span></span>
              </div>
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><XCircle class="h-3.5 w-3.5 text-rose-500" /><span class="text-slate-600">Missing</span></div>
                <span class="font-semibold text-slate-800">{{ summary.itemsByStatus.missing }} <span class="font-normal text-slate-400">({{ pct(summary.itemsByStatus.missing) }}%)</span></span>
              </div>
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><MinusCircle class="h-3.5 w-3.5 text-slate-400" /><span class="text-slate-600">Unset</span></div>
                <span class="font-semibold text-slate-800">{{ summary.itemsByStatus.unset }} <span class="font-normal text-slate-400">({{ pct(summary.itemsByStatus.unset) }}%)</span></span>
              </div>
            </div>
          </div>
          <div v-else class="space-y-2 p-4">
            <div v-for="i in 4" :key="i" class="h-4 animate-pulse rounded bg-slate-100" />
          </div>
        </article>

        <!-- Actor Coverage -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
            <div class="flex items-center gap-2">
              <Users class="h-4 w-4 text-amber-600" />
              <h2 class="text-sm font-semibold text-slate-900">Pages by Actor</h2>
            </div>
            <button class="flex items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-slate-900" @click="router.push('/admin/rtmf/actors')">
              Manage <ArrowRight class="h-3 w-3" />
            </button>
          </div>
          <div v-if="summary" class="divide-y divide-slate-50">
            <div v-for="actor in summary.byActor" :key="actor.id" class="flex items-center justify-between px-4 py-2.5">
              <span class="text-sm text-slate-700">{{ actor.name }}</span>
              <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ actor.frontendsCount }}</span>
            </div>
            <div v-if="summary.byActor.length === 0" class="px-4 py-6 text-center text-sm text-slate-400">No actors defined.</div>
          </div>
          <div v-else class="space-y-2 p-4">
            <div v-for="i in 4" :key="i" class="h-7 animate-pulse rounded bg-slate-100" />
          </div>
        </article>

      </div>

      <!-- Module Breakdown Table -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
          <div class="flex items-center gap-2">
            <Layers class="h-4 w-4 text-blue-600" />
            <h2 class="text-sm font-semibold text-slate-900">Progress by Module</h2>
          </div>
          <button class="flex items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-slate-900" @click="router.push('/admin/rtmf/modules')">
            Manage <ArrowRight class="h-3 w-3" />
          </button>
        </div>
        <div v-if="summary" class="overflow-x-auto">
          <table class="w-full min-w-[600px] text-sm">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50 text-xs font-medium text-slate-500">
                <th class="px-4 py-2 text-left">Module</th>
                <th class="px-4 py-2 text-right">Pages</th>
                <th class="px-4 py-2 text-center">Done</th>
                <th class="px-4 py-2 text-right">Form Items</th>
                <th class="px-4 py-2 text-right">Implemented</th>
                <th class="px-4 py-2 text-right">Progress</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr
                v-for="mod in summary.byModule" :key="mod.id"
                class="cursor-pointer transition-colors hover:bg-slate-50"
                @click="router.push(`/admin/rtmf/frontends?module_id=${mod.id}`)"
              >
                <td class="px-4 py-2.5">
                  <span class="mr-1.5 font-mono text-xs text-slate-400">{{ mod.code }}</span>
                  <span class="text-slate-700">{{ mod.name }}</span>
                </td>
                <td class="px-4 py-2.5 text-right text-slate-600">{{ mod.frontendsCount }}</td>
                <td class="px-4 py-2.5 text-center">
                  <div class="inline-flex items-center gap-1.5">
                    <span class="text-xs font-medium" :class="mod.doneCount === mod.frontendsCount && mod.frontendsCount > 0 ? 'text-emerald-600' : 'text-slate-600'">
                      {{ mod.doneCount }}/{{ mod.frontendsCount }}
                    </span>
                    <div class="h-1.5 w-12 overflow-hidden rounded-full bg-slate-100">
                      <div
                        class="h-full rounded-full transition-all"
                        :class="donePctMod(mod.doneCount, mod.frontendsCount) >= 80 ? 'bg-emerald-500' : donePctMod(mod.doneCount, mod.frontendsCount) >= 40 ? 'bg-amber-400' : 'bg-slate-300'"
                        :style="{ width: donePctMod(mod.doneCount, mod.frontendsCount) + '%' }"
                      />
                    </div>
                  </div>
                </td>
                <td class="px-4 py-2.5 text-right text-slate-600">{{ mod.itemsCount }}</td>
                <td class="px-4 py-2.5 text-right text-slate-600">{{ mod.implementedCount }}</td>
                <td class="px-4 py-2.5 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                      <div
                        class="h-full rounded-full transition-all"
                        :class="mod.itemsCount && (mod.implementedCount / mod.itemsCount) >= 0.8 ? 'bg-emerald-500' : mod.itemsCount && (mod.implementedCount / mod.itemsCount) >= 0.4 ? 'bg-amber-400' : 'bg-rose-400'"
                        :style="{ width: mod.itemsCount ? ((mod.implementedCount / mod.itemsCount) * 100) + '%' : '0%' }"
                      />
                    </div>
                    <span class="w-9 text-right text-xs" :class="implColor(mod.implementedCount, mod.itemsCount)">
                      {{ implPct(mod.implementedCount, mod.itemsCount) }}
                    </span>
                  </div>
                </td>
              </tr>
              <tr v-if="summary.byModule.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-400">No modules defined.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="space-y-2 p-4">
          <div v-for="i in 5" :key="i" class="h-8 animate-pulse rounded bg-slate-100" />
        </div>
      </article>

    </div>
  </AdminLayout>
</template>
