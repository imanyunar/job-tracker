import { ref, watch } from 'vue';
import type { JobApplication, JobFormPayload, JobStats, JobStatus } from '../types/job';
import { jobApi } from '../services/api';

export function useJobs() {
  const applications = ref<JobApplication[]>([]);
  const selectedJob = ref<JobApplication | null>(null);
  const stats = ref<JobStats>({
    total: 0,
    active_in_process: 0,
    positive_rate_percent: 0,
    by_status: {
      applied: 0,
      screening: 0,
      interview: 0,
      offer: 0,
      rejected: 0,
      accepted: 0,
    },
  });

  const loading = ref<boolean>(false);
  const loadingStats = ref<boolean>(false);
  const submitting = ref<boolean>(false);

  const searchQuery = ref<string>('');
  const selectedStatus = ref<string>('all');
  const sortBy = ref<string>('applied_date');
  const sortOrder = ref<'asc' | 'desc'>('desc');

  let debounceTimer: ReturnType<typeof setTimeout> | null = null;

  const fetchApplications = async () => {
    loading.value = true;
    try {
      const response = await jobApi.getApplications({
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
        per_page: 'all',
      });

      applications.value = response.data || [];

      // Update selectedJob reference if present, otherwise select the first item if available
      if (selectedJob.value) {
        const updated = applications.value.find((j) => j.id === selectedJob.value!.id);
        selectedJob.value = updated || applications.value[0] || null;
      } else if (applications.value.length > 0) {
        selectedJob.value = applications.value[0];
      } else {
        selectedJob.value = null;
      }
    } catch (err) {
      console.error('Failed to load applications:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchStats = async () => {
    loadingStats.value = true;
    try {
      const response = await jobApi.getStats();
      if (response.data) {
        stats.value = response.data;
      }
    } catch (err) {
      console.error('Failed to load statistics:', err);
    } finally {
      loadingStats.value = false;
    }
  };

  const selectJob = (job: JobApplication | null) => {
    selectedJob.value = job;
  };

  const createJob = async (payload: JobFormPayload) => {
    submitting.value = true;
    try {
      const response = await jobApi.createApplication(payload);
      await fetchApplications();
      await fetchStats();
      if (response.data) {
        selectedJob.value = response.data;
      }
      return response;
    } finally {
      submitting.value = false;
    }
  };

  const updateJob = async (id: number, payload: JobFormPayload) => {
    submitting.value = true;
    try {
      const response = await jobApi.updateApplication(id, payload);
      await fetchApplications();
      await fetchStats();
      if (response.data) {
        selectedJob.value = response.data;
      }
      return response;
    } finally {
      submitting.value = false;
    }
  };

  const changeStatus = async (id: number, status: JobStatus) => {
    submitting.value = true;
    try {
      const response = await jobApi.updateStatus(id, status);
      await fetchApplications();
      await fetchStats();
      if (response.data && selectedJob.value?.id === id) {
        selectedJob.value = response.data;
      }
      return response;
    } finally {
      submitting.value = false;
    }
  };

  const deleteJob = async (id: number) => {
    submitting.value = true;
    try {
      const response = await jobApi.deleteApplication(id);
      if (selectedJob.value?.id === id) {
        selectedJob.value = null;
      }
      await fetchApplications();
      await fetchStats();
      return response;
    } finally {
      submitting.value = false;
    }
  };

  const resetState = () => {
    applications.value = [];
    selectedJob.value = null;
    stats.value = {
      total: 0,
      active_in_process: 0,
      positive_rate_percent: 0,
      by_status: {
        applied: 0,
        screening: 0,
        interview: 0,
        offer: 0,
        rejected: 0,
        accepted: 0,
      },
    };
    searchQuery.value = '';
    selectedStatus.value = 'all';
    sortBy.value = 'applied_date';
  };

  // Listen to unauthorized event to immediately clear cache
  if (typeof window !== 'undefined') {
    window.addEventListener('auth:unauthorized', () => {
      resetState();
    });
  }

  // Watch filter & search changes with debounce
  watch([searchQuery], () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      fetchApplications();
    }, 300);
  });

  watch([selectedStatus, sortBy, sortOrder], () => {
    fetchApplications();
  });

  return {
    applications,
    selectedJob,
    stats,
    loading,
    loadingStats,
    submitting,
    searchQuery,
    selectedStatus,
    sortBy,
    sortOrder,
    fetchApplications,
    fetchStats,
    selectJob,
    createJob,
    updateJob,
    changeStatus,
    deleteJob,
    resetState,
  };
}
