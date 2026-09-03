<template>
  <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg flex flex-col h-full overflow-hidden">
    <!-- Search & Filter Controls -->
    <div class="p-3.5 border-b border-[#C8D0CC] space-y-3 bg-[#F3F4F0]">
      <!-- Search Input -->
      <div class="relative">
        <input
          :value="searchQuery"
          @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          type="text"
          placeholder="Cari instansi, posisi, lokasi..."
          class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-[#FFFFFF] border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A] transition-colors"
        />
        <svg
          class="w-4 h-4 text-[#82918B] absolute left-3 top-2.5 pointer-events-none"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <button
          v-if="searchQuery"
          @click="$emit('update:searchQuery', '')"
          class="absolute right-2.5 top-2.5 text-[#82918B] hover:text-[#1C2B2A] p-0.5"
          title="Bersihkan pencarian"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Status Filter Tabs / Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="$emit('update:selectedStatus', tab.value)"
          class="px-2.5 py-1 rounded whitespace-nowrap transition-colors cursor-pointer font-medium"
          :class="[
            selectedStatus === tab.value
              ? 'bg-[#1C2B2A] text-[#F3F4F0]'
              : 'bg-[#ECEEEA] text-[#5B6863] hover:bg-[#E4E8E3] hover:text-[#1C2B2A]'
          ]"
        >
          {{ tab.label }}
          <span v-if="tab.count !== undefined" class="ml-1 opacity-75 text-[11px]">
            ({{ tab.count }})
          </span>
        </button>
      </div>

      <!-- Summary Info & Sorter -->
      <div class="flex items-center justify-between text-xs text-[#5B6863] pt-0.5">
        <span>{{ applications.length }} lamaran ditampilkan</span>
        <div class="flex items-center gap-1">
          <label for="sort-select" class="sr-only">Urutkan</label>
          <span>Urut:</span>
          <select
            id="sort-select"
            :value="sortBy"
            @change="$emit('update:sortBy', ($event.target as HTMLSelectElement).value)"
            class="bg-transparent text-[#1C2B2A] font-medium border-b border-[#C8D0CC] pb-0.5 focus:outline-none cursor-pointer"
          >
            <option value="applied_date">Tgl Lamar</option>
            <option value="company_name">Perusahaan</option>
            <option value="position">Posisi</option>
            <option value="status">Status</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Application List -->
    <div class="flex-1 overflow-y-auto divide-y divide-[#C8D0CC]/60">
      <div v-if="loading" class="p-8 text-center text-[#5B6863] text-sm">
        <div class="inline-block animate-spin w-5 h-5 border-2 border-[#1C2B2A] border-t-transparent rounded-full mb-2"></div>
        <p>Memuat daftar lamaran...</p>
      </div>

      <div
        v-else-if="applications.length === 0"
        class="p-8 text-center text-[#5B6863] text-xs sm:text-sm space-y-2"
      >
        <p v-if="searchQuery || selectedStatus !== 'all'">
          Tidak ada lamaran yang sesuai dengan filter atau pencarian ini.
        </p>
        <div v-else class="space-y-3">
          <p>Belum ada lamaran tercatat. Mulai dari yang pertama kamu kirim minggu ini.</p>
          <button
            @click="$emit('open-create')"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-[#1C2B2A] bg-[#ECEEEA] hover:bg-[#E4E8E3] border border-[#C8D0CC] rounded cursor-pointer"
          >
            + Tambah lamaran pertama
          </button>
        </div>
      </div>

      <div
        v-else
        v-for="job in applications"
        :key="job.id"
        @click="handleJobSelect(job)"
        class="p-3.5 text-left transition-all duration-200 cursor-pointer border-l-[3.5px]"
        :class="[
          getStatusBorderClass(job.status),
          (selectedId === job.id || selectedJobId === job.id)
            ? 'bg-[#E4E8E3] shadow-[inset_0_1px_2px_rgba(0,0,0,0.04)] font-semibold'
            : 'bg-[#F3F4F0] hover:bg-[#ECEEEA]'
        ]"
      >
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-sm font-semibold text-[#1C2B2A] leading-snug line-clamp-1">
            {{ job.company_name }}
          </h3>
          <span
            class="text-[11px] font-medium px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0"
            :class="getStatusBadgeClass(job.status)"
          >
            {{ formatStatus(job.status) }}
          </span>
        </div>

        <div class="text-xs text-[#5B6863] mt-1 line-clamp-1 font-medium">
          {{ job.position }}
        </div>

        <div class="flex items-center justify-between text-[11px] text-[#82918B] mt-2">
          <span class="flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ formatDate(job.applied_date) }}
          </span>
          <span v-if="job.location" class="truncate max-w-[120px]">
            {{ job.location }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { JobApplication, JobStatus, JobStats } from '../types/job';

const props = defineProps<{
  applications: JobApplication[];
  selectedId?: number | null;
  selectedJobId?: number | null;
  loading: boolean;
  searchQuery: string;
  selectedStatus: string;
  sortBy: string;
  stats?: JobStats;
}>();

const emit = defineEmits<{
  (e: 'update:searchQuery', value: string): void;
  (e: 'update:selectedStatus', value: string): void;
  (e: 'update:sortBy', value: string): void;
  (e: 'select', job: JobApplication): void;
  (e: 'select-job', job: JobApplication): void;
  (e: 'open-create'): void;
}>();

const handleJobSelect = (job: JobApplication) => {
  emit('select', job);
  emit('select-job', job);
};

const statusTabs = computed(() => [
  { label: 'Semua', value: 'all', count: props.stats?.total },
  { label: 'Applied', value: 'applied', count: props.stats?.by_status?.applied },
  { label: 'Screening', value: 'screening', count: props.stats?.by_status?.screening },
  { label: 'Interview', value: 'interview', count: props.stats?.by_status?.interview },
  { label: 'Offer', value: 'offer', count: props.stats?.by_status?.offer },
  { label: 'Rejected', value: 'rejected', count: props.stats?.by_status?.rejected },
  { label: 'Accepted', value: 'accepted', count: props.stats?.by_status?.accepted },
]);

const getStatusBorderClass = (status: JobStatus): string => {
  if (status === 'interview' || status === 'offer' || status === 'accepted') {
    return 'border-l-[#B8752F]';
  }
  if (status === 'rejected') {
    return 'border-l-[#8B5A5A]';
  }
  return 'border-l-transparent';
};

const getStatusBadgeClass = (status: JobStatus): string => {
  switch (status) {
    case 'interview':
    case 'offer':
    case 'accepted':
      return 'bg-[#F7EFE6] text-[#B8752F]';
    case 'rejected':
      return 'bg-[#F8EFEF] text-[#8B5A5A]';
    default:
      return 'bg-[#ECEEEA] text-[#5B6863]';
  }
};

const formatStatus = (status: JobStatus): string => {
  const map: Record<JobStatus, string> = {
    applied: 'Applied',
    screening: 'Screening',
    interview: 'Interview',
    offer: 'Offer',
    rejected: 'Rejected',
    accepted: 'Accepted',
  };
  return map[status] || status;
};

const formatDate = (dateStr?: string): string => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(d);
};
</script>
