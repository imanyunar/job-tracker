export type JobStatus = 'applied' | 'screening' | 'interview' | 'offer' | 'rejected' | 'accepted';

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin';
  headline?: string | null;
  phone?: string | null;
  target_salary_min?: number | null;
  target_salary_max?: number | null;
  preferred_location?: string | null;
  linkedin_id?: string | null;
  avatar?: string | null;
  created_at?: string;
  job_applications_count?: number;
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

export interface ProfileUpdatePayload {
  name: string;
  headline?: string | null;
  phone?: string | null;
  target_salary_min?: number | null;
  target_salary_max?: number | null;
  preferred_location?: string | null;
}

export interface ChangePasswordPayload {
  current_password?: string;
  password: string;
  password_confirmation: string;
}

export interface JobApplication {
  id: number;
  user_id?: number | null;
  user?: {
    id: number;
    name: string;
    email: string;
  } | null;
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

export interface ParsedJobData {
  company_name?: string | null;
  position?: string | null;
  status?: JobStatus;
  source?: string | null;
  location?: string | null;
  job_url?: string | null;
  salary_range_min?: number | null;
  salary_range_max?: number | null;
  notes?: string | null;
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

export interface AdminStats {
  total_users: number;
  total_admins: number;
  total_applications: number;
  active_in_process: number;
  global_positive_rate: number;
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

export interface EmailParsePayload {
  content: string;
  subject?: string;
  sender?: string;
  application_id?: number;
}

export interface EmailParseResult {
  success: boolean;
  status: JobStatus;
  confidence: 'high' | 'medium' | 'low';
  confidence_score: number;
  status_label: string;
  current_status: JobStatus;
  status_changed: boolean;
  detected_company?: string | null;
  matched_application?: {
    id: number;
    company_name: string;
    position: string;
    status: JobStatus;
    applied_date?: string;
  } | null;
  matched_confidence: 'high' | 'medium' | 'low';
  meeting_link?: string | null;
  meeting_datetime?: string | null;
  detected_keywords: string[];
  excerpt: string;
  suggested_note: string;
}

export interface EmailApplyPayload {
  application_id: number;
  status: JobStatus;
  notes?: string;
  append_note?: boolean;
}

export interface GmailSyncStatus {
  is_connected: boolean;
  is_token_expired: boolean;
  google_email?: string | null;
  last_synced_at?: string | null;
  has_client_config: boolean;
}

export interface GmailScanResultItem {
  id: string;
  subject: string;
  sender: string;
  date: string;
  analysis: EmailParseResult;
}

export interface GmailScanResponse {
  scanned_count: number;
  results: GmailScanResultItem[];
  last_synced_at: string;
}

