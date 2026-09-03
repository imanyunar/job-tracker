<template>
  <teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        @click="$emit('close')"
      >
        <transition name="modal-scale" appear>
          <div
            class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl shadow-2xl max-w-md w-full overflow-hidden text-[#1C2B2A] transform"
            @click.stop
          >
            <div class="p-6 space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#F8EFEF] border border-[#8B5A5A]/30 flex items-center justify-center text-[#8B5A5A] shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-display text-lg font-bold text-[#1C2B2A]">
                    Hapus Data Lamaran?
                  </h3>
                  <p class="text-xs sm:text-sm text-[#5B6863] mt-1 leading-relaxed">
                    Apakah kamu yakin ingin menghapus data lamaran di
                    <strong class="text-[#1C2B2A]">{{ job?.company_name }}</strong>
                    posisi <strong class="text-[#1C2B2A]">{{ job?.position }}</strong>?
                    Aksi ini tidak dapat dibatalkan.
                  </p>
                </div>
              </div>

              <div class="pt-4 border-t border-[#C8D0CC] flex items-center justify-end gap-3">
                <button
                  type="button"
                  @click="$emit('close')"
                  class="px-4 py-2 text-xs sm:text-sm font-medium text-[#5B6863] hover:text-[#1C2B2A] bg-[#ECEEEA] hover:bg-[#E4E8E3] rounded-md border border-[#C8D0CC] transition-colors cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="button"
                  :disabled="submitting"
                  @click="$emit('confirm', job?.id)"
                  class="px-4 py-2 text-xs sm:text-sm font-medium text-white bg-[#8B5A5A] hover:bg-[#784A4A] rounded-md shadow-sm transition-colors cursor-pointer disabled:opacity-50"
                >
                  {{ submitting ? 'Menghapus...' : 'Hapus Lamaran' }}
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import type { JobApplication } from '../types/job';

defineProps<{
  isOpen: boolean;
  job: JobApplication | null;
  submitting?: boolean;
}>();

defineEmits<{
  (e: 'close'): void;
  (e: 'confirm', id: number | undefined): void;
}>();
</script>
