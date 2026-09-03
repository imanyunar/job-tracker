<template>
  <div class="space-y-6">
    <!-- Header Admin -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#C8D0CC]">
      <div>
        <div class="flex items-center gap-2.5">
          <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1C2B2A]">
            Panel Administrator
          </h2>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#1C2B2A] text-[#F3F4F0] border border-[#C8D0CC]">
            Admin Access
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[#5B6863] mt-0.5">
          Pantau seluruh aktivitas sistem, metrik agregat, dan kelola pengguna aplikasi.
        </p>
      </div>

      <button
        @click="loadAdminData"
        :disabled="loading"
        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-[#1C2B2A] bg-[#ECEEEA] hover:bg-[#E4E8E3] border border-[#C8D0CC] rounded-lg transition-colors cursor-pointer self-start sm:self-auto"
      >
        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh Data
      </button>
    </div>

    <!-- Admin System Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
      <!-- Total Users -->
      <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-4 shadow-xs">
        <div class="text-xs font-semibold text-[#5B6863] uppercase tracking-wider">Total Pengguna</div>
        <div class="mt-1 flex items-baseline gap-2">
          <span class="font-display text-3xl font-bold text-[#1C2B2A]">{{ adminStats?.total_users || 0 }}</span>
          <span class="text-xs text-[#5B6863]">akun</span>
        </div>
      </div>

      <!-- Total Applications -->
      <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-4 shadow-xs">
        <div class="text-xs font-semibold text-[#5B6863] uppercase tracking-wider">Total Lamaran</div>
        <div class="mt-1 flex items-baseline gap-2">
          <span class="font-display text-3xl font-bold text-[#1C2B2A]">{{ adminStats?.total_applications || 0 }}</span>
          <span class="text-xs text-[#5B6863]">seluruh user</span>
        </div>
      </div>

      <!-- Active in Process -->
      <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-4 shadow-xs">
        <div class="text-xs font-semibold text-[#5B6863] uppercase tracking-wider">Sedang Diproses</div>
        <div class="mt-1 flex items-baseline gap-2">
          <span class="font-display text-3xl font-bold text-[#B8752F]">{{ adminStats?.active_in_process || 0 }}</span>
          <span class="text-xs text-[#5B6863]">on-progress</span>
        </div>
      </div>

      <!-- Global Positive Conversion -->
      <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl p-4 shadow-xs">
        <div class="text-xs font-semibold text-[#5B6863] uppercase tracking-wider">Rasio Sukses Global</div>
        <div class="mt-1 flex items-baseline gap-2">
          <span class="font-display text-3xl font-bold text-[#1C2B2A]">{{ adminStats?.global_positive_rate || 0 }}%</span>
          <span class="text-xs text-[#5B6863]">interview/offer</span>
        </div>
      </div>
    </div>

    <!-- Sub Navigation Tabs: Users vs Global Applications -->
    <div class="flex border-b border-[#C8D0CC] gap-4">
      <button
        @click="viewMode = 'users'"
        class="pb-2.5 text-xs sm:text-sm font-semibold transition-colors cursor-pointer border-b-2"
        :class="viewMode === 'users' ? 'border-[#1C2B2A] text-[#1C2B2A]' : 'border-transparent text-[#5B6863] hover:text-[#1C2B2A]'"
      >
        👥 Manajemen Pengguna ({{ users.length }})
      </button>
      <button
        @click="viewMode = 'applications'"
        class="pb-2.5 text-xs sm:text-sm font-semibold transition-colors cursor-pointer border-b-2"
        :class="viewMode === 'applications' ? 'border-[#1C2B2A] text-[#1C2B2A]' : 'border-transparent text-[#5B6863] hover:text-[#1C2B2A]'"
      >
        📑 Monitor Lamaran Global ({{ globalApps.length }})
      </button>
    </div>

    <!-- Section 1: User Management Table -->
    <div v-if="viewMode === 'users'" class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl overflow-hidden shadow-xs">
      <!-- Search & Filters Toolbar -->
      <div class="p-4 border-b border-[#C8D0CC] bg-[#ECEEEA] flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
          <input
            v-model="userSearch"
            type="text"
            placeholder="Cari nama atau email user..."
            class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-[#C8D0CC] rounded-md text-[#1C2B2A] placeholder-[#82918B] focus:outline-none focus:border-[#1C2B2A]"
          />
          <svg class="w-3.5 h-3.5 text-[#82918B] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
          <label for="role-filter-select" class="text-[#5B6863]">Role:</label>
          <select
            id="role-filter-select"
            v-model="roleFilter"
            class="bg-white border border-[#C8D0CC] rounded-md px-2.5 py-1 text-xs focus:outline-none"
          >
            <option value="all">Semua Role</option>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>

      <!-- Users Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-[#ECEEEA] text-[#5B6863] border-b border-[#C8D0CC] uppercase tracking-wider font-semibold">
            <tr>
              <th class="py-3 px-4">Pengguna</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Akun LinkedIn</th>
              <th class="py-3 px-4 text-center">Total Lamaran</th>
              <th class="py-3 px-4">Bergabung</th>
              <th class="py-3 px-4 text-right">Aksi Role</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#C8D0CC]/60 bg-[#F3F4F0]">
            <!-- Skeleton Rows -->
            <tr v-if="loading" v-for="i in 5" :key="'user-skel-' + i" class="animate-pulse">
              <td class="py-3.5 px-4 space-y-1.5">
                <div class="h-3.5 w-32 rounded skeleton-shimmer"></div>
                <div class="h-3 w-40 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-4 w-14 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-3.5 w-20 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="h-4 w-8 rounded skeleton-shimmer mx-auto"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-3 w-20 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="h-6 w-24 rounded skeleton-shimmer ml-auto"></div>
              </td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="6" class="py-8 text-center text-[#5B6863]">
                Tidak ada pengguna yang sesuai dengan filter.
              </td>
            </tr>
            <tr v-else v-for="u in filteredUsers" :key="u.id" class="hover:bg-[#ECEEEA]/70 transition-colors">
              <td class="py-3 px-4">
                <div class="font-semibold text-[#1C2B2A]">{{ u.name }}</div>
                <div class="text-[11px] text-[#5B6863]">{{ u.email }}</div>
                <div v-if="u.headline" class="text-[10px] text-[#82918B] truncate max-w-xs">{{ u.headline }}</div>
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                  :class="u.role === 'admin' ? 'bg-[#1C2B2A] text-[#F3F4F0]' : 'bg-[#ECEEEA] text-[#5B6863] border border-[#C8D0CC]'"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span v-if="u.linkedin_id" class="inline-flex items-center gap-1 text-[11px] text-[#0A66C2] font-medium">
                  <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                  </svg>
                  Terhubung
                </span>
                <span v-else class="text-[11px] text-[#82918B]">-</span>
              </td>
              <td class="py-3 px-4 text-center font-semibold text-[#1C2B2A]">
                {{ u.job_applications_count ?? 0 }}
              </td>
              <td class="py-3 px-4 text-[#5B6863] text-[11px]">
                {{ formatDate(u.created_at) }}
              </td>
              <td class="py-3 px-4 text-right">
                <button
                  v-if="u.id !== currentUser?.id"
                  @click="toggleRole(u)"
                  class="px-2.5 py-1 text-[11px] font-medium rounded border transition-colors cursor-pointer"
                  :class="u.role === 'admin' ? 'border-[#8B5A5A] text-[#8B5A5A] hover:bg-[#F8EFEF]' : 'border-[#1C2B2A] text-[#1C2B2A] hover:bg-[#ECEEEA]'"
                >
                  {{ u.role === 'admin' ? 'Jadikan User' : 'Promote ke Admin' }}
                </button>
                <span v-else class="text-[11px] text-[#82918B] italic">Akun Anda</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Section 2: Global Applications Monitor -->
    <div v-if="viewMode === 'applications'" class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-[#C8D0CC] bg-[#ECEEEA] text-xs font-semibold text-[#1C2B2A]">
        Daftar Seluruh Lamaran yang Tercatat di Sistem
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-[#ECEEEA] text-[#5B6863] border-b border-[#C8D0CC] uppercase tracking-wider font-semibold">
            <tr>
              <th class="py-3 px-4">Pengguna</th>
              <th class="py-3 px-4">Perusahaan & Posisi</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Tgl Lamar</th>
              <th class="py-3 px-4">Lokasi & Gaji</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#C8D0CC]/60 bg-[#F3F4F0]">
            <!-- Skeleton Rows -->
            <tr v-if="loading" v-for="i in 5" :key="'app-skel-' + i" class="animate-pulse">
              <td class="py-3.5 px-4 space-y-1.5">
                <div class="h-3.5 w-28 rounded skeleton-shimmer"></div>
                <div class="h-3 w-36 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4 space-y-1.5">
                <div class="h-3.5 w-32 rounded skeleton-shimmer"></div>
                <div class="h-3 w-24 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-4 w-16 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-3.5 w-20 rounded skeleton-shimmer"></div>
              </td>
              <td class="py-3.5 px-4">
                <div class="h-3.5 w-24 rounded skeleton-shimmer"></div>
              </td>
            </tr>
            <tr v-else-if="globalApps.length === 0">
              <td colspan="5" class="py-8 text-center text-[#5B6863]">
                Belum ada data lamaran di sistem.
              </td>
            </tr>
            <tr v-else v-for="app in globalApps" :key="app.id" class="hover:bg-[#ECEEEA]/70 transition-colors">
              <td class="py-3 px-4">
                <div class="font-semibold text-[#1C2B2A]">{{ app.user?.name || 'User #' + app.user_id }}</div>
                <div class="text-[11px] text-[#5B6863]">{{ app.user?.email || '-' }}</div>
              </td>
              <td class="py-3 px-4">
                <div class="font-semibold text-[#1C2B2A]">{{ app.company_name }}</div>
                <div class="text-[11px] text-[#5B6863]">{{ app.position }}</div>
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                  :class="getStatusBadgeClass(app.status)"
                >
                  {{ app.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#5B6863] text-[11px]">
                {{ formatDate(app.applied_date) }}
              </td>
              <td class="py-3 px-4 text-[#5B6863] text-[11px]">
                <div>{{ app.location || '-' }}</div>
                <div v-if="app.salary_range_min || app.salary_range_max" class="text-[#B8752F] font-medium">
                  {{ formatSalary(app.salary_range_min, app.salary_range_max) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import type { User, AdminStats, JobApplication, JobStatus } from '../types/job';
import { adminApi } from '../services/api';

defineProps<{
  currentUser: User | null;
}>();

const emit = defineEmits<{
  (e: 'show-toast', msg: string, type?: 'success' | 'error' | 'info'): void;
}>();

const loading = ref(false);
const viewMode = ref<'users' | 'applications'>('users');
const adminStats = ref<AdminStats | null>(null);
const users = ref<User[]>([]);
const globalApps = ref<JobApplication[]>([]);

const userSearch = ref('');
const roleFilter = ref('all');

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    const matchSearch =
      !userSearch.value ||
      u.name.toLowerCase().includes(userSearch.value.toLowerCase()) ||
      u.email.toLowerCase().includes(userSearch.value.toLowerCase());

    const matchRole = roleFilter.value === 'all' || u.role === roleFilter.value;

    return matchSearch && matchRole;
  });
});

const loadAdminData = async () => {
  loading.value = true;
  try {
    const [statsRes, usersRes, appsRes] = await Promise.all([
      adminApi.getStats(),
      adminApi.getUsers({ per_page: 50 }),
      adminApi.getApplications({ per_page: 50 }),
    ]);

    if (statsRes.data) adminStats.value = statsRes.data;
    if (usersRes.data) users.value = usersRes.data;
    if (appsRes.data) globalApps.value = appsRes.data;
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal memuat data panel admin.';
    emit('show-toast', msg, 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadAdminData();
});

const toggleRole = async (targetUser: User) => {
  const newRole = targetUser.role === 'admin' ? 'user' : 'admin';
  try {
    const res = await adminApi.updateUserRole(targetUser.id, newRole);
    if (res.data) {
      targetUser.role = res.data.role;
      emit('show-toast', `Role ${targetUser.name} berhasil diubah menjadi '${newRole}'.`);
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal mengubah role.';
    emit('show-toast', msg, 'error');
  }
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

const formatDate = (dateStr?: string): string => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(d);
};

const formatSalary = (min: number | null, max: number | null): string => {
  if (!min && !max) return '-';
  const fmt = (val: number) => `Rp ${(val / 1000000).toFixed(0)}jt`;
  if (min && max) return `${fmt(min)} - ${fmt(max)}`;
  if (min) return `>${fmt(min)}`;
  if (max) return `<${fmt(max)}`;
  return '-';
};
</script>
