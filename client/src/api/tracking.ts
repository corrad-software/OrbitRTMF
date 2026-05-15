import { apiRequest } from "./client";

export type ReviewStatus = "approved" | "reviewed" | "open" | null;

export type TrackingReview = {
  ba:   ReviewStatus;
  qa:   ReviewStatus;
  tech: ReviewStatus;
  dev:  ReviewStatus;
};

export type TrackingItems = {
  total:       number;
  implemented: number;
  partial:     number;
  missing:     number;
  unset:       number;
  pct:         number | null;
};

export type TrackingOverview = {
  totals: {
    pages:       number;
    done:        number;
    pending:     number;
    approvedAll: number;
    donePct:     number;
    approvedPct: number;
  };
  byReview: Record<string, { approved: number; reviewed: number; open: number }>;
  items: TrackingItems & { pct: number };
};

export type TrackingModule = {
  id:          number;
  code:        string;
  name:        string;
  pages:       number;
  done:        number;
  donePct:     number;
  approvedAll: number;
  items:       TrackingItems;
  review:      Record<string, { approved: number; reviewed: number; open: number }>;
};

export type TrackingPageRow = {
  id:        number;
  specId:    string;
  title:     string;
  module:    { id: number; code: string; name: string } | null;
  isDone:    boolean;
  review:    TrackingReview;
  items:     TrackingItems;
  assignees: string[];
};

export type TrackingModuleOption = { id: number; code: string; name: string };

export type TrkApiOk<T> = { data: T; meta?: Record<string, unknown> };

export const fetchTrackingOverview = (moduleId?: number) => {
  const qs = moduleId ? `?module_id=${moduleId}` : "";
  return apiRequest<TrkApiOk<TrackingOverview>>(`/api/tracking/overview${qs}`);
};

export const fetchTrackingByModule = (moduleId?: number) => {
  const qs = moduleId ? `?module_id=${moduleId}` : "";
  return apiRequest<TrkApiOk<TrackingModule[]>>(`/api/tracking/by-module${qs}`);
};

export const fetchTrackingPages = (params: {
  page?: number; limit?: number; q?: string;
  moduleId?: number; isDone?: boolean | null;
} = {}) => {
  const qs = new URLSearchParams();
  if (params.page)             qs.set("page",      String(params.page));
  if (params.limit)            qs.set("limit",     String(params.limit));
  if (params.q)                qs.set("q",         params.q);
  if (params.moduleId)         qs.set("module_id", String(params.moduleId));
  if (params.isDone !== null && params.isDone !== undefined)
                               qs.set("is_done",   params.isDone ? "1" : "0");
  const suffix = qs.toString() ? `?${qs.toString()}` : "";
  return apiRequest<TrkApiOk<TrackingPageRow[]>>(`/api/tracking/pages${suffix}`);
};

export const fetchTrackingModules = () =>
  apiRequest<TrkApiOk<TrackingModuleOption[]>>("/api/tracking/modules");

export type TrackingTrendRow = {
  tarikh:         string;
  halamanSelesai: number;
  reviewLulus:    number;
  jumlahSelesai:  number;
  jumlahLulus:    number;
};

export const fetchTrackingTrend = (days: number = 14, moduleId?: number) => {
  const qs = new URLSearchParams({ days: String(days) });
  if (moduleId) qs.set("module_id", String(moduleId));
  return apiRequest<TrkApiOk<TrackingTrendRow[]>>(`/api/tracking/trend?${qs.toString()}`);
};
