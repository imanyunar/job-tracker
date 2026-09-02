export type JobStatus = 'applied' | 'screening' | 'interview' | 'offer' | 'rejected' | 'accepted';

export interface User {
  id: number;
  name: string;
  email: string;
  created_at?: string;
}

export interface AuthResponseData {
  user: User;
  token: string;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface JobApplication {
  id: number;
  user_id?: number | null;
  company_name: string;
  position: string;
  status: JobStatus;
  applied_date: string;
  source: string | null;
  job_url: string | null;
  location: string | null;
  notes: string | null;
  salary_range_min: number | null;
  salary_range_max: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface JobFormPayload {
  company_name: string;
  position: string;
  status: JobStatus;
  applied_date: string;
  source?: string | null;
  job_url?: string | null;
  location?: string | null;
  notes?: string | null;
  salary_range_min?: number | null;
  salary_range_max?: number | null;
}

export interface JobStats {
  total: number;
  active_in_process: number;
  positive_rate_percent: number;
  by_status: {
    applied: number;
    screening: number;
    interview: number;
    offer: number;
    rejected: number;
    accepted: number;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number;
    to?: number;
  };
  errors?: Record<string, string[]>;
}

export interface JobFilter {
  status?: string;
  search?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  per_page?: string | number;
  page?: number;
}
