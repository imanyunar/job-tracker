# Frontend Design: Job Application Tracker

## Konteks

Ini bukan produk SaaS yang dijual ke banyak orang, ini alat pribadi buat satu orang mantau lamaran kerjanya sendiri. Pengalaman utamanya bukan "wow" pas pertama buka, tapi dipakai berulang, sering, dan cepat: buka, lihat status terakhir, update, tutup. Mood yang mau dibangun bukan optimisme SaaS ("kejar produktivitasmu!"), tapi lebih ke ketenangan, kayak buku catatan/logbook yang jujur soal proses cari kerja yang isinya banyak nunggu dan nggak semuanya berujung baik.

## Kenapa bukan default yang biasa

Saya sengaja hindari beberapa pola yang sering muncul di desain AI generic:
- Warna cream hangat + aksen terracotta (kombinasi yang udah jadi ciri khas produk AI, gampang ketauan)
- Background near black + aksen neon hijau/vermillion
- Kartu SaaS seragam dengan border radius sama semua dan shadow abu lembut generik
- Label ALL CAPS di atas tiap heading, meta string pakai titik tengah (A · B · C)
- Font monospace buat label data kecil (status, tanggal), padahal itu bukan konteks kode

## Palet warna

Basisnya bukan cream, tapi abu kebiruan pucat, kesan langit mendung/ruang tunggu, cocok sama nuansa "nunggu kabar" dari proses lamar kerja.

| Token | Hex | Pemakaian |
|---|---|---|
| `bg` | `#DCE1DE` | Background utama, sage abu pucat |
| `surface` | `#F3F4F0` | Panel/list row, sedikit lebih terang dari bg |
| `ink` | `#1C2B2A` | Teks utama, hijau tua gelap (bukan hitam pekat) |
| `ink-muted` | `#5B6863` | Teks sekunder, meta info |
| `accent-forward` | `#B8752F` | Dipakai hanya saat status maju (interview, offer, accepted) |
| `accent-closed` | `#8B5A5A` | Dipakai hanya saat status berhenti (rejected) |

Aksen warna cuma muncul kalau ada perubahan status, bukan dekorasi tetap. Row yang statusnya "applied" polos aja, nggak dikasih warna karena belum ada progres yang perlu ditandai.

## Tipografi

- Display/heading: **Fraunces** (serif dengan karakter, optical size besar buat judul), dipakai buat nama perusahaan dan posisi biar terasa personal, bukan kayak dashboard korporat
- Body/UI: **IBM Plex Sans**, jelas dan netral buat form, tombol, teks panjang
- Nggak ada font monospace buat status/tanggal, dipakai Plex Sans biasa dengan weight medium biar tetap jelas dibaca tapi nggak kesan "kode"

Line length body dijaga di bawah 80 karakter, terutama di panel catatan/notes biar nggak capek dibaca.

## Layout

Dua panel, bukan grid kartu seragam. Kiri daftar ringkas (kayak log/ledger), kanan detail lamaran yang dipilih. Ini lebih cocok buat alat yang dipakai scan cepat, dibanding grid kartu yang bagus buat browsing tapi lambat buat cek status satu per satu.

```
+------------------------------------------------------+
| Job Application Tracker              [+ Tambah]      |
+------------------+-------------------------------------+
| Cari...          | Frontend Engineer                   |
| [Semua v]        | PT Nawa Digital                     |
|                  |                                      |
| PT Nawa Digital  | Status: Interview                   |
|  Frontend Eng.   | Dilamar: 12 Agu 2026                |
|  Interview       | Sumber: LinkedIn                    |
|                  |                                      |
| CV Studio        | Catatan:                            |
|  UI Designer     | Interview kedua tgl 20 Agu,          |
|  Applied         | dengan tim produk.                  |
|                  |                                      |
| Teknoaplikasi    | [Ubah status v]  [Edit]  [Hapus]     |
|  Backend Dev     |                                      |
|  Rejected        |                                      |
+------------------+-------------------------------------+
```

List di kiri rata kiri (left aligned), bukan center, karena isinya informasi yang perlu discan cepat berurutan, bukan konten yang perlu dipamerkan. Detail di kanan juga rata kiri, ngikutin baseline yang sama biar mata nggak lompat.

## Status sebagai warna baris, bukan badge bulat

Daripada badge pill warna warni ala SaaS (yang sering jadi ciri khas AI generated UI), status ditandain lewat garis tipis di kiri tiap row list (border-left 3px) pakai `accent-forward` atau `accent-closed` sesuai kondisi. Row "applied" dan "screening" nggak dikasih garis warna karena masih status netral, belum ada progres yang perlu ditandai.

## Motion

Satu momen aja yang dikasih animasi jelas: saat status berubah, row-nya geser urutan (kalau di-sort by status) dengan transisi singkat 200ms, biar user notice ada progres terjadi. Selain itu nggak ada hover animation di tiap elemen atau fade in scattered pas halaman load, karena ini alat kerja yang dibuka berkali-kali sehari, bukan halaman marketing yang cuma dilihat sekali.

## Microcopy

Tombol pakai kata kerja aktif dan konkret: "Tambah lamaran" bukan "Submit", "Ubah status" bukan "Update". Kalau list kosong (belum ada lamaran sama sekali), teksnya bukan generic "No data available" tapi ajakan langsung: "Belum ada lamaran tercatat. Mulai dari yang pertama kamu kirim minggu ini."

Kalau ada error (misal gagal simpan), teksnya jelasin apa yang salah dan apa yang perlu dilakukan, bukan permintaan maaf generic: "Tanggal lamar belum diisi, lengkapi dulu sebelum disimpan."
