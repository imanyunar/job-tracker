<template>
  <div class="min-h-screen bg-[#DCE1DE] text-[#1C2B2A] flex flex-col font-ui selection:bg-[#B8752F] selection:text-white">
    <!-- Navbar -->
    <nav class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center shadow-sm">
          <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <span class="font-display text-xl font-bold tracking-tight text-[#1C2B2A]">
          Job Tracker
        </span>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="openAuth('login')"
          class="px-4 py-2 text-xs sm:text-sm font-medium text-[#1C2B2A] hover:text-[#000000] bg-transparent hover:bg-[#ECEEEA] rounded-lg transition-colors cursor-pointer"
        >
          Masuk
        </button>
        <button
          @click="openAuth('register')"
          class="px-4 py-2 text-xs sm:text-sm font-medium text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] rounded-lg shadow-xs transition-colors cursor-pointer"
        >
          Daftar Gratis
        </button>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 text-center">
      <!-- Animated Pill Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#ECEEEA] border border-[#C8D0CC] text-xs font-medium text-[#5B6863] mb-6 shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-[#B8752F] animate-pulse"></span>
        <span>Modern Career Pipeline & Logbook</span>
      </div>

      <!-- Main Headline -->
      <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#1C2B2A] leading-[1.15] max-w-4xl mx-auto">
        Kelola lamaran kerjamu dengan <span class="italic text-[#B8752F] font-serif font-normal">tenang</span>, terstruktur, dan terarah.
      </h1>

      <p class="mt-6 text-sm sm:text-base lg:text-lg text-[#5B6863] max-w-2xl mx-auto leading-relaxed">
        Tinggalkan spreadsheet rumit. Catat setiap tahapan wawancara, negosiasi estimasi gaji, dan catatan recruiter dalam satu aplikasi yang bersih tanpa distraksi.
      </p>

      <!-- CTA Buttons -->
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
        <button
          @click="openAuth('register')"
          class="w-full sm:w-auto px-6 py-3 text-sm font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 cursor-pointer flex items-center justify-center gap-2"
        >
          Mulai Sekarang — Gratis
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>

        <button
          @click="handleLinkedInLogin"
          class="w-full sm:w-auto px-6 py-3 text-sm font-semibold text-white bg-[#0A66C2] hover:bg-[#004182] active:bg-[#094c92] rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 cursor-pointer flex items-center justify-center gap-2.5"
        >
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
            <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
          </svg>
          Masuk dengan LinkedIn
        </button>
      </div>

      <!-- Animated Interactive Demo Preview Mockup -->
      <div class="mt-14 max-w-4xl mx-auto">
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl shadow-2xl overflow-hidden text-left p-4 sm:p-6 transition-all duration-300">
          <div class="flex items-center justify-between pb-4 border-b border-[#C8D0CC]">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-[#8B5A5A]/50"></span>
              <span class="w-3 h-3 rounded-full bg-[#B8752F]/50"></span>
              <span class="w-3 h-3 rounded-full bg-[#5B6863]/50"></span>
              <span class="ml-2 text-xs font-mono text-[#82918B]">job-tracker.app / demo-pipeline</span>
            </div>
            <span class="text-xs px-2.5 py-0.5 rounded bg-[#ECEEEA] text-[#1C2B2A] font-medium border border-[#C8D0CC]">
              Live Preview
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4">
            <!-- Mock Left Column -->
            <div class="md:col-span-5 bg-[#FFFFFF] border border-[#C8D0CC] rounded-xl p-3 space-y-2">
              <div class="text-[11px] font-semibold uppercase tracking-wider text-[#82918B] px-1">Daftar Lamaran Terkini</div>

              <div
                v-for="(demo, idx) in demoJobs"
                :key="idx"
                @click="activeDemoIdx = idx"
                class="p-2.5 rounded-lg border transition-all duration-200 cursor-pointer text-xs"
                :class="[
                  activeDemoIdx === idx ? 'bg-[#E4E8E3] border-[#1C2B2A]/40 shadow-xs' : 'bg-[#F3F4F0] border-[#C8D0CC]/60 hover:bg-[#ECEEEA]',
                  demo.status === 'interview' || demo.status === 'offer' ? 'border-l-[3.5px] border-l-[#B8752F]' : (demo.status === 'rejected' ? 'border-l-[3.5px] border-l-[#8B5A5A]' : 'border-l-[3.5px] border-l-transparent')
                ]"
              >
                <div class="flex items-center justify-between font-semibold text-[#1C2B2A]">
                  <span>{{ demo.company }}</span>
                  <span class="text-[10px] px-1.5 py-0.2 rounded uppercase" :class="demo.badgeClass">
                    {{ demo.status }}
                  </span>
                </div>
                <div class="text-[11px] text-[#5B6863] mt-0.5">{{ demo.position }}</div>
              </div>
            </div>

            <!-- Mock Right Detail Column -->
            <div class="md:col-span-7 bg-[#FFFFFF] border border-[#C8D0CC] rounded-xl p-4 flex flex-col justify-between">
              <div>
                <span class="text-[11px] font-medium text-[#5B6863] uppercase tracking-wider">{{ demoJobs[activeDemoIdx].company }}</span>
                <h3 class="font-display text-xl font-bold text-[#1C2B2A] mt-0.5">{{ demoJobs[activeDemoIdx].position }}</h3>
                <div class="flex items-center gap-2 text-xs text-[#5B6863] mt-1">
                  <span>📍 {{ demoJobs[activeDemoIdx].location }}</span>
                  <span>•</span>
                  <span>💰 {{ demoJobs[activeDemoIdx].salary }}</span>
                </div>

                <div class="mt-4 p-3 bg-[#F3F4F0] rounded-lg border border-[#C8D0CC]/60">
                  <div class="text-[11px] font-semibold text-[#5B6863] uppercase tracking-wider">Catatan Proses:</div>
                  <p class="text-xs text-[#1C2B2A] mt-1 leading-relaxed">{{ demoJobs[activeDemoIdx].note }}</p>
                </div>
              </div>

              <div class="flex items-center justify-between pt-4 mt-3 border-t border-[#C8D0CC]">
                <span class="text-[11px] text-[#82918B]">Status: <strong class="text-[#1C2B2A] uppercase">{{ demoJobs[activeDemoIdx].status }}</strong></span>
                <button
                  @click="openAuth('register')"
                  class="px-3 py-1.5 bg-[#1C2B2A] text-[#F3F4F0] text-xs font-semibold rounded-md hover:bg-[#2B3E3C] transition-colors cursor-pointer"
                >
                  Coba Buat Tracker ➔
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-[#C8D0CC]">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1C2B2A]">
          Fitur yang Dirancang Khusus untuk Job Seeker
        </h2>
        <p class="text-xs sm:text-sm text-[#5B6863] mt-2">
          Bukan template SaaS generik. Semua elemen didesain untuk kenyamanan pemakaian jangka panjang.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Feature 1 -->
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="w-10 h-10 rounded-xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#B8752F] flex items-center justify-center font-bold text-lg mb-4">
            📋
          </div>
          <h3 class="font-display text-lg font-bold text-[#1C2B2A]">Dual-Panel & Border Status</h3>
          <p class="text-xs sm:text-sm text-[#5B6863] mt-2 leading-relaxed">
            Scan daftar lamaran di panel kiri dengan indikator garis warna progresif, dan baca catatan lengkap di panel kanan tanpa pop-up yang mengganggu.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="w-10 h-10 rounded-xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#0A66C2] flex items-center justify-center font-bold text-lg mb-4">
            ⚡
          </div>
          <h3 class="font-display text-lg font-bold text-[#1C2B2A]">Login Kilat dengan LinkedIn</h3>
          <p class="text-xs sm:text-sm text-[#5B6863] mt-2 leading-relaxed">
            Masuk dengan satu kali klik menggunakan akun LinkedIn resmi. Data pengguna aman, terenkripsi, dan terisolasi secara privat.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="w-10 h-10 rounded-xl bg-[#ECEEEA] border border-[#C8D0CC] text-[#1C2B2A] flex items-center justify-center font-bold text-lg mb-4">
            📊
          </div>
          <h3 class="font-display text-lg font-bold text-[#1C2B2A]">Statistik & Ekspor CSV</h3>
          <p class="text-xs sm:text-sm text-[#5B6863] mt-2 leading-relaxed">
            Pantau rasio konversi lolos interview dan unduh riwayat pencarian kerjamu ke dalam file spreadsheet CSV kapan saja.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[#C8D0CC] py-8 text-center text-xs text-[#5B6863]">
      <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="font-display font-bold text-[#1C2B2A]">
          Job Application Tracker
        </div>
        <div>
          Dibuat dengan ❤️ untuk para pejuang karir.
        </div>
        <div>
          © 2026 Job Tracker. All rights reserved.
        </div>
      </div>
    </footer>

    <!-- Auth Modal -->
    <AuthModal
      :is-open="isAuthModalOpen"
      :initial-mode="authModalMode"
      @close="isAuthModalOpen = false"
      @auth-success="$emit('auth-success')"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AuthModal from './AuthModal.vue';

defineEmits<{
  (e: 'auth-success'): void;
}>();

const isAuthModalOpen = ref(false);
const authModalMode = ref<'login' | 'register'>('login');
const activeDemoIdx = ref(1);

const demoJobs = ref([
  {
    company: 'CV Studio Desain',
    position: 'UI/UX Designer',
    status: 'applied',
    location: 'Bandung (WFO)',
    salary: 'Rp 8.000.000 - Rp 11.000.000',
    badgeClass: 'bg-[#ECEEEA] text-[#5B6863]',
    note: 'Kirim portofolio Figma lewat email HR. Menunggu konfirmasi jadwal screening.'
  },
  {
    company: 'PT Nawa Digital',
    position: 'Frontend Engineer',
    status: 'interview',
    location: 'Jakarta Selatan (Hybrid)',
    salary: 'Rp 14.000.000 - Rp 18.000.000',
    badgeClass: 'bg-[#F7EFE6] text-[#B8752F]',
    note: 'Interview user lolos! Jadwal interview kedua tanggal 20 Agustus dengan VP of Engineering.'
  },
  {
    company: 'Artha Finansial Tech',
    position: 'Full Stack Dev',
    status: 'offer',
    location: 'Jakarta Pusat (Hybrid)',
    salary: 'Rp 15.000.000 - Rp 17.000.000',
    badgeClass: 'bg-[#F7EFE6] text-[#B8752F]',
    note: 'Offering letter sudah diterima via email. Gaji 16jt + asuransi swasta. Batas konfirmasi akhir pekan.'
  },
  {
    company: 'Teknoaplikasi Nusantara',
    position: 'Backend Developer',
    status: 'rejected',
    location: 'Jakarta Barat (WFO)',
    salary: 'Rp 12.000.000 - Rp 15.000.000',
    badgeClass: 'bg-[#F8EFEF] text-[#8B5A5A]',
    note: 'Feedback: mencari kandidat dengan pengalaman lebih mendalam di concurrency microservices.'
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
