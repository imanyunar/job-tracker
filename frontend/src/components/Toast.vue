<template>
  <transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="visible"
      class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-[#1C2B2A] text-[#F3F4F0] px-4 py-3 rounded-lg shadow-xl border border-[#5B6863]/30 flex items-center justify-between gap-3 text-xs sm:text-sm"
    >
      <div class="flex items-center gap-2.5">
        <span
          class="w-2.5 h-2.5 rounded-full shrink-0"
          :class="{
            'bg-[#B8752F]': type === 'success',
            'bg-[#8B5A5A]': type === 'error',
            'bg-[#DCE1DE]': type === 'info'
          }"
        ></span>
        <span class="leading-snug">{{ message }}</span>
      </div>
      <button
        @click="close"
        class="text-[#82918B] hover:text-[#F3F4F0] transition-colors p-1"
        aria-label="Tutup notifikasi"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const visible = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'info'>('success');
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const show = (msg: string, toastType: 'success' | 'error' | 'info' = 'success', duration: number = 3500) => {
  message.value = msg;
  type.value = toastType;
  visible.value = true;

  if (timeoutId) clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    visible.value = false;
  }, duration);
};

const close = () => {
  visible.value = false;
  if (timeoutId) clearTimeout(timeoutId);
};

defineExpose({ show, close });
</script>
