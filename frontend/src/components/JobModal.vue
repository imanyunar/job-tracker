<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div
      class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl shadow-2xl max-w-xl w-full overflow-hidden transition-all text-[#1C2B2A]"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-[#C8D0CC] flex items-center justify-between bg-[#ECEEEA]">
        <div>
          <h3 class="font-display text-lg sm:text-xl font-bold text-[#1C2B2A]">
            {{ isEditing ? 'Edit Data Lamaran' : 'Tambah Lamaran Baru' }}
          </h3>
          <p class="text-xs text-[#5B6863] mt-0.5">
            {{ isEditing ? 'Perbarui informasi dan catatan proses lamaran.' : 'Catat instansi dan posisi yang baru kamu lamar.' }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="text-[#82918B] hover:text-[#1C2B2A] p-1 rounded-md transition-colors"
          aria-label="Tutup"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
        <!-- Error Alert -->
        <div v-if="errorMessage" class="bg-[#F8EFEF] border border-[#8B5A5A]/30 text-[#8B5A5A] px-4 py-2.5 rounded-lg text-xs flex items-center justify-between">
          <span>{{ errorMessage }}</span>
          <button type="button" @click="errorMessage = ''" class="font-bold text-xs hover:text-[#1C2B2A] ml-2">✕</button>
        </div>

        <!-- Quick Auto-Fill URL Bar -->
        <div class="bg-[#ECEEEA] border border-[#C8D0CC] rounded-xl p-3.5 space-y-2">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs">
            <span class="flex items-center gap-1.5 font-bold text-[#1C2B2A]">
              <span class="text-[#B8752F]">⚡</span>
              <span>Isi Otomatis dari Link Lowongan</span>
            </span>
            <span class="text-[11px] text-[#5B6863]">LinkedIn, Jobstreet, Glints, Indeed, dll.</span>
          </div>

          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <input
                v-model="quickUrlInput"
                type="url"
                placeholder="Tempel tautan lowongan di sini..."
                @keydown.enter.prevent="handleParseUrl"
                :disabled="parsingUrl"
                class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
              />
              <svg class="w-3.5 h-3.5 text-[#82918B] absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.102-1.101" />
              </svg>
            </div>

            <button
              type="button"
              @click="handleParseUrl"
              :disabled="parsingUrl || !quickUrlInput.trim()"
              class="px-3.5 py-2 text-xs font-semibold text-white bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-lg shadow-2xs transition-all disabled:opacity-50 cursor-pointer flex items-center gap-1.5 shrink-0"
            >
              <svg v-if="parsingUrl" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>{{ parsingUrl ? 'Menganalisis...' : 'Ekstrak Data' }}</span>
            </button>
          </div>

          <!-- Parse Success Banner -->
          <transition name="fade-slide">
            <div v-if="parseSuccessMsg" class="text-[11px] text-[#2E6B4D] font-semibold flex items-center gap-1 pt-0.5">
              <span>✓</span>
              <span>{{ parseSuccessMsg }}</span>
            </div>
          </transition>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Nama Instansi / Perusahaan -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Nama Instansi / Perusahaan <span class="text-[#8B5A5A]">*</span>
            </label>
            <input
              v-model="form.company_name"
              type="text"
              required
              placeholder="Contoh: PT Nawa Digital"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>

          <!-- Posisi / Role -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Posisi / Role <span class="text-[#8B5A5A]">*</span>
            </label>
            <input
              v-model="form.position"
              type="text"
              required
              placeholder="Contoh: Frontend Engineer"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Status -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Status Lamaran <span class="text-[#8B5A5A]">*</span>
            </label>
            <select
              v-model="form.status"
              required
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] focus:outline-none focus:border-[#1C2B2A]"
            >
              <option value="applied">Applied (Baru Kirim)</option>
              <option value="screening">Screening (Review CV)</option>
              <option value="interview">Interview (Wawancara)</option>
              <option value="offer">Offer (Penawaran Kerja)</option>
              <option value="rejected">Rejected (Ditolak)</option>
              <option value="accepted">Accepted (Diterima)</option>
            </select>
          </div>

          <!-- Tanggal Submit Lamar -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Tanggal Kirim Lamaran <span class="text-[#8B5A5A]">*</span>
            </label>
            <input
              v-model="form.applied_date"
              type="date"
              required
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Sumber Loker -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Sumber Lowongan
            </label>
            <input
              v-model="form.source"
              type="text"
              placeholder="LinkedIn, Glints, Referral, Jobstreet"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>

          <!-- Lokasi / Tipe Kerja -->
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Lokasi / Tipe Kerja
            </label>
            <input
              v-model="form.location"
              type="text"
              placeholder="Jakarta Selatan, Remote, Hybrid"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>
        </div>

        <!-- Tautan Loker -->
        <div>
          <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
            Tautan Lowongan (URL)
          </label>
          <input
            v-model="form.job_url"
            type="url"
            placeholder="https://linkedin.com/jobs/view/..."
            class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
          />
        </div>

        <!-- Rentang Estimasi Gaji -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Gaji Minimum (IDR)
            </label>
            <input
              v-model.number="form.salary_range_min"
              type="number"
              min="0"
              step="500000"
              placeholder="Contoh: 10000000"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
              Gaji Maksimum (IDR)
            </label>
            <input
              v-model.number="form.salary_range_max"
              type="number"
              min="0"
              step="500000"
              placeholder="Contoh: 15000000"
              class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
            />
          </div>
        </div>

        <!-- Catatan / Notes -->
        <div>
          <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
            Catatan Tambahan & Info Interview
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Catatan tahapan wawancara, nama recruiter, materi tes teknis..."
            class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="pt-4 border-t border-[#C8D0CC] flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-xs sm:text-sm font-medium text-[#5B6863] hover:text-[#1C2B2A] bg-[#ECEEEA] hover:bg-[#E4E8E3] rounded-md border border-[#C8D0CC] transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-5 py-2 text-xs sm:text-sm font-medium text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] rounded-md shadow-sm transition-colors cursor-pointer disabled:opacity-50"
          >
            {{ submitting ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Lamaran') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { JobApplication, JobFormPayload, JobStatus } from '../types/job';
import { jobApi } from '../services/api';

const props = defineProps<{
  isOpen: boolean;
  jobToEdit?: JobApplication | null;
  submitting?: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', payload: JobFormPayload): void;
}>();

const isEditing = ref(false);
const errorMessage = ref('');
const quickUrlInput = ref('');
const parsingUrl = ref(false);
const parseSuccessMsg = ref('');

const getTodayString = () => new Date().toISOString().split('T')[0];

const initialForm = (): JobFormPayload => ({
  company_name: '',
  position: '',
  status: 'applied' as JobStatus,
  applied_date: getTodayString(),
  source: '',
  job_url: '',
  location: '',
  salary_range_min: null,
  salary_range_max: null,
  notes: '',
});

const form = ref<JobFormPayload>(initialForm());

const handleParseUrl = async () => {
  const url = quickUrlInput.value.trim();
  if (!url) return;

  errorMessage.value = '';
  parseSuccessMsg.value = '';
  parsingUrl.value = true;

  try {
    const res = await jobApi.parseJobUrl(url);
    if (res.data) {
      const d = res.data;
      if (d.company_name) form.value.company_name = d.company_name;
      if (d.position) form.value.position = d.position;
      if (d.source) form.value.source = d.source;
      if (d.location) form.value.location = d.location;
      if (d.job_url) form.value.job_url = d.job_url;
      if (d.salary_range_min !== null && d.salary_range_min !== undefined) {
        form.value.salary_range_min = d.salary_range_min;
      }
      if (d.salary_range_max !== null && d.salary_range_max !== undefined) {
        form.value.salary_range_max = d.salary_range_max;
      }
      if (d.notes) {
        form.value.notes = d.notes;
      }

      const platformText = d.source ? `dari ${d.source}` : '';
      parseSuccessMsg.value = `Data lowongan ${platformText} berhasil diisi otomatis! Silakan tinjau dan lengkapi.`;
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal mengekstrak data dari link lowongan.';
    errorMessage.value = msg;
  } finally {
    parsingUrl.value = false;
  }
};

watch(
  () => props.isOpen,
  (val) => {
    errorMessage.value = '';
    parseSuccessMsg.value = '';
    quickUrlInput.value = '';
    if (val) {
      if (props.jobToEdit) {
        isEditing.value = true;
        form.value = {
          company_name: props.jobToEdit.company_name,
          position: props.jobToEdit.position,
          status: props.jobToEdit.status,
          applied_date: props.jobToEdit.applied_date ? props.jobToEdit.applied_date.split('T')[0] : getTodayString(),
          source: props.jobToEdit.source || '',
          job_url: props.jobToEdit.job_url || '',
          location: props.jobToEdit.location || '',
          salary_range_min: props.jobToEdit.salary_range_min || null,
          salary_range_max: props.jobToEdit.salary_range_max || null,
          notes: props.jobToEdit.notes || '',
        };
      } else {
        isEditing.value = false;
        form.value = initialForm();
      }
    }
  }
);

const handleSubmit = () => {
  if (!form.value.company_name.trim() || !form.value.position.trim()) {
    errorMessage.value = 'Nama instansi dan posisi lowongan wajib diisi.';
    return;
  }
  if (!form.value.applied_date) {
    errorMessage.value = 'Tanggal lamar belum diisi, lengkapi dulu sebelum disimpan.';
    return;
  }

  if (
    form.value.salary_range_min &&
    form.value.salary_range_max &&
    Number(form.value.salary_range_min) > Number(form.value.salary_range_max)
  ) {
    errorMessage.value = 'Estimasi gaji maksimum harus lebih besar atau sama dengan gaji minimum.';
    return;
  }

  const payload = { ...form.value };
  if (payload.job_url) {
    const trimmed = payload.job_url.trim();
    if (trimmed && !/^https?:\/\//i.test(trimmed)) {
      payload.job_url = `https://${trimmed}`;
    } else {
      payload.job_url = trimmed || null;
    }
  }

  emit('submit', payload);
};
</script>
