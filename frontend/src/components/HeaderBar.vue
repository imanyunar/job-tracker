<template>
  <header class="flex flex-col gap-4 py-4 border-b border-[#C8D0CC]">
    <!-- Top Row: Brand & Profile Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center shadow-xs">
          <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-[#1C2B2A] leading-tight">
            Job Tracker
          </h1>
          <p class="text-xs text-[#5B6863]">
            {{ user?.headline || 'Logbook Lamaran Kerja Pribadi' }}
          </p>
        </div>
      </div>

      <!-- Right Action Tools -->
      <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
        <!-- Tracker Action Buttons (shown only on tracker view) -->
        <template v-if="currentView === 'tracker'">
          <button
            @click="$emit('export-csv')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#1C2B2A] bg-[#F3F4F0] hover:bg-[#ECEEEA] active:bg-[#E4E8E3] border border-[#C8D0CC] rounded-lg transition-colors cursor-pointer"
            title="Unduh data lamaran dalam format CSV"
          >
            <svg class="w-3.5 h-3.5 text-[#5B6863]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Ekspor CSV
          </button>

          <button
            @click="$emit('open-create')"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-lg shadow-xs transition-colors cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Lamaran
          </button>
        </template>

        <!-- User Profile Pill & Logout -->
        <div v-if="user" class="flex items-center gap-2 px-2.5 py-1.5 bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg text-xs shadow-2xs">
          <div class="w-6 h-6 rounded-full bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center font-bold text-[10px]">
            {{ getInitials(user.name) }}
          </div>
          <div class="max-w-[120px] truncate">
            <div class="font-semibold text-[#1C2B2A] truncate leading-tight">{{ user.name }}</div>
          </div>
          <span
            v-if="user.role === 'admin'"
            class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-[#1C2B2A] text-[#B8752F] uppercase"
          >
            Admin
          </span>
          <button
            @click="$emit('logout')"
            class="ml-1 text-[#8B5A5A] hover:text-[#6D3F3F] p-1 transition-colors cursor-pointer"
            title="Keluar dari akun (Logout)"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Row: Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-0.5 text-xs sm:text-sm">
      <button
        @click="$emit('update:currentView', 'tracker')"
        class="px-3.5 py-1.5 rounded-lg font-semibold transition-all cursor-pointer flex items-center gap-2"
        :class="currentView === 'tracker' ? 'bg-[#1C2B2A] text-[#F3F4F0] shadow-xs' : 'bg-[#ECEEEA] text-[#5B6863] hover:bg-[#E4E8E3] hover:text-[#1C2B2A]'"
      >
        <span>📋</span>
        Tracker Saya
      </button>

      <button
        @click="$emit('update:currentView', 'profile')"
        class="px-3.5 py-1.5 rounded-lg font-semibold transition-all cursor-pointer flex items-center gap-2"
        :class="currentView === 'profile' ? 'bg-[#1C2B2A] text-[#F3F4F0] shadow-xs' : 'bg-[#ECEEEA] text-[#5B6863] hover:bg-[#E4E8E3] hover:text-[#1C2B2A]'"
      >
        <span>👤</span>
        Profil & Pengaturan
      </button>

      <button
        v-if="user?.role === 'admin'"
        @click="$emit('update:currentView', 'admin')"
        class="px-3.5 py-1.5 rounded-lg font-semibold transition-all cursor-pointer flex items-center gap-2"
        :class="currentView === 'admin' ? 'bg-[#1C2B2A] text-[#F3F4F0] shadow-xs' : 'bg-[#ECEEEA] text-[#5B6863] hover:bg-[#E4E8E3] hover:text-[#1C2B2A]'"
      >
        <span>🛡️</span>
        Panel Admin
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import type { User } from '../types/job';

defineProps<{
  user?: User | null;
  currentView: 'tracker' | 'profile' | 'admin';
}>();

defineEmits<{
  (e: 'update:currentView', view: 'tracker' | 'profile' | 'admin'): void;
  (e: 'open-create'): void;
  (e: 'logout'): void;
  (e: 'export-csv'): void;
}>();

const getInitials = (name?: string): string => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
};
</script>
