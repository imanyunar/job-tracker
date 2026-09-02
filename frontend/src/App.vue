<template>
  <div>
    <!-- Gated: When NOT Authenticated, show Landing Page with Animations and Auth Modal -->
    <LandingPage
      v-if="!isAuthenticated"
      @auth-success="handleAuthSuccess"
    />

    <!-- Main Authenticated Workspace -->
    <div v-else class="min-h-screen bg-[#DCE1DE] text-[#1C2B2A] flex flex-col font-ui selection:bg-[#B8752F] selection:text-white">
      <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 flex flex-col flex-1 pb-12">
        <!-- Header Navigation Bar -->
        <HeaderBar
          :user="user"
          :current-view="currentView"
          @update:current-view="currentView = $event"
          @open-create="openCreateModal"
          @logout="handleLogout"
          @export-csv="handleExportCsv"
        />

        <!-- Main Workspace Views with Smooth Animation -->
        <main class="flex-1 mt-5">
          <transition name="fade-slide" mode="out-in">
            <!-- View 1: My Applications Tracker (Dual-Panel) -->
            <div v-if="currentView === 'tracker'" key="tracker" class="space-y-5">
              <!-- Summary Metrics Cards -->
              <JobStats :stats="stats" />

              <!-- Dual Panel Layout -->
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                <!-- Left Panel: Job Applications List (5 cols) -->
                <div class="lg:col-span-5 h-[calc(100vh-270px)] min-h-[500px]">
                  <JobList
                    :applications="applications"
                    :selected-id="selectedJob?.id ?? null"
                    :loading="loading"
                    :search-query="searchQuery"
                    :selected-status="selectedStatus"
                    :sort-by="sortBy"
                    @select="selectJob"
                    @update:search-query="searchQuery = $event"
                    @update:selected-status="selectedStatus = $event"
                    @update:sort-by="sortBy = $event"
                  />
                </div>

                <!-- Right Panel: Job Detail & Notes Ledger (7 cols) -->
                <div class="lg:col-span-7 h-[calc(100vh-270px)] min-h-[500px]">
                  <JobDetail
                    :job="selectedJob"
                    :loading="loading"
                    @edit="openEditModal"
                    @delete="openDeleteModal"
                    @status-change="handleStatusChange"
                    @open-create="openCreateModal"
                  />
                </div>
              </div>
            </div>

            <!-- View 2: Profile & Career Preferences -->
            <div v-else-if="currentView === 'profile'" key="profile">
              <ProfileView
                :user="user"
                @profile-updated="handleProfileUpdated"
                @show-toast="handleShowToast"
              />
            </div>

            <!-- View 3: Admin Management Panel -->
            <div v-else-if="currentView === 'admin' && user?.role === 'admin'" key="admin">
              <AdminView
                :current-user="user"
                @show-toast="handleShowToast"
              />
            </div>
          </transition>
        </main>
      </div>

      <!-- Job Application Create / Edit Modal -->
      <JobModal
        :is-open="isModalOpen"
        :job-to-edit="jobToEdit"
        :submitting="submitting"
        @close="isModalOpen = false"
        @submit="handleFormSubmit"
      />

      <!-- Delete Confirmation Modal -->
      <DeleteConfirmModal
        :is-open="isDeleteModalOpen"
        :job="jobToDelete"
        :submitting="submitting"
        @close="isDeleteModalOpen = false"
        @confirm="handleConfirmDelete"
      />
    </div>

    <!-- Global Toast Notifications (Always mounted) -->
    <Toast ref="toastRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { JobApplication, JobFormPayload, JobStatus, User } from './types/job';
import { useJobs } from './composables/useJobs';
import { useAuth } from './composables/useAuth';
import { authApi, jobApi } from './services/api';

import LandingPage from './components/LandingPage.vue';
import HeaderBar from './components/HeaderBar.vue';
import JobStats from './components/JobStats.vue';
import JobList from './components/JobList.vue';
import JobDetail from './components/JobDetail.vue';
import JobModal from './components/JobModal.vue';
import DeleteConfirmModal from './components/DeleteConfirmModal.vue';
import ProfileView from './components/ProfileView.vue';
import AdminView from './components/AdminView.vue';
import Toast from './components/Toast.vue';

const { user, isAuthenticated, checkAuth, logout } = useAuth();
const currentView = ref<'tracker' | 'profile' | 'admin'>('tracker');

