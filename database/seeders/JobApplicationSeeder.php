<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use Illuminate\Database\Seeder;

class JobApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samples = [
            [
                'company_name' => 'PT Nawa Digital',
                'position' => 'Frontend Engineer',
                'status' => 'interview',
                'applied_date' => '2026-08-12',
                'source' => 'LinkedIn',
                'job_url' => 'https://linkedin.com/jobs/view/nawa-frontend-eng',
                'location' => 'Jakarta Selatan (Hybrid)',
                'notes' => 'Interview kedua tanggal 20 Agustus dengan tim produk dan VP of Engineering.',
                'salary_range_min' => 14000000,
                'salary_range_max' => 18000000,
            ],
            [
                'company_name' => 'CV Studio Desain',
                'position' => 'UI/UX Designer',
                'status' => 'applied',
                'applied_date' => '2026-08-25',
                'source' => 'Jobstreet',
                'job_url' => 'https://jobstreet.co.id/job/ui-designer-studio',
                'location' => 'Bandung (WFO)',
                'notes' => 'Kirim portfolio Figma lewat form website. Menunggu konfirmasi HR.',
                'salary_range_min' => 8000000,
                'salary_range_max' => 11000000,
            ],
            [
                'company_name' => 'Teknoaplikasi Nusantara',
                'position' => 'Backend Developer',
                'status' => 'rejected',
                'applied_date' => '2026-08-05',
                'source' => 'Glints',
                'job_url' => 'https://glints.com/id/opportunities/backend-dev-tekno',
                'location' => 'Jakarta Barat',
                'notes' => 'Dapat email penolakan setelah take-home test. Feedback: butuh yang lebih berpengalaman di Golang concurrency.',
                'salary_range_min' => 12000000,
                'salary_range_max' => 15000000,
            ],
            [
                'company_name' => 'Artha Finansial Teknologi',
                'position' => 'Full Stack Developer',
                'status' => 'offer',
                'applied_date' => '2026-08-01',
                'source' => 'Referral Teman',
                'job_url' => 'https://arthafin.co.id/careers',
                'location' => 'Jakarta Pusat (Hybrid)',
                'notes' => 'Offering letter sudah masuk via email. Gaji 16jt + tunjangan kesehatan. Batas konfirmasi akhir pekan ini.',
                'salary_range_min' => 15000000,
                'salary_range_max' => 17000000,
            ],
            [
                'company_name' => 'ScaleOps Remote HQ',
                'position' => 'Software Engineer (Vue/TypeScript)',
                'status' => 'screening',
                'applied_date' => '2026-08-28',
                'source' => 'RemoteOK',
                'job_url' => 'https://remoteok.com/job/scaleops-swe',
                'location' => 'Remote (Singapore timezone)',
                'notes' => 'Sudah isi screening form di portal Greenhouse.',
                'salary_range_min' => 22000000,
                'salary_range_max' => 30000000,
            ],
            [
                'company_name' => 'Mitra Solusi Solusindo',
                'position' => 'Web Developer',
                'status' => 'accepted',
                'applied_date' => '2026-07-20',
                'source' => 'LinkedIn',
                'job_url' => 'https://mitrasolusi.com/careers/web-dev',
                'location' => 'Yogyakarta (Remote)',
                'notes' => 'Tawaran diterima. Mulai onboarding tanggal 15 September.',
                'salary_range_min' => 13000000,
                'salary_range_max' => 15000000,
            ],
        ];

        foreach ($samples as $sample) {
            JobApplication::updateOrCreate(
                [
                    'company_name' => $sample['company_name'],
                    'position' => $sample['position'],
                ],
                $sample
            );
        }
    }
}
