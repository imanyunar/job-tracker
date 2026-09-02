<template>
  <div class="min-h-screen bg-[#DCE1DE] text-[#1C2B2A] flex flex-col font-ui selection:bg-[#B8752F] selection:text-white relative overflow-hidden">
    <!-- Ambient Animated Background Glow Orbs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#1C2B2A]/10 rounded-full blur-3xl pointer-events-none animate-float-slow"></div>
    <div class="absolute top-48 -right-32 w-96 h-96 bg-[#B8752F]/15 rounded-full blur-3xl pointer-events-none animate-float-reverse"></div>
    <div class="absolute bottom-20 left-1/3 w-80 h-80 bg-[#5B6863]/10 rounded-full blur-3xl pointer-events-none animate-pulse-glow"></div>

    <!-- Top Navbar -->
    <nav class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between relative z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center shadow-md transform hover:rotate-6 transition-transform duration-300">
          <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <span class="font-display text-xl font-bold tracking-tight text-[#1C2B2A]">
            Job Tracker
          </span>
          <span class="hidden sm:inline-block ml-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#ECEEEA] text-[#5B6863] border border-[#C8D0CC]">
            v2.0
          </span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="openAuth('login')"
          class="px-4 py-2 text-xs sm:text-sm font-semibold text-[#1C2B2A] hover:text-[#000000] bg-transparent hover:bg-[#ECEEEA] rounded-xl transition-all duration-200 cursor-pointer active:scale-95"
        >
          Masuk
        </button>
        <button
          @click="openAuth('register')"
          class="px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] rounded-xl shadow-md transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95 cursor-pointer"
        >
          Daftar Akun
        </button>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-14 text-center relative z-10">

      <!-- Main Headline with Smooth Dynamic Role Ticker -->
      <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#1C2B2A] leading-[1.18] max-w-4xl mx-auto">
        Pantau lamaran kerja impianmu dengan <span class="italic text-[#B8752F] font-serif font-normal inline-block transform hover:scale-105 transition-transform duration-300">tenang</span> & terstruktur.
      </h1>

      <!-- Rotating Role subtitle -->
      <div class="mt-4 flex items-center justify-center gap-2 text-sm sm:text-base font-medium text-[#5B6863]">
        <span>Cocok untuk:</span>
        <transition name="fade-slide" mode="out-in">
          <span :key="currentRoleIndex" class="inline-block px-3 py-1 rounded-lg bg-[#ECEEEA] text-[#1C2B2A] font-semibold border border-[#C8D0CC] shadow-2xs">
            {{ rotatingRoles[currentRoleIndex] }}
          </span>
        </transition>
      </div>

      <p class="mt-5 text-sm sm:text-base lg:text-lg text-[#5B6863] max-w-2xl mx-auto leading-relaxed">
        Tinggalkan spreadsheet rumit. Catat jadwal interview, batas offering, dan feedback recruiter dalam satu dashboard minimalis yang terintegrasi.
      </p>

      <!-- CTA Buttons with Micro-interactions -->
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
        <button
          @click="openAuth('register')"
          class="w-full sm:w-auto px-7 py-3.5 text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 active:scale-95 cursor-pointer flex items-center justify-center gap-2"
        >
          Mulai Sekarang — Gratis
          <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>

        <button
          @click="handleLinkedInLogin"
          class="w-full sm:w-auto px-6 py-3.5 text-sm font-semibold text-white bg-[#0A66C2] hover:bg-[#004182] active:bg-[#094c92] rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 active:scale-95 cursor-pointer flex items-center justify-center gap-2.5"
        >
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
            <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
          </svg>
          Masuk dengan LinkedIn
        </button>
      </div>

      <!-- Animated Interactive Demo Preview Mockup -->
      <div class="mt-14 max-w-4xl mx-auto">
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl shadow-2xl overflow-hidden text-left p-4 sm:p-6 transition-all duration-300 hover:shadow-[0_20px_50px_rgba(28,43,42,0.12)]">
          <!-- Top Window Bar -->
          <div class="flex items-center justify-between pb-4 border-b border-[#C8D0CC]">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-[#8B5A5A]/60"></span>
              <span class="w-3 h-3 rounded-full bg-[#B8752F]/60"></span>
              <span class="w-3 h-3 rounded-full bg-[#5B6863]/60"></span>
              <span class="ml-2 text-xs font-mono text-[#82918B]">job-tracker.app / demo-pipeline</span>
            </div>
            <!-- Auto-cycling Indicator -->
            <div class="flex items-center gap-2">
              <span class="text-[11px] text-[#5B6863] hidden sm:inline">Auto-stepping:</span>
              <div class="w-16 h-1.5 bg-[#DCE1DE] rounded-full overflow-hidden">
                <div class="h-full bg-[#B8752F] transition-all duration-300" :style="{ width: `${((activeDemoIdx + 1) / demoJobs.length) * 100}%` }"></div>
              </div>
              <span class="text-[10px] px-2 py-0.5 rounded bg-[#ECEEEA] text-[#1C2B2A] font-semibold border border-[#C8D0CC]">
                LIVE SIMULATION
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4">
            <!-- Mock Left Column -->
            <div class="md:col-span-5 bg-[#FFFFFF] border border-[#C8D0CC] rounded-xl p-3 space-y-2">
              <div class="flex items-center justify-between px-1 text-[11px] font-semibold uppercase tracking-wider text-[#82918B]">
                <span>Daftar Lamaran</span>
                <span class="text-[10px] text-[#B8752F]">Klik kartu ➔</span>
              </div>

              <div
                v-for="(demo, idx) in demoJobs"
                :key="idx"
                @click="activeDemoIdx = idx"
                class="p-2.5 rounded-lg border transition-all duration-300 cursor-pointer text-xs transform hover:translate-x-1"
                :class="[
                  activeDemoIdx === idx
                    ? 'bg-[#E4E8E3] border-[#1C2B2A]/50 shadow-xs ring-1 ring-[#1C2B2A]/20'
                    : 'bg-[#F3F4F0] border-[#C8D0CC]/60 hover:bg-[#ECEEEA]',
                  demo.status === 'interview' || demo.status === 'offer'
                    ? 'border-l-[4px] border-l-[#B8752F]'
                    : (demo.status === 'rejected' ? 'border-l-[4px] border-l-[#8B5A5A]' : 'border-l-[4px] border-l-transparent')
                ]"
              >
                <div class="flex items-center justify-between font-semibold text-[#1C2B2A]">
                  <span class="truncate max-w-[140px]">{{ demo.company }}</span>
                  <span class="text-[10px] px-1.5 py-0.2 rounded uppercase font-semibold" :class="demo.badgeClass">
                    {{ demo.status }}
                  </span>
                </div>
                <div class="text-[11px] text-[#5B6863] mt-0.5 truncate">{{ demo.position }}</div>
              </div>
            </div>

            <!-- Mock Right Detail Column with Smooth Transition -->
            <div class="md:col-span-7 bg-[#FFFFFF] border border-[#C8D0CC] rounded-xl p-4 flex flex-col justify-between min-h-[260px]">
              <transition name="fade-slide" mode="out-in">
                <div :key="activeDemoIdx">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#5B6863] uppercase tracking-wider">{{ demoJobs[activeDemoIdx].company }}</span>
                    <span class="text-[11px] px-2 py-0.5 rounded uppercase font-bold" :class="demoJobs[activeDemoIdx].badgeClass">
                      {{ demoJobs[activeDemoIdx].status }}
                    </span>
                  </div>
                  <h3 class="font-display text-xl font-bold text-[#1C2B2A] mt-1">{{ demoJobs[activeDemoIdx].position }}</h3>
                  
                  <div class="flex flex-wrap items-center gap-2 text-xs text-[#5B6863] mt-2">
                    <span class="bg-[#F3F4F0] px-2 py-0.5 rounded border border-[#C8D0CC]/60">📍 {{ demoJobs[activeDemoIdx].location }}</span>
                    <span class="bg-[#F7EFE6] text-[#B8752F] font-semibold px-2 py-0.5 rounded border border-[#B8752F]/20">💰 {{ demoJobs[activeDemoIdx].salary }}</span>
                  </div>

                  <div class="mt-4 p-3.5 bg-[#F3F4F0] rounded-xl border border-[#C8D0CC]/80">
                    <div class="text-[11px] font-bold text-[#5B6863] uppercase tracking-wider flex items-center gap-1.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-[#B8752F]"></span>
                      Catatan Proses & Tahapan:
                    </div>
                    <p class="text-xs text-[#1C2B2A] mt-1.5 leading-relaxed font-ui">{{ demoJobs[activeDemoIdx].note }}</p>
                  </div>
                </div>
              </transition>

              <div class="flex items-center justify-between pt-4 mt-3 border-t border-[#C8D0CC]">
                <span class="text-[11px] text-[#82918B]">
                  Simulasi interaktif real-time
                </span>
                <button
                  @click="openAuth('register')"
                  class="px-3.5 py-1.5 bg-[#1C2B2A] text-[#F3F4F0] text-xs font-semibold rounded-lg hover:bg-[#2B3E3C] shadow-xs transition-all transform hover:scale-105 cursor-pointer"
                >
                  Gunakan Sekarang ➔
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Horizontal Category Marquee Ticker -->
    <div class="w-full bg-[#ECEEEA] border-y border-[#C8D0CC] py-3 overflow-hidden relative">
      <div class="flex items-center gap-8 whitespace-nowrap animate-ticker text-xs font-semibold text-[#5B6863] uppercase tracking-wider">
        <span>🚀 Tech Startups</span>
        <span>•</span>
        <span>💼 Enterprise Companies</span>
        <span>•</span>
        <span>🌍 Global Remote Opportunities</span>
        <span>•</span>
        <span>🏦 FinTech & Banking</span>
        <span>•</span>
        <span>🎨 Creative & Design Agencies</span>
        <span>•</span>
        <span>⚡ Software Engineering Roles</span>
        <span>•</span>
        <span>🚀 Tech Startups</span>
        <span>•</span>
        <span>💼 Enterprise Companies</span>
        <span>•</span>
        <span>🌍 Global Remote Opportunities</span>
        <span>•</span>
        <span>🏦 FinTech & Banking</span>
        <span>•</span>
        <span>🎨 Creative & Design Agencies</span>
      </div>
    </div>

    <!-- Features Section with Magnetic Hover Cards -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
      <div class="text-center max-w-2xl mx-auto mb-14">
        <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#1C2B2A] tracking-tight">
          Dirancang untuk Ketenangan Pencarian Karir
        </h2>
        <p class="text-xs sm:text-sm text-[#5B6863] mt-2.5">
          Tiga pilar utama yang membedakan Job Tracker dari spreadsheet atau aplikasi pelacak lainnya.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Feature 1 -->
        <div class="group bg-[#F3F4F0] border border-[#C8D0CC] hover:border-[#1C2B2A]/40 rounded-2xl p-7 shadow-xs hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1.5 flex flex-col justify-between">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#B8752F] flex items-center justify-center font-bold text-xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-2xs">
              📋
            </div>
            <h3 class="font-display text-xl font-bold text-[#1C2B2A]">Dual-Panel & Border Status</h3>
            <p class="text-xs sm:text-sm text-[#5B6863] mt-3 leading-relaxed">
              Panel kiri untuk memindai antrean lamaran kerja dengan indikator warna yang jelas, panel kanan untuk membaca catatan recruiter dan checklist interview tanpa gangguan modal pop-up.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#C8D0CC]/60 text-xs font-semibold text-[#B8752F]">
            Efisien & Terstruktur ➔
          </div>
        </div>

        <!-- Feature 2 -->
        <div class="group bg-[#F3F4F0] border border-[#C8D0CC] hover:border-[#1C2B2A]/40 rounded-2xl p-7 shadow-xs hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1.5 flex flex-col justify-between">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#0A66C2] flex items-center justify-center font-bold text-xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-2xs">
              ⚡
            </div>
            <h3 class="font-display text-xl font-bold text-[#1C2B2A]">LinkedIn One-Click Login</h3>
            <p class="text-xs sm:text-sm text-[#5B6863] mt-3 leading-relaxed">
              Masuk secara instan menggunakan akun LinkedIn Anda melalui protokol OAuth 2.0 OpenID Connect resmi. Privasi data terenkripsi dan terisolasi privat.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#C8D0CC]/60 text-xs font-semibold text-[#0A66C2]">
            Aman & Terpercaya ➔
          </div>
        </div>

        <!-- Feature 3 -->
        <div class="group bg-[#F3F4F0] border border-[#C8D0CC] hover:border-[#1C2B2A]/40 rounded-2xl p-7 shadow-xs hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1.5 flex flex-col justify-between">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#1C2B2A] flex items-center justify-center font-bold text-xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-2xs">
              📊
            </div>
            <h3 class="font-display text-xl font-bold text-[#1C2B2A]">Metrik & Ekspor Spreadsheet</h3>
            <p class="text-xs sm:text-sm text-[#5B6863] mt-3 leading-relaxed">
              Ketahui rasio konversi interview dan offering kamu secara transparan. Ekspor seluruh riwayat pencarian kerjamu dalam format CSV kapan saja.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#C8D0CC]/60 text-xs font-semibold text-[#1C2B2A]">
            Analisis Komprehensif ➔
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[#C8D0CC] py-8 text-center text-xs text-[#5B6863] bg-[#ECEEEA]/60">
      <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="font-display font-bold text-[#1C2B2A] text-sm">
          Job Application Tracker
        </div>
        <div>
          Dirancang untuk membantu para talenta meraih karir terbaik.
        </div>
        <div>
          © 2026 Job Tracker. All rights reserved.
        </div>
      </div>
    </footer>

    <!-- Auth Modal with Spring Transition -->
    <AuthModal
      :is-open="isAuthModalOpen"
      :initial-mode="authModalMode"
      @close="isAuthModalOpen = false"
      @auth-success="$emit('auth-success')"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import AuthModal from './AuthModal.vue';

defineEmits<{
  (e: 'auth-success'): void;
}>();

const isAuthModalOpen = ref(false);
const authModalMode = ref<'login' | 'register'>('login');
const activeDemoIdx = ref(1);

const currentRoleIndex = ref(0);
const rotatingRoles = [
  'Frontend Engineer',
  'Fullstack Developer',
  'UI/UX Designer',
  'Backend Go & PHP Dev',
  'Product Manager',
  'Data Scientist & Analyst',
];

let roleInterval: ReturnType<typeof setInterval> | null = null;
let demoInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
  // Rotate hero roles every 2.8s
  roleInterval = setInterval(() => {
    currentRoleIndex.value = (currentRoleIndex.value + 1) % rotatingRoles.length;
  }, 2800);

  // Auto-cycle live simulation preview every 3.5s
  demoInterval = setInterval(() => {
    activeDemoIdx.value = (activeDemoIdx.value + 1) % demoJobs.value.length;
  }, 3500);
});