const {
  applications,
  selectedJob,
  stats,
  loading,
  submitting,
  searchQuery,
  selectedStatus,
  sortBy,
  fetchApplications,
  fetchStats,
  selectJob,
  createJob,
  updateJob,
  changeStatus,
  deleteJob,
} = useJobs();

const toastRef = ref<InstanceType<typeof Toast> | null>(null);

// Modal states
const isModalOpen = ref(false);
const jobToEdit = ref<JobApplication | null>(null);

const isDeleteModalOpen = ref(false);
const jobToDelete = ref<JobApplication | null>(null);

onMounted(async () => {
  // Check if returning from LinkedIn OAuth callback with token or error
  const urlParams = new URLSearchParams(window.location.search);
  const tokenFromUrl = urlParams.get('token');
  const errorFromUrl = urlParams.get('error');

  if (tokenFromUrl) {
    authApi.setToken(tokenFromUrl);
    window.history.replaceState({}, document.title, window.location.pathname);
    const isAuthed = await checkAuth();
    if (isAuthed) {
      currentView.value = 'tracker';
      await loadInitialData();
      toastRef.value?.show(`Berhasil masuk dengan LinkedIn! Selamat datang, ${user.value?.name || 'Pengguna'}.`);
      return;
    }
  }

  if (errorFromUrl) {
    window.history.replaceState({}, document.title, window.location.pathname);
    toastRef.value?.show(decodeURIComponent(errorFromUrl), 'error');
  }

  const isAuthed = await checkAuth();
  if (isAuthed) {
    await loadInitialData();
  }
});

const loadInitialData = async () => {
  await Promise.all([fetchApplications(), fetchStats()]);
};

const handleAuthSuccess = async () => {
  await checkAuth();
  currentView.value = 'tracker';
  await loadInitialData();
  toastRef.value?.show(`Selamat datang kembali, ${user.value?.name || 'Pengguna'}!`);
};

const handleLogout = async () => {
  await logout();
  toastRef.value?.show('Berhasil keluar dari akun.', 'info');
};

const handleProfileUpdated = (updatedUser: User) => {
  if (user.value) {
    user.value = { ...user.value, ...updatedUser };
  }
};

const handleShowToast = (msg: string, type: 'success' | 'error' | 'info' = 'success') => {
  toastRef.value?.show(msg, type);
};

const handleExportCsv = () => {
  const token = authApi.getToken();
  const exportUrl = jobApi.getExportUrl();
  
  // Trigger download with auth token
  fetch(exportUrl, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'text/csv',
    }
  })
  .then(res => res.blob())
  .then(blob => {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `job-applications-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    toastRef.value?.show('File CSV riwayat lamaran berhasil diunduh.');
  })
  .catch(() => {
    toastRef.value?.show('Gagal mengunduh file CSV.', 'error');
  });
};

const openCreateModal = () => {
  jobToEdit.value = null;
  isModalOpen.value = true;
};

const openEditModal = (job: JobApplication) => {
  jobToEdit.value = job;
  isModalOpen.value = true;
};

const openDeleteModal = (job: JobApplication) => {
  jobToDelete.value = job;
  isDeleteModalOpen.value = true;
};

const handleFormSubmit = async (payload: JobFormPayload) => {
  try {
    if (jobToEdit.value) {
      await updateJob(jobToEdit.value.id, payload);
      toastRef.value?.show(`Lamaran di ${payload.company_name} berhasil diperbarui.`);
    } else {
      await createJob(payload);
      toastRef.value?.show(`Lamaran baru di ${payload.company_name} berhasil dicatat.`);
    }
    isModalOpen.value = false;
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menyimpan data lamaran.';
    toastRef.value?.show(msg, 'error');
  }
};

const handleStatusChange = async (payload: { id: number; status: JobStatus }) => {
  try {
    await changeStatus(payload.id, payload.status);
    toastRef.value?.show(`Status lamaran diubah menjadi "${payload.status}".`);
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal mengubah status.';
    toastRef.value?.show(msg, 'error');
  }
};

const handleConfirmDelete = async () => {
  if (!jobToDelete.value) return;
  try {
    const company = jobToDelete.value.company_name;
    await deleteJob(jobToDelete.value.id);
    toastRef.value?.show(`Lamaran di ${company} berhasil dihapus.`);
    isDeleteModalOpen.value = false;
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menghapus lamaran.';
    toastRef.value?.show(msg, 'error');
  }
};
</script>
