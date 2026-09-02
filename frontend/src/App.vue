<template>
  <div class="min-h-screen bg-[#DCE1DE] text-[#1C2B2A] flex flex-col">
    <!-- Top Container -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex flex-col flex-1 gap-4 sm:gap-6">
      <!-- Header -->
      <HeaderBar @open-create="handleOpenCreate" />

      <!-- Stats Bar -->
      <JobStats :stats="stats" />

      <!-- Main Dual Panel Workspace -->
      <main class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 flex-1 min-h-[560px]">
        <!-- Left Panel: Job List (5 cols on lg) -->
        <section class="lg:col-span-5 h-[520px] lg:h-[calc(100vh-270px)] min-h-[480px]">
          <JobList
            :applications="applications"
            :selected-job-id="selectedJob?.id"
            :loading="loading"
            :stats="stats"
            v-model:search-query="searchQuery"
            v-model:selected-status="selectedStatus"
            v-model:sort-by="sortBy"
            @select-job="selectJob"
            @open-create="handleOpenCreate"
          />
        </section>

        <!-- Right Panel: Job Detail (7 cols on lg) -->
        <section class="lg:col-span-7 h-[520px] lg:h-[calc(100vh-270px)] min-h-[480px]">
          <JobDetail
            :job="selectedJob"
            :submitting="submitting"
            @change-status="handleChangeStatus"
            @edit-job="handleOpenEdit"
            @delete-job="handleOpenDelete"
          />
        </section>
      </main>
    </div>

    <!-- Modals -->
    <JobModal
      :is-open="isModalOpen"
      :job-to-edit="jobToEdit"
      :submitting="submitting"
      @close="isModalOpen = false"
      @submit="handleFormSubmit"
    />

    <DeleteConfirmModal
      :is-open="isDeleteModalOpen"
      :job="jobToDelete"
      :submitting="submitting"
      @close="isDeleteModalOpen = false"
      @confirm="handleConfirmDelete"
    />

    <!-- Toast Notifications -->
    <Toast ref="toastRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { JobApplication, JobFormPayload, JobStatus } from './types/job';
import { useJobs } from './composables/useJobs';

import HeaderBar from './components/HeaderBar.vue';
import JobStats from './components/JobStats.vue';
import JobList from './components/JobList.vue';
import JobDetail from './components/JobDetail.vue';
import JobModal from './components/JobModal.vue';
import DeleteConfirmModal from './components/DeleteConfirmModal.vue';
import Toast from './components/Toast.vue';

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
  await Promise.all([fetchApplications(), fetchStats()]);
});

const handleOpenCreate = () => {
  jobToEdit.value = null;
  isModalOpen.value = true;
};

const handleOpenEdit = (job: JobApplication) => {
  jobToEdit.value = job;
  isModalOpen.value = true;
};

const handleOpenDelete = (job: JobApplication) => {
  jobToDelete.value = job;
  isDeleteModalOpen.value = true;
};

const handleFormSubmit = async (payload: JobFormPayload) => {
  try {
    if (jobToEdit.value) {
      await updateJob(jobToEdit.value.id, payload);
      toastRef.value?.show(`Perubahan lamaran di ${payload.company_name} berhasil disimpan.`);
    } else {
      await createJob(payload);
      toastRef.value?.show(`Lamaran baru di ${payload.company_name} berhasil ditambahkan.`);
    }
    isModalOpen.value = false;
  } catch (err: any) {
    const message = err.response?.data?.message || 'Gagal menyimpan data lamaran.';
    toastRef.value?.show(message, 'error');
  }
};

const handleChangeStatus = async (id: number, status: JobStatus) => {
  try {
    const res = await changeStatus(id, status);
    toastRef.value?.show(res.message || `Status diubah menjadi '${status}'.`);
  } catch (err: any) {
    const message = err.response?.data?.message || 'Gagal mengubah status.';
    toastRef.value?.show(message, 'error');
  }
};

const handleConfirmDelete = async (id: number | undefined) => {
  if (!id) return;
  try {
    const company = jobToDelete.value?.company_name || 'Lamaran';
    await deleteJob(id);
    toastRef.value?.show(`Data lamaran ${company} berhasil dihapus.`);
    isDeleteModalOpen.value = false;
  } catch (err: any) {
    const message = err.response?.data?.message || 'Gagal menghapus lamaran.';
    toastRef.value?.show(message, 'error');
  }
};
</script>
