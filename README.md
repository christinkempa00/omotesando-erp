# Allez ERP — PT Omotesando

Sistem ERP internal untuk grup restoran PT Omotesando (Allez Group). Dipakai tim
GA, Head, IT, dan staf outlet untuk mengelola aset, seragam, pemeliharaan,
pengajuan barang/jasa, dan log kerja teknisi.

**Outlet:** Ironiku · The Cutler · Ask for Patty · Zodiac · Patty and Sons ·
Central Kitchen · Head Office · Central Storage

**Production:** https://portal.allez-group.com

> Riwayat perubahan ada di [CHANGELOG.md](CHANGELOG.md). Panduan deploy ada di
> [DEPLOY.md](DEPLOY.md).

---

## Daftar Isi

- [Teknologi](#teknologi)
- [Menjalankan di Lokal](#menjalankan-di-lokal)
- [Role & Portal](#role--portal)
- [Modul](#modul)
- [Konsep Data](#konsep-data)
- [Struktur Kode](#struktur-kode)
- [Konvensi](#konvensi)
- [Deploy](#deploy)

---

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Blade, Tailwind CSS 3, Alpine.js 3, Vite 7 |
| Database | MySQL (production), SQLite (lokal) |
| Dokumen | `barryvdh/laravel-dompdf` (PDF), `phpoffice/phpspreadsheet` (Excel), `endroid/qr-code` (QR aset) |
| Notifikasi | Telegram Bot (pengingat pemeliharaan, permintaan cepat) |

---

## Menjalankan di Lokal

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan storage:link

composer run dev        # server Laravel + Vite + queue sekaligus
```

### Akun testing

Dibuat oleh `database/seeders/UserSeeder.php`. Password: **`password`**.

| Role | Email |
|---|---|
| Head | `head@omotesando.test` |
| IT | `it@omotesando.test` |
| Outlet (Ironiku) | `outlet@omotesando.test` |

Akun GA (`ga@allez-group.com`) memakai password tersendiri, lihat
`UserSeeder`. Semua akun wajib ganti password saat login pertama.

### Variabel `.env` tambahan

| Variabel | Fungsi |
|---|---|
| `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID` | Notifikasi Telegram |
| `DEPLOY_TOKEN` | Route darurat `/deploy-tasks/{token}` untuk hosting tanpa SSH (lihat DEPLOY.md) |

---

## Role & Portal

Setiap role punya portal dan sidebar sendiri. Akses per modul diatur per akun
oleh IT (Manajemen User), bukan hanya berdasarkan role.

| Role | Portal | Isi |
|---|---|---|
| **GA** | `/ga`, `/dashboard` | Operasional penuh: aset, seragam, pemeliharaan, pengajuan, work log |
| **Head** | `/head` | Monitoring read-only + Kontrol Modul |
| **IT** | `/it` | Manajemen user, kontrol akses & mode pemeliharaan, papan kerja Kanban |
| **Outlet** | `/outlet` | Versi sederhana modul GA, otomatis terbatas pada outlet milik akun |

Hak akses per halaman bisa **Edit** atau **Lihat saja** (`UserPagePermission`).

---

## Modul

### GA (General Affairs)

| Modul | Route | Ringkasan |
|---|---|---|
| **Dashboard** | `dashboard` | KPI aset, pengajuan, dan biaya pemeliharaan |
| **Asset Inventory** | `ga.assets.*` | CRUD aset (kode `AST-0001`), foto aset & foto SN, QR per aset + halaman publik `/a/{kode}`, label QR massal, export Excel/PDF |
| **Asset Maintenance Schedule** | `ga.maintenance.*` | Jadwal pemeliharaan per aset (`MNT-2026-0001`), status, tipe, prioritas, checklist, pengingat Telegram H-1 hari & H-1 jam |
| **Uniform Inventory** | `ga.uniforms.records.*` | Serah-terima & pengembalian seragam per karyawan, foto bukti wajib, Berita Acara (PDF) |
| **Stock Management** | `ga.uniforms.stocks.*` | Stok per varian (outlet/tipe/warna/ukuran), restock, adjustment, disposal, riwayat mutasi; tampilan Kartu atau Tabel |
| **Asset Request** | `ga.requests.*` | Pengajuan barang/jasa (draft → diajukan → dokumen PDF) + permintaan cepat via Telegram |
| **Work Log** | `ga.worklogs.*` | Log kerja teknisi per outlet: waktu (boleh lewat tengah malam), kategori, teknisi in charge/assist, hasil, foto; diagram beban kerja per teknisi |

Semua modul GA **tanpa alur approval**: data langsung tersimpan dan berlaku.

### Head

- **Dashboard** dan **Semua Pengajuan**: monitoring, tanpa aksi.
- **Inventaris Aset, Inventaris Seragam, Jadwal Pemeliharaan**: read-only.
- **Kontrol Modul**: aktif/nonaktifkan modul GA.

### IT

- **Manajemen User**: satu-satunya jalur pembuatan akun (`/register` publik
  dihapus). Atur role, modul, outlet, divisi, status aktif, dan reset password.
- **Kontrol Akses & Mode Pemeliharaan**: tandai halaman "Dalam Pemeliharaan"
  tanpa deploy. User non-IT diarahkan ke pemberitahuan; IT tetap bisa masuk.
- **Papan Kerja Kanban**: task IT dengan checklist, komentar, dan label.

### Outlet

Pengajuan, Aset, Seragam (stok & serah-terima), Jadwal Pemeliharaan, dan Work
Log, dengan data otomatis dibatasi ke outlet akun tersebut. Modul **Monitoring
Outlet** (laporan inspeksi per area) masih dalam pengembangan.

### Fitur lintas modul

- **Filter tersimpan**: filter daftar diingat per user; kembali ke daftar
  memulihkan filter terakhir. Tombol Reset menghapusnya.
- **Filter Outlet + Cabang** di daftar Aset dan Pengajuan GA.
- **Lightbox foto**: klik foto untuk melihat layar penuh, zoom, dan geser.
- **Header sticky**: judul halaman tetap terlihat saat scroll.

---

## Konsep Data

| Istilah di UI | Model | Keterangan |
|---|---|---|
| **Outlet** | `Branch` | Ironiku, The Cutler, Ask for Patty, dst. |
| **Cabang** | `BranchLocation` | Sub-lokasi di bawah outlet, mis. Ask for Patty → Gandaria City / Event. Bisa ditambah cepat dari dropdown Cabang di form |
| **Lokasi** | kolom `location` | Area di dalam outlet: Kitchen, Floor, Bar, Lainnya |

Daftar outlet per modul diatur lewat konstanta di `App\Models\Branch`
(`GA_OUTLETS`, `WORK_LOG_OUTLETS`, `MONITORING_OUTLETS`).

---

## Struktur Kode

```
app/
  Http/Controllers/
    GA/        Controller modul GA (dipakai juga oleh portal Outlet)
    Head/      Monitoring Head
    IT/        Manajemen user, kontrol modul, Kanban
    Outlet/    Dashboard & laporan Outlet
  Models/      Model inti (Branch, User, Role, Module, ...)
    GA/        Model modul GA
    IT/        Model Kanban IT
  Console/Commands/   maintenance:send-reminders, import foto aset
resources/views/
  ga/  head/  it/  outlet/   View per portal
  components/                Komponen Blade bersama (filter, lightbox, footer tabel, ...)
  layouts/                   Layout aplikasi & sidebar
routes/web.php               Semua route, dikelompokkan per portal
```

---

## Konvensi

- **Satu controller untuk dua portal.** Controller GA melayani `ga.*` dan
  `outlet.*`. Gunakan `viewFor()` dan `routeFor()` di `Controller` agar view dan
  route mengikuti portal aktif. Outlet di-scope lewat `User::scopingBranch()`.
- **Filter daftar** memakai `Controller::rememberFilters()`.
- **Komponen bersama** ada di `resources/views/components`: `filter-bar`,
  `filter-pills`, `branch-filter`, `branch-location-select`, `photo-lightbox`,
  `table-footer`.
- **Bahasa UI**: Bahasa Indonesia untuk isian data; sebagian label navigasi GA
  memakai Bahasa Inggris.
- **Tema**: token warna & komponen (`card-flat`, `glass-panel`, `btn-primary`,
  `btn-secondary`, `field-input`) ada di `tailwind.config.js` dan
  `resources/css/app.css`.

### Testing

```bash
php artisan test
```

---

## Deploy

Production berjalan di Hostinger dengan struktur terpisah (`laravel_app` +
`public_html`). Ringkasnya:

1. `npm run build`, lalu upload `public/build` ke `public_html/build`.
2. Upload file yang berubah dengan `bash deploy.sh <path...>` (via SSH alias
   `hostinger-omotesando`).
3. Di server: `php artisan migrate --force` bila ada migration, lalu
   `php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`.
4. Scheduler: cron `php artisan schedule:run` tiap menit (pengingat pemeliharaan).

Langkah lengkap, setup awal, dan troubleshooting ada di [DEPLOY.md](DEPLOY.md).
