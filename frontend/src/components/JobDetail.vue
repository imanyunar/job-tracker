<template>
  <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg h-full flex flex-col overflow-hidden">
    <!-- If no job is selected -->
    <div
      v-if="!job"
      class="flex-1 flex flex-col items-center justify-center p-8 text-center text-[#5B6863]"
    >
      <div class="w-12 h-12 rounded-full bg-[#ECEEEA] flex items-center justify-center text-[#82918B] mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="font-display text-lg font-semibold text-[#1C2B2A]">Pilih Lamaran</h3>
      <p class="text-xs sm:text-sm mt-1 max-w-sm">
        Pilih salah satu lamaran dari daftar di sebelah kiri untuk melihat catatan, detail gaji, dan riwayat proses.
      </p>
    </div>

    <!-- Job Detail View -->
    <div v-else class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
      <!-- Header / Company & Position -->
      <div class="border-b border-[#C8D0CC] pb-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#5B6863]">
              {{ job.company_name }}
            </span>
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1C2B2A] mt-0.5 leading-tight">
              {{ job.position }}
            </h2>
            <div v-if="job.location" class="flex items-center gap-1.5 text-xs text-[#5B6863] mt-2">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ job.location }}</span>
            </div>
          </div>

          <!-- Quick Status Changer Dropdown -->
          <div class="flex items-center gap-2">
            <label for="detail-status-select" class="sr-only">Ubah status</label>
            <div class="relative">
              <select
                id="detail-status-select"
                :value="job.status"
                @change="onStatusChange(($event.target as HTMLSelectElement).value as JobStatus)"
                :disabled="submitting"
                class="appearance-none text-xs font-semibold uppercase tracking-wider px-3 py-1.5 pr-8 rounded border transition-colors cursor-pointer focus:outline-none"
                :class="getStatusSelectClass(job.status)"
              >
                <option value="applied">Applied</option>
                <option value="screening">Screening</option>
                <option value="interview">Interview</option>
                <option value="offer">Offer</option>
                <option value="rejected">Rejected</option>
                <option value="accepted">Accepted</option>
              </select>
              <svg class="w-3.5 h-3.5 absolute right-2.5 top-2.5 pointer-events-none text-current opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Key Metadata Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
        <div class="bg-[#ECEEEA] rounded-md p-3 border border-[#C8D0CC]/60">
          <div class="text-[11px] font-medium text-[#5B6863] uppercase tracking-wider">Tanggal Dilamar</div>
          <div class="text-sm font-semibold text-[#1C2B2A] mt-1 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-[#82918B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ formatDateLong(job.applied_date) }}
          </div>
        </div>

        <div class="bg-[#ECEEEA] rounded-md p-3 border border-[#C8D0CC]/60">
          <div class="text-[11px] font-medium text-[#5B6863] uppercase tracking-wider">Sumber Loker</div>
          <div class="text-sm font-semibold text-[#1C2B2A] mt-1 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-[#82918B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.102-1.101" />
            </svg>
            {{ job.source || 'Tidak dicatat' }}
          </div>
        </div>

        <div class="bg-[#ECEEEA] rounded-md p-3 border border-[#C8D0CC]/60 col-span-2 sm:col-span-1">
          <div class="text-[11px] font-medium text-[#5B6863] uppercase tracking-wider">Estimasi Gaji</div>
          <div class="text-sm font-semibold text-[#1C2B2A] mt-1">
            {{ formatSalaryRange(job.salary_range_min, job.salary_range_max) }}
          </div>
        </div>
      </div>

      <!-- Job URL if present -->
      <div v-if="job.job_url" class="flex items-center gap-2 text-xs text-[#5B6863]">
        <span class="font-medium">Tautan Lowongan:</span>
        <a
          :href="job.job_url"
          target="_blank"
          rel="noopener noreferrer"
          class="text-[#B8752F] hover:underline flex items-center gap-1 truncate max-w-md"
        >
          {{ job.job_url }}
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </a>
      </div>

      <!-- Notes / Catatan Proses -->
      <div class="space-y-2">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-[#5B6863]">
          Catatan & Perkembangan Proses
        </h4>
        <div class="bg-[#FFFFFF] border border-[#C8D0CC] rounded-lg p-4 max-w-2xl">
          <p
            v-if="job.notes"
            class="text-xs sm:text-sm text-[#1C2B2A] leading-relaxed whitespace-pre-line font-ui"
          >
            {{ job.notes }}
          </p>
          <p v-else class="text-xs text-[#82918B] italic">
            Belum ada catatan interview atau catatan khusus untuk lowongan ini. Klik edit untuk menambahkan catatan.
          </p>
        </div>
      </div>

      <!-- Footer Actions: Edit & Delete -->
      <div class="flex items-center justify-between pt-4 border-t border-[#C8D0CC]">
        <div class="text-[11px] text-[#82918B]">
          ID Lamaran: #{{ job.id }}
        </div>
        <div class="flex items-center gap-2.5">
          <button
            @click="$emit('edit', job)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#1C2B2A] bg-[#ECEEEA] hover:bg-[#E4E8E3] border border-[#C8D0CC] rounded transition-colors cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-[#5B6863]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>

          <button
            @click="$emit('delete', job)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#8B5A5A] bg-[#F8EFEF] hover:bg-[#F2DFDF] border border-[#8B5A5A]/30 rounded transition-colors cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-[#8B5A5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { JobApplication, JobStatus } from '../types/job';

const props = defineProps<{
  job: JobApplication | null;
  loading?: boolean;
  submitting?: boolean;
}>();

const emit = defineEmits<{
  (e: 'status-change', payload: { id: number; status: JobStatus }): void;
  (e: 'edit', job: JobApplication): void;
  (e: 'delete', job: JobApplication): void;
  (e: 'open-create'): void;
}>();

const onStatusChange = (newStatus: JobStatus) => {
  if (props.job && props.job.status !== newStatus) {
    emit('status-change', { id: props.job.id, status: newStatus });
  }
};

const getStatusSelectClass = (status: JobStatus): string => {
  switch (status) {
    case 'interview':
    case 'offer':
    case 'accepted':
      return 'bg-[#F7EFE6] text-[#B8752F] border-[#B8752F]/40';
    case 'rejected':
      return 'bg-[#F8EFEF] text-[#8B5A5A] border-[#8B5A5A]/40';
    default:
      return 'bg-[#ECEEEA] text-[#1C2B2A] border-[#C8D0CC]';
  }
};

const formatDateLong = (dateStr?: string): string => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(d);
};

const formatSalaryRange = (min: number | null, max: number | null): string => {
  if (!min && !max) return 'Tidak dicantumkan';

  const formatIDR = (val: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);

  if (min && max) return `${formatIDR(min)} - ${formatIDR(max)}`;
  if (min) return `Mulai ${formatIDR(min)}`;
  if (max) return `Hingga ${formatIDR(max)}`;
  return '-';
};
</script>
