<template>
  <div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#C8D0CC]">
      <div>
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1C2B2A]">
          Profil & Pengaturan Akun
        </h2>
        <p class="text-xs sm:text-sm text-[#5B6863] mt-0.5">
          Atur informasi personal, preferensi target karir, dan keamanan akunmu.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span
          v-if="user?.role === 'admin'"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#1C2B2A] text-[#F3F4F0]"
        >
          <span class="w-2 h-2 rounded-full bg-[#B8752F]"></span>
          Administrator
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-[#ECEEEA] text-[#5B6863] border border-[#C8D0CC]"
        >
          Member
        </span>
      </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left Sidebar: Account Summary Card -->
      <div class="lg:col-span-4 space-y-4">
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-6 text-center shadow-xs">
          <!-- Avatar -->
          <div class="relative inline-block mb-3">
            <div class="w-20 h-20 rounded-2xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center font-display text-2xl font-bold mx-auto shadow-sm">
              {{ getInitials(user?.name) }}
            </div>
            <span
              v-if="user?.linkedin_id"
              class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#0A66C2] text-white flex items-center justify-center shadow-sm"
              title="Terhubung dengan LinkedIn"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
              </svg>
            </span>
          </div>

          <h3 class="font-display text-lg font-bold text-[#1C2B2A]">
            {{ user?.name }}
          </h3>
          <p class="text-xs text-[#5B6863] mt-0.5">
            {{ user?.headline || 'Pencari Kerja Aktif' }}
          </p>
          <div class="text-[11px] text-[#82918B] mt-1">
            {{ user?.email }}
          </div>

          <!-- Mini stats -->
          <div class="grid grid-cols-2 gap-2 mt-6 pt-4 border-t border-[#C8D0CC]">
            <div class="bg-[#ECEEEA] rounded-lg p-2.5">
              <div class="text-[11px] text-[#5B6863]">Total Lamaran</div>
              <div class="font-display text-lg font-bold text-[#1C2B2A] mt-0.5">{{ stats.total_applications || 0 }}</div>
            </div>
            <div class="bg-[#ECEEEA] rounded-lg p-2.5">
              <div class="text-[11px] text-[#5B6863]">Status Akun</div>
              <div class="text-xs font-semibold text-[#B8752F] mt-1">Aktif</div>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-2 space-y-1">
          <button
            @click="activeTab = 'general'"
            class="w-full text-left px-3.5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors flex items-center justify-between cursor-pointer"
            :class="activeTab === 'general' ? 'bg-[#1C2B2A] text-[#F3F4F0]' : 'text-[#5B6863] hover:bg-[#ECEEEA] hover:text-[#1C2B2A]'"
          >
            <span class="flex items-center gap-2.5">
              <span>👤</span>
              Data Pribadi & Karir
            </span>
            <span v-if="activeTab === 'general'">➔</span>
          </button>

          <button
            @click="activeTab = 'security'"
            class="w-full text-left px-3.5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors flex items-center justify-between cursor-pointer"
            :class="activeTab === 'security' ? 'bg-[#1C2B2A] text-[#F3F4F0]' : 'text-[#5B6863] hover:bg-[#ECEEEA] hover:text-[#1C2B2A]'"
          >
            <span class="flex items-center gap-2.5">
              <span>🔒</span>
              Keamanan & Kata Sandi
            </span>
            <span v-if="activeTab === 'security'">➔</span>
          </button>
        </div>
      </div>

      <!-- Right Content Form Area -->
      <div class="lg:col-span-8">
        <!-- Tab 1: General Profile & Career Settings -->
        <div v-if="activeTab === 'general'" class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-6 shadow-xs space-y-5">
          <div class="border-b border-[#C8D0CC] pb-3">
            <h3 class="font-display text-lg font-bold text-[#1C2B2A]">
              Informasi Pribadi & Preferensi Karir
            </h3>
            <p class="text-xs text-[#5B6863] mt-0.5">
              Data ini membantu mempersonalisasi logbook dan target pencarian kerjamu.
            </p>
          </div>

          <form @submit.prevent="handleUpdateProfile" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Nama Lengkap</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Target Posisi / Headline</label>
                <input
                  v-model="profileForm.headline"
                  type="text"
                  placeholder="Contoh: Senior Frontend Engineer"
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Nomor WhatsApp / Telepon</label>
                <input
                  v-model="profileForm.phone"
                  type="tel"
                  placeholder="Contoh: 081234567890"
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Preferensi Lokasi / Tipe Kerja</label>
                <input
                  v-model="profileForm.preferred_location"
                  type="text"
                  placeholder="Contoh: Remote / Jakarta (Hybrid)"
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Target Gaji Minimum (IDR)</label>
                <input
                  v-model.number="profileForm.target_salary_min"
                  type="number"
                  step="500000"
                  placeholder="Contoh: 12000000"
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Target Gaji Maksimum (IDR)</label>
                <input
                  v-model.number="profileForm.target_salary_max"
                  type="number"
                  step="500000"
                  placeholder="Contoh: 18000000"
                  class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
                />
              </div>
            </div>

            <div class="pt-4 border-t border-[#C8D0CC] flex items-center justify-end">
              <button
                type="submit"
                :disabled="savingProfile"
                class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50"
              >
                {{ savingProfile ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 2: Security & Password -->
        <div v-if="activeTab === 'security'" class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-6 shadow-xs space-y-5">
          <div class="border-b border-[#C8D0CC] pb-3">
            <h3 class="font-display text-lg font-bold text-[#1C2B2A]">
              Keamanan & Kata Sandi
            </h3>
            <p class="text-xs text-[#5B6863] mt-0.5">
              Gunakan kata sandi yang kuat minimal 6 karakter untuk melindungi akunmu.
            </p>
          </div>

          <form @submit.prevent="handleChangePassword" class="space-y-4 max-w-md">
            <div>
              <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Kata Sandi Saat Ini</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                placeholder="••••••••"
                class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
              />
              <p class="text-[11px] text-[#82918B] mt-1">Kosongkan jika sebelumnya mendaftar via LinkedIn.</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Kata Sandi Baru (Min. 6 Karakter)</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                minlength="6"
                placeholder="••••••••"
                class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">Konfirmasi Kata Sandi Baru</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                minlength="6"
                placeholder="••••••••"
                class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
              />
            </div>

            <div class="pt-4 border-t border-[#C8D0CC] flex items-center justify-end">
              <button
                type="submit"
                :disabled="savingPassword"
                class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50"
              >
                {{ savingPassword ? 'Mengubah kata sandi...' : 'Perbarui Kata Sandi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import type { User, ProfileUpdatePayload, ChangePasswordPayload } from '../types/job';
import { profileApi } from '../services/api';

const props = defineProps<{
  user: User | null;
}>();

const emit = defineEmits<{
  (e: 'profile-updated', user: User): void;
  (e: 'show-toast', msg: string, type?: 'success' | 'error' | 'info'): void;
}>();

const activeTab = ref<'general' | 'security'>('general');
const savingProfile = ref(false);
const savingPassword = ref(false);

const stats = ref({
  total_applications: 0,
  has_linkedin: false,
  member_since: '',
});

const profileForm = reactive<ProfileUpdatePayload>({
  name: '',
  headline: '',
  phone: '',
  target_salary_min: null,
  target_salary_max: null,
  preferred_location: '',
});

const passwordForm = reactive<ChangePasswordPayload>({
  current_password: '',
  password: '',
  password_confirmation: '',
});

onMounted(async () => {
  if (props.user) {
    profileForm.name = props.user.name || '';
    profileForm.headline = props.user.headline || '';
    profileForm.phone = props.user.phone || '';
    profileForm.target_salary_min = props.user.target_salary_min || null;
    profileForm.target_salary_max = props.user.target_salary_max || null;
    profileForm.preferred_location = props.user.preferred_location || '';
  }

  try {
    const res = await profileApi.getProfile();
    if (res.data?.stats) {
      stats.value = res.data.stats;
    }
  } catch (err) {
    console.error('Failed to load profile stats:', err);
  }
});

const getInitials = (name?: string): string => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
};

const handleUpdateProfile = async () => {
  savingProfile.value = true;
  try {
    const res = await profileApi.updateProfile({ ...profileForm });
    if (res.data) {
      emit('profile-updated', res.data);
      emit('show-toast', 'Profil dan preferensi karir berhasil diperbarui.');
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal memperbarui profil.';
    emit('show-toast', msg, 'error');
  } finally {
    savingProfile.value = false;
  }
};

const handleChangePassword = async () => {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    emit('show-toast', 'Konfirmasi kata sandi baru tidak cocok.', 'error');
    return;
  }

  savingPassword.value = true;
  try {
    await profileApi.changePassword({ ...passwordForm });
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
    emit('show-toast', 'Kata sandi berhasil diperbarui.');
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal mengubah kata sandi.';
    emit('show-toast', msg, 'error');
  } finally {
    savingPassword.value = false;
  }
};
</script>
