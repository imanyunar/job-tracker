<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailJobStatusParserService
{
    /**
     * Regex patterns for meeting links.
     */
    protected array $meetingLinkPatterns = [
        'google_meet' => '/https?:\/\/meet\.google\.com\/[a-z]{3}-[a-z]{4}-[a-z]{3}/i',
        'zoom' => '/https?:\/\/[a-z0-9\.\-_]*zoom\.us\/[jw]\/[a-zA-Z0-9\?\=\&\-_]+/i',
        'ms_teams' => '/https?:\/\/teams\.microsoft\.com\/l\/meetup-join\/[^\s<>"]+/i',
        'generic_meet' => '/https?:\/\/(?:meet|call|teleconference)\.[^\s<>"]+/i',
    ];

    /**
     * Parse raw email content and detect job application status change.
     */
    public function parseEmail(
        string $content,
        ?string $subject = null,
        ?string $sender = null,
        ?User $user = null,
        ?int $explicitApplicationId = null
    ): array {
        $cleanContent = $this->normalizeText($content);
        $cleanSubject = $subject ? $this->normalizeText($subject) : '';
        $cleanSender = $sender ? trim($sender) : '';
        $combinedText = trim($cleanSubject . "\n\n" . $cleanContent);

        // 1. Extract meeting details if any
        $meetingLink = $this->extractMeetingLink($combinedText);
        $meetingDateTime = $this->extractDateTime($combinedText);

        // 2. Classify status intent & confidence
        $statusClassification = $this->classifyStatus($combinedText, !empty($meetingLink));

        // 3. Match with user's job applications
        $matchedApplication = null;
        $matchedConfidence = 'low';
        $detectedCompany = $this->extractCompanyName($cleanSubject, $cleanSender, $cleanContent);

        if ($user) {
            $matchedApplication = $this->findMatchingApplication(
                $user,
                $cleanSubject,
                $cleanSender,
                $cleanContent,
                $explicitApplicationId,
                $matchedConfidence
            );

            if ($matchedApplication && empty($detectedCompany)) {
                $detectedCompany = $matchedApplication->company_name;
            }
        }

        // 4. Generate structured notes and action recommendation
        $newStatus = $statusClassification['status'];
        $currentStatus = $matchedApplication?->status ?? 'applied';
        $statusChanged = $matchedApplication ? ($matchedApplication->status !== $newStatus) : true;

        $suggestedNote = $this->generateSuggestedNote(
            $newStatus,
            $cleanSubject,
            $meetingDateTime,
            $meetingLink,
            $statusClassification['key_phrases']
        );

        return [
            'success' => true,
            'status' => $newStatus,
            'confidence' => $statusClassification['confidence'],
            'confidence_score' => $statusClassification['score'],
            'status_label' => $this->getStatusLabel($newStatus),
            'current_status' => $currentStatus,
            'status_changed' => $statusChanged,
            'detected_company' => $detectedCompany,
            'matched_application' => $matchedApplication ? [
                'id' => $matchedApplication->id,
                'company_name' => $matchedApplication->company_name,
                'position' => $matchedApplication->position,
                'status' => $matchedApplication->status,
                'applied_date' => $matchedApplication->applied_date?->format('Y-m-d'),
            ] : null,
            'matched_confidence' => $matchedConfidence,
            'meeting_link' => $meetingLink,
            'meeting_datetime' => $meetingDateTime,
            'detected_keywords' => $statusClassification['matched_keywords'],
            'excerpt' => $this->createExcerpt($cleanContent, $statusClassification['matched_keywords']),
            'suggested_note' => $suggestedNote,
        ];
    }

    /**
     * Normalize email text by stripping tags, decodes special entities, cleans excessive spacing.
     */
    public function normalizeText(string $text): string
    {
        // Remove style and script blocks
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $text);
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);

        // Convert common block tags to newlines
        $text = preg_replace('/<(?:br|p|div|tr|li)\s*\/?>/i', "\n", $text);

        // Strip remaining HTML tags
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize carriage returns and tabs
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Classify status from email text with multi-tier heuristic scores.
     */
    protected function classifyStatus(string $text, bool $hasMeetingLink = false): array
    {
        $lower = strtolower($text);

        $rules = [
            'rejected' => [
                'high' => [
                    'regret to inform', 'unfortunately, we will not be moving forward', 'we have decided to move forward with other candidates',
                    'belum dapat melanjutkan', 'sayangnya belum dapat', 'sayangnya kami belum dapat', 'belum berhasil ke tahap',
                    'belum sesuai dengan kualifikasi', 'posisi telah terisi', 'keep your resume on file', 'kami simpan data anda',
                    'tidak dapat memproses lamaran', 'belum dapat bergabung', 'we will not be proceeding',
                    'decided to pursue other applicants', 'unable to offer you', 'after careful consideration, we have chosen',
                ],
                'medium' => [
                    'tetap semangat', 'terima kasih atas partisipasi', 'terima kasih atas minat', 'semoga sukses',
                    'kesempatan lain di masa', 'future opportunities', 'other candidates whose qualifications',
                    'not selected', 'tidak terpilih', 'belum lolos',
                ],
            ],
            'offer' => [
                'high' => [
                    'job offer', 'offering letter', 'surat penawaran', 'pleased to offer you the position',
                    'formal offer', 'kami menawarkan posisi', 'selamat bergabung bersama kami', 'welcome to the team',
                    'paket penawaran kerja', 'penawaran pekerjaan', 'surat penawaran kerja', 'salary offer',
                    'letter of offer', 'employment agreement', 'tanda tangan offering',
                ],
                'medium' => [
                    'rincian kompensasi', 'benefit dan fasilitas', 'kompensasi dan benefit', 'start date',
                    'tanggal mulai bekerja', 'penawaran gaji', 'kontrak kerja',
                ],
            ],
            'interview' => [
                'high' => [
                    'undangan wawancara', 'undangan interview', 'interview invitation', 'jadwal interview',
                    'jadwal wawancara', 'technical interview', 'user interview', 'hr interview',
                    'wawancara user', 'wawancara hrd', 'wawancara teknis', 'sesi interview',
                    'sesi wawancara', 'wawancara tahap', 'interview tahap', 'final interview',
                    'wawancara akhir', 'tautan wawancara', 'link wawancara', 'kami mengundang anda untuk wawancara',
                    'we would like to invite you for an interview', 'schedule an interview', 'agenda wawancara',
                ],
                'medium' => [
                    'interview', 'wawancara', 'google meet', 'zoom meeting', 'microsoft teams',
                    'tatap muka virtual', 'sesi perkenalan', 'video call interview', 'diskusi teknis',
                    'meet.google.com', 'zoom.us', 'tahap wawancara', 'interview via',
                ],
            ],
            'screening' => [
                'high' => [
                    'online assessment', 'online test', 'tes online', 'psikotes', 'psikotest',
                    'screening test', 'hackerrank', 'codility', 'glints skill test', 'tes kemampuan dasar',
                    'tkd', 'core values assessment', 'technical test link', 'tautan tes online',
                    'tahap seleksi administrasi lolos', 'lolos seleksi berkas', 'seleksi administrasi',
                ],
                'medium' => [
                    'assessment', 'pengerjaan tes', 'tahap screening', 'tahap tes', 'uji kompetensi',
                    'batas waktu pengerjaan', 'deadline pengerjaan tes', 'soal tes', 'link pengerjaan',
                ],
            ],
            'applied' => [
                'high' => [
                    'thank you for applying', 'terima kasih telah melamar', 'lamaran anda telah kami terima',
                    'we have received your application', 'application received', 'konfirmasi lamaran',
                    'telah berhasil mengirimkan lamaran', 'pendaftaran berhasil',
                ],
                'medium' => [
                    'lamaran diterima', 'berkas lamaran', 'resume received', 'akan kami review',
                    'tim hrd akan meninjau',
                ],
            ],
        ];

        $scores = [
            'rejected' => 0,
            'offer' => 0,
            'interview' => 0,
            'screening' => 0,
            'applied' => 0,
        ];

        $matchedKeywords = [
            'rejected' => [],
            'offer' => [],
            'interview' => [],
            'screening' => [],
            'applied' => [],
        ];

        // Meeting link automatically gives strong signal to interview
        if ($hasMeetingLink) {
            $scores['interview'] += 25;
            $matchedKeywords['interview'][] = 'Meeting Link Detected';
        }

        foreach ($rules as $status => $levels) {
            foreach ($levels['high'] as $phrase) {
                if (str_contains($lower, $phrase)) {
                    $scores[$status] += 30;
                    $matchedKeywords[$status][] = $phrase;
                }
            }
            foreach ($levels['medium'] as $phrase) {
                if (str_contains($lower, $phrase)) {
                    $scores[$status] += 12;
                    $matchedKeywords[$status][] = $phrase;
                }
            }
        }

        // Rejection check: Rejection is very decisive. If explicit rejection phrases exist, boost rejection
        if (!empty($matchedKeywords['rejected'])) {
            $scores['rejected'] += 20;
        }

        // Find max score
        arsort($scores);
        $topStatus = array_key_first($scores);
        $topScore = $scores[$topStatus];

        // Default fallback if no significant score
        if ($topScore < 15) {
            $topStatus = 'applied';
            $confidence = 'low';
        } elseif ($topScore >= 50) {
            $confidence = 'high';
        } else {
            $confidence = 'medium';
        }

        return [
            'status' => $topStatus,
            'score' => $topScore,
            'confidence' => $confidence,
            'matched_keywords' => array_unique($matchedKeywords[$topStatus] ?? []),
            'key_phrases' => array_slice(array_unique($matchedKeywords[$topStatus] ?? []), 0, 3),
        ];
    }

    /**
     * Find matching job application belonging to the user.
     */
    protected function findMatchingApplication(
        User $user,
        string $subject,
        string $sender,
        string $content,
        ?int $explicitApplicationId,
        string &$matchedConfidence
    ): ?JobApplication {
        if ($explicitApplicationId) {
            $app = $user->jobApplications()->find($explicitApplicationId);
            if ($app) {
                $matchedConfidence = 'high';
                return $app;
            }
        }

        $applications = $user->jobApplications()->get();
        if ($applications->isEmpty()) {
            $matchedConfidence = 'low';
            return null;
        }

        $bestApp = null;
        $bestScore = 0;

        $senderLower = strtolower($sender);
        $subjectLower = strtolower($subject);
        $contentLower = strtolower(substr($content, 0, 2000)); // First 2000 chars

        foreach ($applications as $app) {
            $company = trim($app->company_name);
            if (empty($company)) {
                continue;
            }

            $compLower = strtolower($company);
            $appScore = 0;

            // 1. Sender email domain match (e.g. @tokopedia.com -> Tokopedia)
            $cleanCompWord = preg_replace('/[^a-z0-9]/', '', $compLower);
            if (strlen($cleanCompWord) >= 3 && str_contains($senderLower, $cleanCompWord)) {
                $appScore += 60;
            }

            // Core brand keyword (strip generic suffixes: PT, Group, Indonesia, Tbk, etc.)
            $coreBrand = trim(preg_replace('/\b(?:pt|cv|tbk|group|indonesia|technologies|technology|international|services|consulting|holdings|corp|corporation|inc|ltd)\b/i', '', $compLower));
            $coreClean = preg_replace('/[^a-z0-9]/', '', $coreBrand);
            if (strlen($coreClean) >= 3 && $coreClean !== $cleanCompWord) {
                if (str_contains($senderLower, $coreClean)) {
                    $appScore += 55;
                }
                if (str_contains($subjectLower, $coreBrand)) {
                    $appScore += 45;
                }
                if (str_contains($contentLower, $coreBrand)) {
                    $appScore += 30;
                }
            }

            // 2. Exact company name in Subject
            if (str_contains($subjectLower, $compLower)) {
                $appScore += 50;
            }

            // 3. Exact company name in Content body
            if (str_contains($contentLower, $compLower)) {
                $appScore += 35;
            }

            // 4. Word boundary match
            if (preg_match('/\b' . preg_quote($compLower, '/') . '\b/i', $subjectLower)) {
                $appScore += 15;
            }

            // 5. Position match in subject or body
            if (!empty($app->position)) {
                $posLower = strtolower(trim($app->position));
                if (strlen($posLower) >= 3 && (str_contains($subjectLower, $posLower) || str_contains($contentLower, $posLower))) {
                    $appScore += 25;
                }
            }

            if ($appScore > $bestScore) {
                $bestScore = $appScore;
                $bestApp = $app;
            }
        }

        if ($bestScore >= 50) {
            $matchedConfidence = 'high';
        } elseif ($bestScore >= 25) {
            $matchedConfidence = 'medium';
        } else {
            $matchedConfidence = 'low';
            $bestApp = null;
        }

        return $bestApp;
    }

    /**
     * Extract meeting link (Google Meet, Zoom, MS Teams) from text.
     */
    public function extractMeetingLink(string $text): ?string
    {
        foreach ($this->meetingLinkPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[0]);
            }
        }

        return null;
    }

    /**
     * Extract interview date and time heuristics from text.
     */
    public function extractDateTime(string $text): ?string
    {
        // Pattern 1: Indonesian date with time
        // e.g., "Senin, 15 September 2026 pukul 14:00 WIB" or "10 Okt 2026 10:30"
        $patternIndo = '/(?:(?:Hari|Pada)\s+)?(?:Senin|Selasa|Rabu|Kamis|Jum[\'a]at|Sabtu|Minggu)?(?:,\s*)?(\d{1,2}\s+(?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Aug|Sep|Okt|Oct|Nov|Des|Dec)\s+\d{4})(?:[^\n\r]*?(?:pukul|jam|at|time)\s*[:]?\s*(\d{1,2}[:.]\d{2}(?:\s*(?:WIB|WITA|WIT|AM|PM))?))?/i';

        if (preg_match($patternIndo, $text, $matches)) {
            $date = $matches[1] ?? '';
            $time = $matches[2] ?? '';
            return trim($date . ($time ? ' ' . $time : ''));
        }

        // Pattern 2: English date with time
        // e.g. "September 15, 2026 at 2:00 PM" or "15th September 2026 14:00"
        $patternEng = '/((?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{1,2}(?:st|nd|rd|th)?,?\s+\d{4})(?:[^\n\r]*?(?:at|time)\s*[:]?\s*(\d{1,2}[:.]\d{2}\s*(?:AM|PM|WIB)?))?/i';
        if (preg_match($patternEng, $text, $matches)) {
            $date = $matches[1] ?? '';
            $time = $matches[2] ?? '';
            return trim($date . ($time ? ' ' . $time : ''));
        }

        // Pattern 3: Simple ISO-like or slash date: "15/09/2026 14:00"
        $patternSlash = '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})(?:\s+(?:pukul\s+)?(\d{1,2}[:.]\d{2}(?:\s*(?:WIB|WITA|WIT|AM|PM))?))?/i';
        if (preg_match($patternSlash, $text, $matches)) {
            $date = $matches[1] ?? '';
            $time = $matches[2] ?? '';
            return trim($date . ($time ? ' ' . $time : ''));
        }

        return null;
    }

    /**
     * Extract candidate company name from sender or subject.
     */
    protected function extractCompanyName(string $subject, string $sender, string $content): ?string
    {
        // 1. From sender display name: "Talent Acquisition PT Shopee International <hr@shopee.com>"
        if (preg_match('/^"?([^"<@]+)"?\s*</', $sender, $matches)) {
            $name = trim($matches[1]);
            // Remove generic titles
            $cleaned = preg_replace('/^(?:HRD|HR|Talent Acquisition|Recruitment|Rekrutmen|Tim|Team)\s+(?:of\s+)?/i', '', $name);
            if (!empty($cleaned) && strlen($cleaned) >= 2 && !preg_match('/no[-_]?reply/i', $cleaned)) {
                return $cleaned;
            }
        }

        // 2. From sender email domain: "recruiter@tokopedia.com" -> "Tokopedia"
        if (preg_match('/@([a-z0-9\-]+)\.(?:com|co\.id|id|io|tech|net)/i', $sender, $matches)) {
            $domain = $matches[1];
            $ignored = ['gmail', 'yahoo', 'outlook', 'hotmail', 'mail', 'noreply', 'notifications'];
            if (!in_array(strtolower($domain), $ignored, true)) {
                return ucfirst($domain);
            }
        }

        // 3. From subject brackets: "[Tokopedia] Undangan Wawancara" or "Undangan Interview - BCA"
        if (preg_match('/\[([^\]]+)\]/', $subject, $matches)) {
            $candidate = trim($matches[1]);
            if (strlen($candidate) <= 40) {
                return $candidate;
            }
        }

        if (preg_match('/(?:di|at|from|\-)\s+([A-Z][A-Za-z0-9\s\.\&]+?)(?:\s+(?:pada|via|for|$|\-))/i', $subject, $matches)) {
            $candidate = trim($matches[1]);
            if (strlen($candidate) <= 40) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Create short excerpt around matched keywords.
     */
    protected function createExcerpt(string $content, array $keywords): string
    {
        if (empty($content)) {
            return '';
        }

        if (empty($keywords)) {
            return substr($content, 0, 200) . '...';
        }

        $firstKey = $keywords[0];
        $pos = stripos($content, $firstKey);
        if ($pos === false) {
            return substr($content, 0, 200) . '...';
        }

        $start = max(0, $pos - 60);
        $length = 220;
        $excerpt = substr($content, $start, $length);

        return ($start > 0 ? '...' : '') . trim($excerpt) . '...';
    }

    /**
     * Generate suggested note for timeline update.
     */
    protected function generateSuggestedNote(
        string $status,
        string $subject,
        ?string $meetingDateTime,
        ?string $meetingLink,
        array $keyPhrases
    ): string {
        $dateStr = Carbon::now()->format('d M Y');
        $label = $this->getStatusLabel($status);

        $parts = ["[Update Email: {$label} - {$dateStr}]"];

        if (!empty($subject)) {
            $parts[] = "Subjek: \"{$subject}\"";
        }

        if (!empty($meetingDateTime)) {
            $parts[] = "Jadwal: {$meetingDateTime}";
        }

        if (!empty($meetingLink)) {
            $parts[] = "Link Pertemuan: {$meetingLink}";
        }

        if (!empty($keyPhrases)) {
            $parts[] = "Indikator: " . implode(', ', $keyPhrases);
        }

        return implode("\n", $parts);
    }

    /**
     * Human-friendly status label.
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'screening' => 'Screening / Tes',
            'interview' => 'Interview',
            'offer' => 'Offer / Penawaran',
            'rejected' => 'Ditolak / Selesai',
            'accepted' => 'Diterima',
            default => 'Applied (Terkirim)',
        };
    }
}
