<template>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <!-- Card Total -->
    <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg p-3.5 sm:p-4 hover:shadow-xs transition-all duration-200">
      <div class="text-xs text-[#5B6863] font-medium tracking-wide">Total Lamaran</div>
      <div v-if="loading" class="mt-2 flex items-baseline gap-2 animate-pulse">
        <div class="h-7 w-12 rounded skeleton-shimmer"></div>
        <div class="h-3 w-16 rounded skeleton-shimmer"></div>
      </div>
      <div v-else class="mt-1 flex items-baseline gap-2">
        <span class="font-display text-2xl font-bold text-[#1C2B2A] transition-all">{{ stats.total }}</span>
        <span class="text-xs text-[#5B6863]">posisi tercatat</span>
      </div>
    </div>

    <!-- Card Sedang Proses -->
    <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg p-3.5 sm:p-4 hover:shadow-xs transition-all duration-200">
      <div class="text-xs text-[#5B6863] font-medium tracking-wide">Sedang Proses</div>
      <div v-if="loading" class="mt-2 flex items-baseline gap-2 animate-pulse">
        <div class="h-7 w-12 rounded skeleton-shimmer"></div>
        <div class="h-3 w-20 rounded skeleton-shimmer"></div>
      </div>
      <div v-else class="mt-1 flex items-baseline gap-2">
        <span class="font-display text-2xl font-bold text-[#1C2B2A] transition-all">{{ stats.active_in_process }}</span>
        <span class="text-xs text-[#5B6863]">menunggu respon</span>
      </div>
    </div>

    <!-- Card Interview & Offer -->
    <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg p-3.5 sm:p-4 hover:shadow-xs transition-all duration-200">
      <div class="text-xs text-[#5B6863] font-medium tracking-wide">Tahap Lanjut</div>
      <div v-if="loading" class="mt-2 flex items-baseline gap-2 animate-pulse">
        <div class="h-7 w-12 rounded skeleton-shimmer"></div>
        <div class="h-3 w-16 rounded skeleton-shimmer"></div>
      </div>
      <div v-else class="mt-1 flex items-baseline gap-2">
        <span class="font-display text-2xl font-bold text-[#B8752F] transition-all">
          {{ (stats.by_status?.interview || 0) + (stats.by_status?.offer || 0) + (stats.by_status?.accepted || 0) }}
        </span>
        <span class="text-xs text-[#5B6863]">interview & offer</span>
      </div>
    </div>

    <!-- Card Rasio Positif -->
    <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-lg p-3.5 sm:p-4 hover:shadow-xs transition-all duration-200">
      <div class="text-xs text-[#5B6863] font-medium tracking-wide">Rasio Tahap Lanjut</div>
      <div v-if="loading" class="mt-2 flex items-baseline gap-2 animate-pulse">
        <div class="h-7 w-14 rounded skeleton-shimmer"></div>
        <div class="h-3 w-16 rounded skeleton-shimmer"></div>
      </div>
      <div v-else class="mt-1 flex items-baseline gap-2">
        <span class="font-display text-2xl font-bold text-[#1C2B2A] transition-all">{{ stats.positive_rate_percent }}%</span>
        <span class="text-xs text-[#5B6863]">konversi positif</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { JobStats } from '../types/job';

defineProps<{
  stats: JobStats;
  loading?: boolean;
}>();
</script>
