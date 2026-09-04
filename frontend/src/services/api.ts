import axios from 'axios';
import type {
  JobApplication,
  JobFormPayload,
  JobStats,
  ApiResponse,
  JobFilter,
  LoginPayload,
  RegisterPayload,
  AuthResponseData,
  User,
  ProfileUpdatePayload,
  ChangePasswordPayload,
  AdminStats,
  ParsedJobData,
  EmailParsePayload,
  EmailParseResult,
  EmailApplyPayload,
  GmailSyncStatus,
  GmailScanResponse,
} from '../types/job';

const TOKEN_KEY = 'job_tracker_token';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Attach Bearer token to every request if available
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY);
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// On 401 Unauthorized, automatically handle session expiry
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const isAuthUrl = error.config.url?.includes('/auth/login') || error.config.url?.includes('/auth/register');
      if (!isAuthUrl) {
        localStorage.removeItem(TOKEN_KEY);
        window.dispatchEvent(new CustomEvent('auth:unauthorized'));
      }
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  },

  setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
  },

  removeToken(): void {
    localStorage.removeItem(TOKEN_KEY);
  },

  async register(data: RegisterPayload): Promise<ApiResponse<AuthResponseData>> {
    const response = await apiClient.post<ApiResponse<AuthResponseData>>('/auth/register', data);
    if (response.data?.data?.token) {
      this.setToken(response.data.data.token);
    }
    return response.data;
  },

  async login(data: LoginPayload): Promise<ApiResponse<AuthResponseData>> {
    const response = await apiClient.post<ApiResponse<AuthResponseData>>('/auth/login', data);
    if (response.data?.data?.token) {
      this.setToken(response.data.data.token);
    }
    return response.data;
  },

  async logout(): Promise<ApiResponse<null>> {
    try {
      const response = await apiClient.post<ApiResponse<null>>('/auth/logout');
      return response.data;
    } finally {
      this.removeToken();
    }
  },

  async getMe(): Promise<ApiResponse<User>> {
    const response = await apiClient.get<ApiResponse<User>>('/auth/me');
    return response.data;
  },
};

export const profileApi = {
  async getProfile(): Promise<ApiResponse<{ user: User; stats: { total_applications: number; has_linkedin: boolean; member_since: string } }>> {
    const response = await apiClient.get('/profile');
    return response.data;
  },

  async updateProfile(data: ProfileUpdatePayload): Promise<ApiResponse<User>> {
    const response = await apiClient.put<ApiResponse<User>>('/profile', data);
    return response.data;
  },

  async changePassword(data: ChangePasswordPayload): Promise<ApiResponse<null>> {
    const response = await apiClient.put<ApiResponse<null>>('/profile/password', data);
    return response.data;
  },
};

export const jobApi = {
  async getApplications(params: JobFilter = {}): Promise<ApiResponse<JobApplication[]>> {
    const response = await apiClient.get<ApiResponse<JobApplication[]>>('/job-applications', { params });
    return response.data;
  },

  async getStats(): Promise<ApiResponse<JobStats>> {
    const response = await apiClient.get<ApiResponse<JobStats>>('/job-applications/stats');
    return response.data;
  },

  async getApplication(id: number): Promise<ApiResponse<JobApplication>> {
    const response = await apiClient.get<ApiResponse<JobApplication>>(`/job-applications/${id}`);
    return response.data;
  },

  async createApplication(data: JobFormPayload): Promise<ApiResponse<JobApplication>> {
    const response = await apiClient.post<ApiResponse<JobApplication>>('/job-applications', data);
    return response.data;
  },

  async updateApplication(id: number, data: JobFormPayload): Promise<ApiResponse<JobApplication>> {
    const response = await apiClient.put<ApiResponse<JobApplication>>(`/job-applications/${id}`, data);
    return response.data;
  },

  async updateStatus(id: number, status: string): Promise<ApiResponse<JobApplication>> {
    const response = await apiClient.patch<ApiResponse<JobApplication>>(`/job-applications/${id}/status`, { status });
    return response.data;
  },

  async deleteApplication(id: number): Promise<ApiResponse<null>> {
    const response = await apiClient.delete<ApiResponse<null>>(`/job-applications/${id}`);
    return response.data;
  },

  async parseJobUrl(url: string): Promise<ApiResponse<ParsedJobData>> {
    const response = await apiClient.post<ApiResponse<ParsedJobData>>('/job-applications/parse-url', { url });
    return response.data;
  },

  getExportUrl(): string {
    return '/api/job-applications/export';
  },
};

export const adminApi = {
  async getStats(): Promise<ApiResponse<AdminStats>> {
    const response = await apiClient.get<ApiResponse<AdminStats>>('/admin/stats');
    return response.data;
  },

  async getUsers(params: { search?: string; role?: string; page?: number; per_page?: number } = {}): Promise<ApiResponse<User[]>> {
    const response = await apiClient.get<ApiResponse<User[]>>('/admin/users', { params });
    return response.data;
  },

  async updateUserRole(id: number, role: 'user' | 'admin'): Promise<ApiResponse<User>> {
    const response = await apiClient.patch<ApiResponse<User>>(`/admin/users/${id}/role`, { role });
    return response.data;
  },

  async getApplications(params: JobFilter = {}): Promise<ApiResponse<JobApplication[]>> {
    const response = await apiClient.get<ApiResponse<JobApplication[]>>('/admin/applications', { params });
    return response.data;
  },
};

export const emailSyncApi = {
  async parseEmail(data: EmailParsePayload): Promise<ApiResponse<EmailParseResult>> {
    const response = await apiClient.post<ApiResponse<EmailParseResult>>('/email-sync/parse', data);
    return response.data;
  },

  async applyUpdate(data: EmailApplyPayload): Promise<ApiResponse<{ application: JobApplication; old_status: string; new_status: string }>> {
    const response = await apiClient.post<ApiResponse<{ application: JobApplication; old_status: string; new_status: string }>>('/email-sync/apply', data);
    return response.data;
  },

  async createApplication(data: JobFormPayload): Promise<ApiResponse<JobApplication>> {
    const response = await apiClient.post<ApiResponse<JobApplication>>('/email-sync/create-application', data);
    return response.data;
  },

  async getGmailStatus(): Promise<ApiResponse<GmailSyncStatus>> {
    const response = await apiClient.get<ApiResponse<GmailSyncStatus>>('/email-sync/gmail/status');
    return response.data;
  },

  getGoogleRedirectUrl(): string {
    const token = localStorage.getItem(TOKEN_KEY);
    return `/api/auth/google/redirect${token ? `?token=${encodeURIComponent(token)}` : ''}`;
  },

  async disconnectGmail(): Promise<ApiResponse<null>> {
    const response = await apiClient.post<ApiResponse<null>>('/email-sync/gmail/disconnect');
    return response.data;
  },

  async scanGmail(): Promise<ApiResponse<GmailScanResponse>> {
    const response = await apiClient.post<ApiResponse<GmailScanResponse>>('/email-sync/gmail/scan');
    return response.data;
  },
};

export default {
  auth: authApi,
  profile: profileApi,
  jobs: jobApi,
  admin: adminApi,
  emailSync: emailSyncApi,
};

