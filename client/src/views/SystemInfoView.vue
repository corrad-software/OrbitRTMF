<script setup lang="ts">
import {
  Cpu,
  Database,
  Globe,
  Layers,
  Monitor,
  Palette,
  Server,
  Shield,
  Package,
} from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";

type StackItem = { name: string; version: string; description?: string };
type StackGroup = { label: string; icon: unknown; color: { text: string }; items: StackItem[] };

const stack: StackGroup[] = [
  {
    label: "Frontend",
    icon: Monitor,
    color: { text: "text-violet-600" },
    items: [
      { name: "Vue", version: "3.5.21", description: "Progressive JavaScript framework" },
      { name: "TypeScript", version: "5.9.2", description: "Typed superset of JavaScript" },
      { name: "Vite", version: "8.0.1", description: "Frontend build tool and dev server (port 5180)" },
      { name: "Vue Router", version: "4.5.1", description: "Official router for Vue.js" },
      { name: "Pinia", version: "3.0.3", description: "State management for Vue" },
      { name: "xlsx (SheetJS)", version: "0.18.5", description: "Client-side Excel parsing for bulk import" },
    ],
  },
  {
    label: "Styling & UI",
    icon: Palette,
    color: { text: "text-pink-600" },
    items: [
      { name: "Tailwind CSS", version: "3.4.17", description: "Utility-first CSS framework" },
      { name: "PostCSS", version: "8.5.6", description: "CSS post-processor for build pipeline" },
      { name: "Lucide Icons", version: "0.542", description: "Beautiful consistent icon set" },
    ],
  },
  {
    label: "Backend",
    icon: Server,
    color: { text: "text-blue-600" },
    items: [
      { name: "Laravel", version: "13.1.1", description: "PHP web framework and REST API" },
      { name: "PHP", version: "8.5.2", description: "Runtime version (requires ^8.4)" },
      { name: "Eloquent ORM", version: "13.x", description: "Laravel ORM for model/database access" },
      { name: "Sanctum", version: "4.3.1", description: "Session-based SPA authentication" },
      { name: "Laravel Pint", version: "1.29.0", description: "PHP code style formatter" },
      { name: "Laravel Tinker", version: "3.x", description: "REPL for Laravel application" },
    ],
  },
  {
    label: "Database",
    icon: Database,
    color: { text: "text-emerald-600" },
    items: [
      { name: "PostgreSQL", version: "17", description: "Primary application database (orbitrtmf)" },
      { name: "MantisBT MySQL", version: "5.x", description: "External read-only defect tracker database" },
      { name: "Migrations", version: "Laravel 13", description: "Schema versioning with artisan migrate" },
      { name: "Seeders", version: "Laravel 13", description: "Role, user, setting, and category fixtures" },
    ],
  },
  {
    label: "Authentication & Security",
    icon: Shield,
    color: { text: "text-amber-600" },
    items: [
      { name: "Laravel Sanctum", version: "4.3.1", description: "SPA auth guard via session cookie" },
      { name: "CSRF Middleware", version: "Laravel 13", description: "PreventRequestForgery via Sanctum" },
      { name: "Throttle Middleware", version: "Laravel 13", description: "Rate limiting on auth and API routes" },
      { name: "RBAC", version: "Custom", description: "Permission class + CheckPermission middleware with 6 roles" },
      { name: "Password Hashing", version: "bcrypt", description: "Laravel hashed cast, 12 rounds in production" },
    ],
  },
  {
    label: "Infrastructure",
    icon: Layers,
    color: { text: "text-teal-600" },
    items: [
      { name: "Composer", version: "2.x", description: "PHP dependency management" },
      { name: "npm", version: "Scripts", description: "Runs dev/build pipeline for admin client" },
      { name: "Laravel Vite Plugin", version: "3.0", description: "Vite integration for Laravel apps" },
      { name: "Docker", version: "Dockerfile", description: "Containerised deployment via Coolify" },
      { name: "PHPUnit", version: "12.5.14", description: "Feature and unit test suite (orbitrtmf_test DB)" },
    ],
  },
];
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-4">
      <!-- ───── Hero Header ───── -->
      <div class="flex items-center justify-between">
        <h1 class="page-title">System Information</h1>
      </div>

      <!-- ───── Architecture Overview ───── -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <Cpu class="h-4 w-4 text-slate-600" />
          <h2 class="text-sm font-semibold text-slate-900">Architecture</h2>
        </div>
        <div class="p-4">
          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-3">
              <div class="flex items-center gap-2">
                <Globe class="h-4 w-4 text-violet-500" />
                <p class="text-sm font-semibold text-slate-900">Frontend SPA</p>
              </div>
              <p class="mt-1.5 text-xs text-slate-500">Vue 3 + TypeScript single-page application built with Vite, served on port 5180. State managed by Pinia; routing by Vue Router.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
              <div class="flex items-center gap-2">
                <Server class="h-4 w-4 text-blue-500" />
                <p class="text-sm font-semibold text-slate-900">Laravel API</p>
              </div>
              <p class="mt-1.5 text-xs text-slate-500">Laravel 13 JSON REST API with Sanctum session auth, CamelCaseMiddleware, RBAC permission gates, and Eloquent ORM backed by PostgreSQL.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
              <div class="flex items-center gap-2">
                <Package class="h-4 w-4 text-teal-500" />
                <p class="text-sm font-semibold text-slate-900">Hybrid Repository</p>
              </div>
              <p class="mt-1.5 text-xs text-slate-500">Laravel root with a <code class="rounded bg-slate-100 px-1 font-mono text-[11px]">client/</code> Vue admin frontend. API controllers in <code class="rounded bg-slate-100 px-1 font-mono text-[11px]">app/Http/Controllers/Api</code>. Deployed via Docker + Coolify.</p>
            </div>
          </div>
        </div>
      </article>

      <!-- ───── Stack Groups ───── -->
      <div class="grid gap-4 lg:grid-cols-2">
        <article v-for="group in stack" :key="group.label" class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
            <component :is="group.icon" class="h-4 w-4" :class="group.color.text" />
            <h2 class="text-sm font-semibold text-slate-900">{{ group.label }}</h2>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="item in group.items" :key="item.name" class="flex items-center justify-between px-4 py-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-900">{{ item.name }}</p>
                <p v-if="item.description" class="text-xs text-slate-400">{{ item.description }}</p>
              </div>
              <span class="ml-3 shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-mono text-slate-600">{{ item.version }}</span>
            </div>
          </div>
        </article>
      </div>

      <!-- ───── Database Models ───── -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5">
          <Database class="h-4 w-4 text-emerald-600" />
          <h2 class="text-sm font-semibold text-slate-900">Database Models</h2>
        </div>
        <div class="p-4">
          <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="model in [
              { name: 'User', desc: 'Admin users with roles, permissions, and photo' },
              { name: 'Role', desc: 'Role definitions with permission arrays' },
              { name: 'Category', desc: 'Content taxonomy for organizing posts' },
              { name: 'Post', desc: 'Blog posts with draft/published/archived states' },
              { name: 'Page', desc: 'Static pages with publish workflow' },
              { name: 'Media', desc: 'Uploaded files with image metadata' },
              { name: 'Setting', desc: 'Key-value site configuration pairs' },
              { name: 'AuditLog', desc: 'Security and change tracking events' },
              { name: 'RtmfProject', desc: 'RTMF projects with member roles' },
              { name: 'RtmfModule', desc: 'Page catalog modules and sub-modules' },
              { name: 'RtmfFrontend', desc: 'Page specs with items, scenarios, and feedback' },
              { name: 'RtmfScenario', desc: 'Flow scenarios with steps and links' },
            ]" :key="model.name" class="rounded-lg border border-slate-200 px-3 py-2">
              <p class="text-sm font-medium text-slate-900">{{ model.name }}</p>
              <p class="mt-0.5 text-xs text-slate-400">{{ model.desc }}</p>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
