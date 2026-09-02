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
            class="w-full max-w-md bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl shadow-2xl overflow-hidden text-[#1C2B2A] transform"
            @click.stop
          >
            <!-- Header -->
            <div class="p-6 text-center border-b border-[#C8D0CC] bg-[#ECEEEA] relative">
              <button
                @click="$emit('close')"
                class="absolute right-4 top-4 text-[#82918B] hover:text-[#1C2B2A] p-1.5 rounded-lg hover:bg-[#E4E8E3] transition-colors cursor-pointer"
                aria-label="Tutup"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <div class="w-11 h-11 rounded-2xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center mx-auto mb-3 shadow-sm transform hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <h2 class="font-display text-xl font-bold text-[#1C2B2A] tracking-tight">
                Job Application Tracker
              </h2>
              <p class="text-xs text-[#5B6863] mt-1">
                {{ mode === 'login' ? 'Masuk untuk mengelola lamaran kerjamu.' : 'Buat akun baru untuk mulai mencatat proses karirmu.' }}
              </p>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">
              <!-- Error Message -->
              <transition name="fade-slide">
                <div
                  v-if="errorMessage"
                  class="bg-[#F8EFEF] border border-[#8B5A5A]/30 text-[#8B5A5A] px-3.5 py-2.5 rounded-lg text-xs leading-relaxed"
                >
                  {{ errorMessage }}
                </div>
              </transition>

              <!-- Social Login: LinkedIn -->
              <div>
                <button
                  type="button"
                  @click="handleLinkedInLogin"
                  class="w-full py-2.5 px-4 text-xs sm:text-sm font-semibold text-white bg-[#0A66C2] hover:bg-[#004182] active:bg-[#094c92] rounded-xl shadow-xs transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2.5 cursor-pointer"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                  </svg>
                  Masuk dengan LinkedIn
                </button>
              </div>

              <!-- Divider -->
              <div class="relative flex items-center justify-center">
                <div class="border-t border-[#C8D0CC] w-full"></div>
                <span class="bg-[#F3F4F0] px-3 text-[11px] text-[#82918B] uppercase tracking-wider font-medium shrink-0">
                  atau dengan email
                </span>
                <div class="border-t border-[#C8D0CC] w-full"></div>
              </div>

              <!-- Mode Tabs -->
              <div class="flex rounded-xl bg-[#DCE1DE] p-1 border border-[#C8D0CC]">
                <button
                  type="button"
                  @click="mode = 'login'; clearErrors()"
                  class="flex-1 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer"
                  :class="mode === 'login' ? 'bg-[#F3F4F0] text-[#1C2B2A] shadow-xs' : 'text-[#5B6863] hover:text-[#1C2B2A]'"
                >
                  Masuk Akun
                </button>
                <button
                  type="button"
                  @click="mode = 'register'; clearErrors()"
                  class="flex-1 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer"
                  :class="mode === 'register' ? 'bg-[#F3F4F0] text-[#1C2B2A] shadow-xs' : 'text-[#5B6863] hover:text-[#1C2B2A]'"
                >
                  Daftar Baru
                </button>
              </div>

              <!-- Login Form -->
              <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="space-y-3.5 pt-1">
                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Email</label>
                  <input
                    v-model="loginForm.email"
                    type="email"
                    required
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Kata Sandi</label>
                  <input
                    v-model="loginForm.password"
                    type="password"
                    required
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="submitting"
                  class="w-full py-2.5 px-4 text-xs sm:text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-95 cursor-pointer disabled:opacity-50 mt-2"
                >
                  {{ submitting ? 'Memeriksa kredensial...' : 'Masuk ke Akun' }}
                </button>
              </form>

              <!-- Register Form -->
              <form v-else @submit.prevent="handleRegister" class="space-y-3 pt-1 max-h-[48vh] overflow-y-auto pr-1">
                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Nama Lengkap</label>
                  <input
                    v-model="registerForm.name"
                    type="text"
                    required
                    placeholder="Contoh: Iman Yunar"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Email</label>
                  <input
                    v-model="registerForm.email"
                    type="email"
                    required
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Kata Sandi (Min. 6 Karakter)</label>
                  <input
                    v-model="registerForm.password"
                    type="password"
                    required
                    minlength="6"
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Konfirmasi Kata Sandi</label>
                  <input
                    v-model="registerForm.password_confirmation"
                    type="password"
                    required
                    minlength="6"
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="submitting"
                  class="w-full py-2.5 px-4 text-xs sm:text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-95 cursor-pointer disabled:opacity-50 mt-2"
                >
                  {{ submitting ? 'Mendaftarkan akun...' : 'Buat Akun & Mulai' }}
                </button>
              </form>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { useAuth } from '../composables/useAuth';

const props = defineProps<{
  isOpen: boolean;
  initialMode?: 'login' | 'register';
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'auth-success'): void;
}>();

const { login, register, submitting } = useAuth();

const mode = ref<'login' | 'register'>('login');
const errorMessage = ref('');

watch(
  () => props.initialMode,
  (val) => {
    if (val) mode.value = val;
  },
  { immediate: true }
);

const loginForm = reactive({
  email: '',
  password: '',
});

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const clearErrors = () => {
  errorMessage.value = '';
};

const handleLinkedInLogin = () => {
  window.location.href = '/api/auth/linkedin/redirect';
};

const handleLogin = async () => {
  errorMessage.value = '';
  try {
    await login({
      email: loginForm.email.trim(),
      password: loginForm.password,
    });
    emit('auth-success');
    emit('close');
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Email atau kata sandi tidak valid.';
  }
};

const handleRegister = async () => {
  errorMessage.value = '';
  if (registerForm.password !== registerForm.password_confirmation) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
    return;
  }
  if (registerForm.password.length < 6) {
    errorMessage.value = 'Kata sandi minimal 6 karakter.';
    return;
  }

  try {
    await register({
      name: registerForm.name.trim(),
      email: registerForm.email.trim(),
      password: registerForm.password,
      password_confirmation: registerForm.password_confirmation,
    });
    emit('auth-success');
    emit('close');
  } catch (err: any) {
    const errorData = err.response?.data;
    if (errorData?.errors) {
      const firstKey = Object.keys(errorData.errors)[0];
      errorMessage.value = errorData.errors[firstKey][0];
    } else {
      errorMessage.value = errorData?.message || 'Pendaftaran akun gagal. Silakan coba lagi.';
    }
  }
};
</script>
