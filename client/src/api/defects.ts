import { apiRequest } from "./client";

export type DefectKpi = {
  newToday: number;
  resolvedToday: number;
  reopenedToday: number;
  kritikalOpen: number;
  highOpen: number;
  feedbackOpen: number;
  totalActive: number;
  activeYesterday: number;
  deltaVsYesterday: number;
};

export type DefectListItem = {
  id: number;
  ringkasan: string;
  modul: string | null;
  tahap: string;
  priority?: string;
  assigned?: string | null;
  resolver?: string | null;
  user?: string | null;
  tarikh: string;
};

export type DashboardResponse = {
  kpi: DefectKpi;
  newDefects: DefectListItem[];
  resolvedTodayList: DefectListItem[];
  reopenedTodayList: DefectListItem[];
};

export type DefectLogRow = {
  id: number;
  projek: string | null;
  kategori: string | null;
  ringkasan: string;
  severity: string;
  priority: string;
  tahap: string;
  status: string;
  resolution: string;
  by: string | null;
  assigned: string | null;
  tarikh: string;
  kemaskini: string;
  umur: number;
};

export type SummaryResponse = {
  totals: { total: number; open: number; resolved: number; closed: number };
  severity: { label: string; count: number; color: string }[];
  assignees: { name: string; open: number; resolved: number }[];
  modules: { name: string; open: number; resolved: number }[];
};

export type CategoryRow = {
  label: string;
  jumlah: number;
  open: number;
  resolved: number;
  reopened: number;
  kritikalHigh: number;
  pctOpen: number;
};

export type TrendRow = {
  tarikh: string;
  baru: number;
  resolved: number;
  reopened: number;
  baki: number;
  kadarResolve: number;
  kadarReopen: number;
  tren: string;
  catatan: string;
};

export type ApiOk<T> = { data: T; meta?: Record<string, unknown> };

export type CategorySource = "all" | "internal" | "external";

export const fetchDefectDashboard = (source: CategorySource = "all") =>
  apiRequest<ApiOk<DashboardResponse>>(`/api/defects/dashboard?source=${source}`);

export const fetchDefectLog = (params: {
  page?: number; limit?: number; q?: string;
  tahap?: string[]; status?: string[];
  source?: CategorySource;
} = {}) => {
  const qs = new URLSearchParams();
  if (params.page) qs.set("page", String(params.page));
  if (params.limit) qs.set("limit", String(params.limit));
  if (params.q) qs.set("q", params.q);
  if (params.source && params.source !== "all") qs.set("source", params.source);
  params.tahap?.forEach(t => qs.append("tahap[]", t));
  params.status?.forEach(s => qs.append("status[]", s));
  const suffix = qs.toString() ? `?${qs.toString()}` : "";
  return apiRequest<ApiOk<DefectLogRow[]>>(`/api/defects/log${suffix}`);
};

export const fetchDefectSummary = (source: CategorySource = "all") =>
  apiRequest<ApiOk<SummaryResponse>>(`/api/defects/summary?source=${source}`);

export const fetchDefectCategories = (source: CategorySource = "all") =>
  apiRequest<ApiOk<CategoryRow[]>>(`/api/defects/categories?source=${source}`);

export const fetchDefectTrend = (days = 14, source: CategorySource = "all") =>
  apiRequest<ApiOk<TrendRow[]>>(`/api/defects/trend?days=${days}&source=${source}`);
