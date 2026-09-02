import axios from 'axios';
import type { JobApplication, JobFormPayload, JobStats, ApiResponse, JobFilter } from '../types/job';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

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

  getExportUrl(): string {
    return '/api/job-applications/export';
  },
};

export default jobApi;
