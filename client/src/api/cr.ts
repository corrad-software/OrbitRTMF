import { apiRequest } from "./client";

export type CrLogRow = {
  id: number;
  projek: string | null;
  kategori: string | null;
  ringkasan: string;
  priority: string;
  status: string;
  resolution: string;
  by: string | null;
  assigned: string | null;
  tarikh: string;
  kemaskini: string;
  umur: number;
};

export type CrFiltersResponse = {
  projek: string[];
  kategori: string[];
  priority: string[];
  by: string[];
  assigned: string[];
};

export type CrSummaryResponse = {
  totals: { total: number; open: number; resolved: number };
  assignees: { name: string; open: number }[];
  modules: { name: string; open: number }[];
};

export type CrTrendRow = {
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

export type CrApiOk<T> = { data: T; meta?: Record<string, unknown> };

export const fetchCrFilters = () =>
  apiRequest<CrApiOk<CrFiltersResponse>>("/api/cr/filters");

export const fetchCrLog = (params: {
  page?: number;
  limit?: number;
  q?: string;
  status?: string[];
  priority?: string[];
  projek?: string[];
  kategori?: string[];
  by?: string[];
  assigned?: string[];
  dateFrom?: string;
  dateTo?: string;
} = {}) => {
  const qs = new URLSearchParams();
  if (params.page)   qs.set("page",  String(params.page));
  if (params.limit)  qs.set("limit", String(params.limit));
  if (params.q)      qs.set("q",     params.q);
  if (params.dateFrom) qs.set("date_from", params.dateFrom);
  if (params.dateTo)   qs.set("date_to",   params.dateTo);
  params.status?.forEach(s   => qs.append("status[]",   s));
  params.priority?.forEach(p => qs.append("priority[]", p));
  params.projek?.forEach(v   => qs.append("projek[]",   v));
  params.kategori?.forEach(v => qs.append("kategori[]", v));
  params.by?.forEach(v       => qs.append("by[]",       v));
  params.assigned?.forEach(v => qs.append("assigned[]", v));
  const suffix = qs.toString() ? `?${qs.toString()}` : "";
  return apiRequest<CrApiOk<CrLogRow[]>>(`/api/cr/log${suffix}`);
};

export const fetchCrSummary = () =>
  apiRequest<CrApiOk<CrSummaryResponse>>("/api/cr/summary");

export const fetchCrTrend = (days = 14) =>
  apiRequest<CrApiOk<CrTrendRow[]>>(`/api/cr/trend?days=${days}`);
