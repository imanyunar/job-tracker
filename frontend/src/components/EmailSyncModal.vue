<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#1C2B2A]/60 backdrop-blur-xs overflow-y-auto"
        @click.self="closeModal"
      >
        <div
          class="relative w-full max-w-2xl bg-[#F3F4F0] border border-[#C8D0CC] rounded-2xl shadow-2xl overflow-hidden my-8 transform transition-all"
        >
          <!-- Header Bar -->
          <div class="flex items-center justify-between px-6 py-4 bg-[#FFFFFF] border-b border-[#C8D0CC]">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center shadow-xs">
                <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-display text-lg font-bold text-[#1C2B2A]">
                    Email Scraping & Status Sync
                  </h3>
                  <span class="inline-flex items-center gap-1 text-[10px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-full bg-[#B8752F]/15 text-[#B8752F]">
                    <span>✨ AI Assistant</span>
                  </span>
                </div>
                <p class="text-xs text-[#5B6863]">
                  Deteksi email wawancara, tes, offering, atau penolakan lalu perbarui status otomatis.
                </p>
              </div>
            </div>

            <button
              @click="closeModal"
              class="text-[#5B6863] hover:text-[#1C2B2A] p-1.5 rounded-lg hover:bg-[#ECEEEA] transition-colors cursor-pointer"
              title="Tutup Modal"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Tabs Navigation -->
          <div class="flex border-b border-[#C8D0CC] bg-[#ECEEEA] px-6 pt-2 gap-2 text-xs font-semibold">
            <button
              @click="activeTab = 'paste'"
              class="pb-2.5 px-3 border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeTab === 'paste' ? 'border-[#1C2B2A] text-[#1C2B2A]' : 'border-transparent text-[#5B6863] hover:text-[#1C2B2A]'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Scan & Paste Email (Instan)
            </button>

            <button
              @click="activeTab = 'gmail'"
              class="pb-2.5 px-3 border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeTab === 'gmail' ? 'border-[#1C2B2A] text-[#1C2B2A]' : 'border-transparent text-[#5B6863] hover:text-[#1C2B2A]'"
            >
              <svg class="w-3.5 h-3.5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
              Integrasi Gmail (OAuth)
              <span
                v-if="gmailStatus?.is_connected"
                class="w-2 h-2 rounded-full bg-[#2E6F40]"
                title="Terhubung"
              ></span>
            </button>
          </div>

          <!-- Body Content -->
          <div class="p-6 max-h-[75vh] overflow-y-auto">
            <!-- TAB 1: PASTE EMAIL -->
            <div v-if="activeTab === 'paste'" class="space-y-4">
              <!-- Target Application Selector -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
                    Tautkan ke Lamaran
                  </label>
                  <select
                    v-model="selectedAppId"
                    class="w-full text-xs px-3 py-2 bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] focus:outline-none focus:ring-1 focus:ring-[#1C2B2A]"
                  >
                    <option :value="null">✨ Otomatis Deteksi dari Isi/Pengirim Email</option>
                    <option v-for="app in applications" :key="app.id" :value="app.id">
                      {{ app.company_name }} - {{ app.position }} ({{ app.status }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
                    Pengirim Email / Recruiter (Opsional)
                  </label>
                  <input
                    v-model="emailSender"
                    type="text"
                    placeholder="Contoh: talent@shopee.com atau HR BCA"
                    class="w-full text-xs px-3 py-2 bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#5B6863]/60 focus:outline-none focus:ring-1 focus:ring-[#1C2B2A]"
                  />
                </div>
              </div>

              <!-- Subject Input -->
              <div>
                <label class="block text-xs font-semibold text-[#1C2B2A] mb-1">
                  Subjek Email (Opsional)
                </label>
                <input
                  v-model="emailSubject"
                  type="text"
                  placeholder="Contoh: [Shopee] Undangan Wawancara Teknis - Frontend Developer"
                  class="w-full text-xs px-3 py-2 bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] placeholder-[#5B6863]/60 focus:outline-none focus:ring-1 focus:ring-[#1C2B2A]"
                />
              </div>

              <!-- Content Input & Presets -->
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <label class="text-xs font-semibold text-[#1C2B2A]">
                    Isi Email dari Recruiter / Perusahaan
                  </label>
                  <span class="text-[11px] text-[#5B6863]">
                    Tempel teks email di bawah ini
                  </span>
                </div>

                <textarea
                  v-model="emailContent"
                  rows="5"
                  placeholder="Salin dan tempel isi email wawancara, link tes, offer letter, atau pengumuman hasil seleksi di sini..."
                  class="w-full text-xs px-3.5 py-2.5 bg-white border border-[#C8D0CC] rounded-xl text-[#1C2B2A] placeholder-[#5B6863]/60 focus:outline-none focus:ring-1 focus:ring-[#1C2B2A] transition-all font-sans leading-relaxed resize-y"
                ></textarea>

                <!-- Quick Presets for Instant Testing -->
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                  <span class="text-[11px] font-medium text-[#5B6863] mr-1">Coba contoh:</span>
                  <button
                    v-for="preset in presets"
                    :key="preset.id"
                    type="button"
                    @click="applyPreset(preset)"
                    class="text-[11px] px-2.5 py-1 rounded-md bg-[#FFFFFF] border border-[#C8D0CC] hover:bg-[#1C2B2A] hover:text-[#F3F4F0] hover:border-[#1C2B2A] transition-colors cursor-pointer text-[#1C2B2A]"
                  >
                    {{ preset.label }}
                  </button>
                </div>
              </div>

              <!-- Analyze Button -->
              <div class="pt-1">
                <button
                  type="button"
                  @click="runAnalysis"
                  :disabled="isAnalyzing || !emailContent.trim()"
                  class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-xs transition-all cursor-pointer"
                >
                  <svg
                    v-if="isAnalyzing"
                    class="w-4 h-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>{{ isAnalyzing ? 'Menganalisis Konten Email...' : 'Analisis Email Cerdas' }}</span>
                </button>
              </div>

              <!-- Analysis Results Card -->
              <div
                v-if="analysisResult"
                class="mt-4 p-4 rounded-xl border border-[#C8D0CC] bg-[#FFFFFF] shadow-sm space-y-3.5 transition-all"
              >
                <div class="flex items-center justify-between pb-2 border-b border-[#ECEEEA]">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#1C2B2A]">Hasil Deteksi Email</span>
                    <span
                      class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize"
                      :class="getConfidenceBadgeClass(analysisResult.confidence)"
                    >
                      Akurasi {{ analysisResult.confidence }} ({{ analysisResult.confidence_score }} pts)
                    </span>
                  </div>

                  <span
                    v-if="analysisResult.matched_application"
                    class="text-[11px] font-medium text-[#2E6F40] flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Cocok dengan #{{ analysisResult.matched_application.id }} ({{ analysisResult.matched_application.company_name }})
                  </span>
                  <span v-else class="text-[11px] font-medium text-[#B8752F]">
                    Perusahaan Terdeteksi: {{ analysisResult.detected_company || 'Tidak spesifik' }}
                  </span>
                </div>

                <!-- Status Transition Flow -->
                <div class="flex items-center gap-3 p-3 bg-[#F3F4F0] rounded-xl border border-[#C8D0CC]/60">
                  <div class="flex-1 text-center">
                    <div class="text-[10px] text-[#5B6863] uppercase font-semibold">Status Saat Ini</div>
                    <div class="mt-0.5 inline-block px-2.5 py-1 text-xs font-bold rounded-lg uppercase" :class="getStatusBadgeClass(currentAppStatus)">
                      {{ currentAppStatus }}
                    </div>
                  </div>

                  <div class="text-[#5B6863]">
                    <svg class="w-5 h-5 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                  </div>

                  <div class="flex-1 text-center">
                    <div class="text-[10px] text-[#5B6863] uppercase font-semibold">Status Baru Rekomendasi</div>
                    <div class="mt-0.5 inline-block px-2.5 py-1 text-xs font-bold rounded-lg uppercase shadow-2xs" :class="getStatusBadgeClass(analysisResult.status)">
                      {{ analysisResult.status }} ({{ analysisResult.status_label }})
                    </div>
                  </div>
                </div>

                <!-- Key details extracted -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                  <div v-if="analysisResult.meeting_datetime" class="p-2.5 bg-[#ECEEEA]/60 rounded-lg">
                    <div class="text-[10px] text-[#5B6863] font-semibold">📅 Jadwal Terdeteksi</div>
                    <div class="font-bold text-[#1C2B2A] mt-0.5">{{ analysisResult.meeting_datetime }}</div>
                  </div>

                  <div v-if="analysisResult.meeting_link" class="p-2.5 bg-[#ECEEEA]/60 rounded-lg">
                    <div class="text-[10px] text-[#5B6863] font-semibold">🔗 Link Pertemuan Online</div>
                    <a
                      :href="analysisResult.meeting_link"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-[#2E6F40] underline font-medium truncate block mt-0.5"
                    >
                      {{ analysisResult.meeting_link }}
                    </a>
                  </div>
                </div>

                <!-- Trigger keywords -->
                <div v-if="analysisResult.detected_keywords?.length" class="text-xs">
                  <span class="text-[11px] text-[#5B6863] font-semibold block mb-1">Pemicu Deteksi:</span>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="(kw, idx) in analysisResult.detected_keywords"
                      :key="idx"
                      class="px-2 py-0.5 text-[10px] bg-[#ECEEEA] text-[#1C2B2A] rounded-md font-mono"
                    >
                      {{ kw }}
                    </span>
                  </div>
                </div>

                <!-- Suggested Note Editor -->
                <div>
                  <label class="block text-[11px] font-semibold text-[#1C2B2A] mb-1">
                    Catatan yang akan ditambahkan ke logbook:
                  </label>
                  <textarea
                    v-model="customNote"
                    rows="2"
                    class="w-full text-xs p-2.5 bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] focus:outline-none focus:ring-1 focus:ring-[#1C2B2A]"
                  ></textarea>
                </div>

                <!-- Target Job Confirmation Dropdown if not matched -->
                <div v-if="!analysisResult.matched_application">
                  <label class="block text-xs font-semibold text-[#8B5A5A] mb-1">
                    ⚠️ Pilih lamaran yang ingin diperbarui:
                  </label>
                  <select
                    v-model="fallbackAppId"
                    class="w-full text-xs px-3 py-2 bg-white border border-[#C8D0CC] rounded-lg text-[#1C2B2A] focus:ring-1 focus:ring-[#1C2B2A]"
                  >
                    <option :value="null">-- Pilih Salah Satu Lamaran Pekerjaan --</option>
                    <option v-for="app in applications" :key="app.id" :value="app.id">
                      {{ app.company_name }} - {{ app.position }} (Saat ini: {{ app.status }})
                    </option>
                  </select>
                </div>

                <!-- Apply Update Button -->
                <div class="pt-2">
                  <button
                    type="button"
                    @click="applyStatusUpdate"
                    :disabled="isApplying || (!analysisResult.matched_application && !fallbackAppId)"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-[#2E6F40] hover:bg-[#235832] disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-xs transition-colors cursor-pointer"
                  >
                    <svg
                      v-if="isApplying"
                      class="w-4 h-4 animate-spin"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Perbarui Status Lamaran Sekarang</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- TAB 2: GMAIL INTEGRATION -->
            <div v-else class="space-y-4">
              <!-- Connection Card -->
              <div class="p-5 rounded-2xl bg-white border border-[#C8D0CC] shadow-xs space-y-4">
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#F3F4F0] border border-[#C8D0CC] flex items-center justify-center">
                      <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.272H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
                      </svg>
                    </div>
                    <div>
                      <h4 class="font-bold text-sm text-[#1C2B2A]">
                        Google Workspace / Gmail Sync
                      </h4>
                      <p class="text-xs text-[#5B6863]">
                        Sinkronisasi otomatis email lamaran kerja langsung dari inbox Gmail Anda.
                      </p>
                    </div>
                  </div>

                  <span
                    v-if="gmailStatus?.is_connected"
                    class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-[#2E6F40]/15 text-[#2E6F40] flex items-center gap-1.5"
                  >
                    <span class="w-2 h-2 rounded-full bg-[#2E6F40]"></span>
                    Terhubung
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-[#5B6863]/15 text-[#5B6863]"
                  >
                    Belum Terhubung
                  </span>
                </div>

                <!-- If Connected -->
                <div v-if="gmailStatus?.is_connected" class="space-y-3 pt-2 border-t border-[#ECEEEA]">
                  <div class="flex items-center justify-between text-xs">
                    <span class="text-[#5B6863]">Akun Google:</span>
                    <span class="font-semibold text-[#1C2B2A]">{{ gmailStatus.google_email }}</span>
                  </div>
                  <div class="flex items-center justify-between text-xs">
                    <span class="text-[#5B6863]">Terakhir Dipindai:</span>
                    <span class="font-semibold text-[#1C2B2A]">{{ gmailStatus.last_synced_at || 'Belum pernah' }}</span>
                  </div>

                  <div class="flex items-center gap-2 pt-2">
                    <button
                      type="button"
                      @click="triggerGmailScan"
                      :disabled="isScanningGmail"
                      class="flex-1 inline-flex items-center justify-center gap-2 px-3.5 py-2 text-xs font-bold text-white bg-[#1C2B2A] hover:bg-[#2B3E3C] disabled:opacity-50 rounded-xl transition-colors cursor-pointer"
                    >
                      <svg
                        v-if="isScanningGmail"
                        class="w-4 h-4 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <svg v-else class="w-4 h-4 text-[#B8752F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                      <span>{{ isScanningGmail ? 'Memindai Inbox Gmail...' : 'Scan Inbox Sekarang' }}</span>
                    </button>

                    <button
                      type="button"
                      @click="disconnectGmail"
                      class="px-3 py-2 text-xs font-semibold text-[#8B5A5A] hover:bg-[#FBE8E8] rounded-xl border border-[#E2B8B8] transition-colors cursor-pointer"
                    >
                      Putuskan
                    </button>
                  </div>
                </div>

                <!-- If Not Connected -->
                <div v-else class="space-y-3 pt-2 border-t border-[#ECEEEA]">
                  <p class="text-xs text-[#5B6863] leading-relaxed">
                    Dengan menghubungkan Gmail, Job Tracker dapat mendeteksi email masuk dari HRD dan memperbarui status lamaran secara otomatis. Kami hanya meminta izin baca (<code class="font-mono text-[11px] bg-[#ECEEEA] px-1 py-0.5 rounded">gmail.readonly</code>) untuk email terkait rekrutmen.
                  </p>

                  <div v-if="!gmailStatus?.has_client_config" class="p-3 bg-[#FEF3C7] border border-[#FDE68A] rounded-xl text-xs text-[#92400E] space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                      </svg>
                      Petunjuk Konfigurasi Google OAuth
                    </div>
                    <p class="text-[11px] leading-relaxed text-[#78350F]">
                      Untuk menghubungkan Google secara langsung, isi parameter <code class="font-mono font-bold">GOOGLE_CLIENT_ID</code> dan <code class="font-mono font-bold">GOOGLE_CLIENT_SECRET</code> di file <code class="font-mono font-bold">backend/.env</code>. Anda tetap dapat menggunakan tab <strong>"Scan & Paste Email"</strong> secara instan tanpa konfigurasi apa pun!
                    </p>
                  </div>

                  <a
                    :href="googleRedirectUrl"
                    class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 text-xs font-bold text-[#1C2B2A] bg-white hover:bg-[#ECEEEA] border border-[#C8D0CC] rounded-xl shadow-xs transition-colors cursor-pointer"
                  >
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                      <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3h3.88c2.27-2.09 3.66-5.17 3.66-9.09z"/>
                      <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.28v3.09C3.26 21.3 7.37 24 12 24z"/>
                      <path fill="#FBBC05" d="M5.28 14.32c-.25-.72-.38-1.49-.38-2.32s.13-1.6.38-2.32V6.59H1.28C.46 8.22 0 10.06 0 12s.46 3.78 1.28 5.41l4-3.09z"/>
                      <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.37 0 3.26 2.7 1.28 6.59l4 3.09c.95-2.83 3.6-4.93 6.72-4.93z"/>
                    </svg>
                    Hubungkan Akun Google
                  </a>
                </div>
              </div>

              <!-- Gmail Scan Results List -->
              <div v-if="scanResults?.length" class="space-y-3">
                <h5 class="text-xs font-bold text-[#1C2B2A] flex items-center gap-2">
                  <span>Hasil Pemindaian Inbox ({{ scanResults.length }} Pesan Terdeteksi)</span>
                </h5>

                <div
                  v-for="item in scanResults"
                  :key="item.id"
                  class="p-3.5 bg-white border border-[#C8D0CC] rounded-xl space-y-2 text-xs"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <div class="font-bold text-[#1C2B2A]">{{ item.subject || '(Tanpa Subjek)' }}</div>
                      <div class="text-[11px] text-[#5B6863] mt-0.5">Pengirim: {{ item.sender }}</div>
                    </div>
                    <span
                      class="px-2 py-0.5 text-[10px] font-bold rounded uppercase shrink-0"
                      :class="getStatusBadgeClass(item.analysis.status)"
                    >
                      {{ item.analysis.status }}
                    </span>
                  </div>

                  <p class="text-[11px] text-[#5B6863] italic line-clamp-2 bg-[#F3F4F0] p-2 rounded-lg">
                    {{ item.analysis.excerpt }}
                  </p>

                  <div class="flex items-center justify-between pt-1">
                    <span v-if="item.analysis.matched_application" class="text-[11px] font-medium text-[#2E6F40]">
                      Perusahaan: {{ item.analysis.matched_application.company_name }}
                    </span>
                    <span v-else class="text-[11px] text-[#B8752F]">
                      Perusahaan: {{ item.analysis.detected_company || 'Tidak terdeteksi' }}
                    </span>

                    <button
                      v-if="item.analysis.matched_application"
                      type="button"
                      @click="quickApplyGmailItem(item)"
                      class="px-3 py-1 bg-[#2E6F40] hover:bg-[#235832] text-white font-semibold text-[11px] rounded-lg transition-colors cursor-pointer"
                    >
                      Update ke {{ item.analysis.status }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-3 bg-[#FFFFFF] border-t border-[#C8D0CC] flex items-center justify-between text-xs text-[#5B6863]">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-[#2E6F40]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span>Deteksi Cerdas Bilingual (Indonesia & English)</span>
            </div>
            <button
              type="button"
              @click="closeModal"
              class="px-3.5 py-1.5 rounded-lg border border-[#C8D0CC] text-[#1C2B2A] hover:bg-[#ECEEEA] font-medium transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import type { JobApplication, JobStatus, EmailParseResult, GmailSyncStatus, GmailScanResultItem } from '../types/job';
import { emailSyncApi } from '../services/api';

const props = defineProps<{
  isOpen: boolean;
  applications: JobApplication[];
  preselectedAppId?: number | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'applied', payload: { application: JobApplication; message: string }): void;
  (e: 'show-toast', message: string, type: 'success' | 'error' | 'info'): void;
}>();

const activeTab = ref<'paste' | 'gmail'>('paste');
const selectedAppId = ref<number | null>(null);
const emailSubject = ref<string>('');
const emailSender = ref<string>('');
const emailContent = ref<string>('');
const isAnalyzing = ref<boolean>(false);
const isApplying = ref<boolean>(false);
const analysisResult = ref<EmailParseResult | null>(null);
const customNote = ref<string>('');
const fallbackAppId = ref<number | null>(null);

// Gmail states
const gmailStatus = ref<GmailSyncStatus | null>(null);
const isScanningGmail = ref<boolean>(false);
const scanResults = ref<GmailScanResultItem[]>([]);
const googleRedirectUrl = computed(() => emailSyncApi.getGoogleRedirectUrl());

// Presets for testing
const presets = [
  {
    id: 'interview_id',
    label: '📅 Undangan Interview (Meet)',
    subject: '[Shopee] Undangan Wawancara Teknis - Frontend Developer',
    sender: 'recruitment@shopee.com',
    content: `Halo Iman Yunar,

Terima kasih atas minat Anda melamar posisi Frontend Developer di Shopee.
Berdasarkan hasil seleksi berkas Anda, kami mengundang Anda untuk menghadiri sesi Wawancara Teknis (User Interview) yang akan dilaksanakan pada:

Hari/Tanggal: Kamis, 10 September 2026 pukul 14:00 WIB
Tautan Wawancara: https://meet.google.com/abc-defg-hij

Mohon persiapkan koneksi internet yang stabil dan konfirmasi kehadiran Anda.
Salam,
Talent Acquisition Shopee`,
  },
  {
    id: 'screening_id',
    label: '📝 Online Test / Psikotes',
    subject: 'Undangan Online Assessment - PT Bank Central Asia',
    sender: 'talent@bca.co.id',
    content: `Selamat! Anda dinyatakan lolos tahap seleksi administrasi berkas.
Tahap selanjutnya adalah pengerjaan Online Assessment (Tes Kemampuan Dasar & Tes Psikotes).
Batas waktu pengerjaan tes adalah 12 September 2026.
Tautan tes telah dikirimkan ke portal asesmen Anda.`,
  },
  {
    id: 'rejection_id',
    label: '✉️ Rejection Letter',
    subject: 'Status Lamaran Pekerjaan di Google',
    sender: 'no-reply@google.com',
    content: `Dear Candidate,

Thank you for your interest in the Software Engineer position.
We received applications from many qualified candidates, and after careful consideration, we have chosen to move forward with other applicants.

We regret to inform you that you were not selected for this role. We will keep your resume on file for future opportunities.
Best wishes in your job search.`,
  },
  {
    id: 'offer_id',
    label: '🎉 Job Offering Letter',
    subject: 'Job Offer: Full Stack Engineer at GoTo',
    sender: 'talent@goto.com',
    content: `Dear Iman,

We are pleased to offer you the position of Full Stack Engineer at GoTo.
Attached is the formal offering letter detailing your compensation and benefits package.
Please review and sign the employment agreement by next Monday.
Welcome to the team!`,
  },
];

const currentAppStatus = computed<JobStatus>(() => {
  if (analysisResult.value?.matched_application) {
    return analysisResult.value.matched_application.status;
  }
  if (selectedAppId.value) {
    const found = props.applications.find((a) => a.id === selectedAppId.value);
    if (found) return found.status;
  }
  if (fallbackAppId.value) {
    const found = props.applications.find((a) => a.id === fallbackAppId.value);
    if (found) return found.status;
  }
  return 'applied';
});

watch(
  () => props.preselectedAppId,
  (newVal) => {
    if (newVal) {
      selectedAppId.value = newVal;
      const target = props.applications.find((a) => a.id === newVal);
      if (target) {
        emailSubject.value = `[${target.company_name}] Update Status`;
      }
    }
  },
  { immediate: true }
);

watch(
  () => props.isOpen,
  async (isOpen) => {
    if (isOpen) {
      loadGmailStatus();
    }
  }
);

const applyPreset = (preset: typeof presets[0]) => {
  emailSubject.value = preset.subject;
  emailSender.value = preset.sender;
  emailContent.value = preset.content;
  analysisResult.value = null;
  runAnalysis();
};

const runAnalysis = async () => {
  if (!emailContent.value.trim()) return;

  isAnalyzing.value = true;
  analysisResult.value = null;

  try {
    const res = await emailSyncApi.parseEmail({
      content: emailContent.value,
      subject: emailSubject.value || undefined,
      sender: emailSender.value || undefined,
      application_id: selectedAppId.value || undefined,
    });

    if (res.data) {
      analysisResult.value = res.data;
      customNote.value = res.data.suggested_note || '';

      if (res.data.matched_application) {
        fallbackAppId.value = res.data.matched_application.id;
      }
    }
  } catch (err: any) {
    emit('show-toast', err.response?.data?.message || 'Gagal menganalisis email.', 'error');
  } finally {
    isAnalyzing.value = false;
  }
};

const applyStatusUpdate = async () => {
  if (!analysisResult.value) return;

  const targetId = analysisResult.value.matched_application?.id || fallbackAppId.value || selectedAppId.value;
  if (!targetId) {
    emit('show-toast', 'Pilih lamaran yang ingin diperbarui terlebih dahulu.', 'error');
    return;
  }

  isApplying.value = true;

  try {
    const res = await emailSyncApi.applyUpdate({
      application_id: targetId,
      status: analysisResult.value.status,
      notes: customNote.value,
      append_note: true,
    });

    if (res.data?.application) {
      emit('applied', {
        application: res.data.application,
        message: res.message || 'Status lamaran berhasil diperbarui.',
      });
      emit('show-toast', `Status berhasil diubah ke ${analysisResult.value.status_label}!`, 'success');
      closeModal();
    }
  } catch (err: any) {
    emit('show-toast', err.response?.data?.message || 'Gagal memperbarui status lamaran.', 'error');
  } finally {
    isApplying.value = false;
  }
};

const loadGmailStatus = async () => {
  try {
    const res = await emailSyncApi.getGmailStatus();
    if (res.data) {
      gmailStatus.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load Gmail status:', err);
  }
};

const triggerGmailScan = async () => {
  isScanningGmail.value = true;
  scanResults.value = [];

  try {
    const res = await emailSyncApi.scanGmail();
    if (res.data) {
      scanResults.value = res.data.results || [];
      emit('show-toast', `Berhasil memindai ${res.data.scanned_count} email dari Gmail!`, 'success');
      await loadGmailStatus();
    }
  } catch (err: any) {
    emit('show-toast', err.response?.data?.message || 'Gagal memindai inbox Gmail.', 'error');
  } finally {
    isScanningGmail.value = false;
  }
};

const quickApplyGmailItem = async (item: GmailScanResultItem) => {
  if (!item.analysis.matched_application) return;

  try {
    const res = await emailSyncApi.applyUpdate({
      application_id: item.analysis.matched_application.id,
      status: item.analysis.status,
      notes: item.analysis.suggested_note,
      append_note: true,
    });

    if (res.data?.application) {
      emit('applied', {
        application: res.data.application,
        message: res.message,
      });
      emit('show-toast', `Status ${item.analysis.matched_application.company_name} diubah ke ${item.analysis.status}!`, 'success');
    }
  } catch (err: any) {
    emit('show-toast', err.response?.data?.message || 'Gagal memperbarui status.', 'error');
  }
};

const disconnectGmail = async () => {
  if (!confirm('Apakah Anda yakin ingin memutuskan koneksi akun Gmail?')) return;

  try {
    await emailSyncApi.disconnectGmail();
    emit('show-toast', 'Koneksi akun Gmail berhasil diputuskan.', 'info');
    await loadGmailStatus();
  } catch (err: any) {
    emit('show-toast', err.response?.data?.message || 'Gagal memutuskan koneksi.', 'error');
  }
};

const closeModal = () => {
  emit('close');
};

const getStatusBadgeClass = (status: string): string => {
  switch (status) {
    case 'applied':
      return 'bg-[#2B4C7E]/15 text-[#2B4C7E] border border-[#2B4C7E]/30';
    case 'screening':
      return 'bg-[#B8752F]/15 text-[#B8752F] border border-[#B8752F]/30';
    case 'interview':
      return 'bg-[#6D3B84]/15 text-[#6D3B84] border border-[#6D3B84]/30';
    case 'offer':
      return 'bg-[#2E6F40]/15 text-[#2E6F40] border border-[#2E6F40]/30';
    case 'rejected':
      return 'bg-[#8B5A5A]/15 text-[#8B5A5A] border border-[#8B5A5A]/30';
    case 'accepted':
      return 'bg-[#1C2B2A] text-[#F3F4F0] border border-[#1C2B2A]';
    default:
      return 'bg-[#5B6863]/15 text-[#5B6863]';
  }
};

const getConfidenceBadgeClass = (confidence: string): string => {
  switch (confidence) {
    case 'high':
      return 'bg-[#2E6F40]/15 text-[#2E6F40]';
    case 'medium':
      return 'bg-[#B8752F]/15 text-[#B8752F]';
    default:
      return 'bg-[#5B6863]/15 text-[#5B6863]';
  }
};

onMounted(() => {
  // Check if redirected from Google OAuth callback
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('gmail_connected')) {
    activeTab.value = 'gmail';
    emit('show-toast', 'Akun Google berhasil dihubungkan!', 'success');
    window.history.replaceState({}, document.title, window.location.pathname);
  } else if (urlParams.get('gmail_error')) {
    activeTab.value = 'gmail';
    emit('show-toast', urlParams.get('gmail_error') || 'Gagal menghubungkan akun Google.', 'error');
    window.history.replaceState({}, document.title, window.location.pathname);
  }
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