onUnmounted(() => {
  if (roleInterval) clearInterval(roleInterval);
  if (demoInterval) clearInterval(demoInterval);
});

const demoJobs = ref([
  {
    company: 'CV Studio Desain',
    position: 'UI/UX Designer',
    status: 'applied',
    location: 'Bandung (WFO)',
    salary: 'Rp 8jt - Rp 11jt',
    badgeClass: 'bg-[#ECEEEA] text-[#5B6863]',
    note: 'Kirim portofolio Figma lewat email HR. Menunggu konfirmasi jadwal screening wawancara tahap pertama.'
  },
  {
    company: 'PT Nawa Digital',
    position: 'Frontend Engineer',
    status: 'interview',
    location: 'Jakarta Selatan (Hybrid)',
    salary: 'Rp 14jt - Rp 18jt',
    badgeClass: 'bg-[#F7EFE6] text-[#B8752F]',
    note: 'Interview user lolos! Lanjut ke Technical Live Coding Vue 3 & TypeScript minggu depan dengan VP of Engineering.'
  },
  {
    company: 'Artha Finansial Tech',
    position: 'Full Stack Dev',
    status: 'offer',
    location: 'Jakarta Pusat (Hybrid)',
    salary: 'Rp 15jt - Rp 17jt',
    badgeClass: 'bg-[#F7EFE6] text-[#B8752F]',
    note: 'Offering letter sudah diterima via email! Benefit: BPJS + Asuransi swasta + Hybrid 2 hari seminggu. Batas tanda tangan Jumat ini.'
  },
  {
    company: 'Teknoaplikasi Global',
    position: 'Backend Developer',
    status: 'rejected',
    location: 'Jakarta Barat (WFO)',
    salary: 'Rp 12jt - Rp 15jt',
    badgeClass: 'bg-[#F8EFEF] text-[#8B5A5A]',
    note: 'Feedback recruiter: mencari kandidat dengan pengalaman lebih mendalam di concurrency microservices.'
  }
]);

const openAuth = (mode: 'login' | 'register') => {
  authModalMode.value = mode;
  isAuthModalOpen.value = true;
};

const handleLinkedInLogin = () => {
  window.location.href = '/api/auth/linkedin/redirect';
};
</script>
