const raw = import.meta.env.VITE_API_BASE_URL as string | undefined;
/** Empty = same origin (Coolify / reverse proxy). Undefined = local API default. */
export const API_BASE_URL =
  raw === undefined || raw === null ? "http://localhost:8000" : raw.replace(/\/$/, "");
