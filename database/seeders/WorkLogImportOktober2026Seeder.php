<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\GA\WorkLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Impor sekali jalan: log kerja teknisi no. 78–103 (1–21 Oktober 2026) dari
 * Google Sheets "Jadwal Teknisi" (diekspor PDF, 11/11/2026). No. 1–77 belum
 * tersedia. Teks Detail/Hasil/Note disalin apa adanya dari sheet.
 *
 * Kategori tidak ada di sheet — ditetapkan dari isi Detail Kerja.
 * "No Assist" -> technician_assist null. Jam selesai lebih kecil dari jam
 * mulai = selesai hari berikutnya (lihat WorkLog::durationMinutes()).
 *
 * Idempoten: aman dijalankan ulang (firstOrCreate per tanggal + jam mulai +
 * outlet + detail kerja).
 *
 *   php artisan db:seed --class=WorkLogImportOktober2026Seeder --force
 */
class WorkLogImportOktober2026Seeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('name', 'GA Omotesando')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', Role::GA))->orderBy('id')->first();

        if (! $creator) {
            throw new RuntimeException('Tidak ada user GA utk kolom created_by.');
        }

        $branches = Branch::pluck('id', 'name');
        $location = fn (string $branch, string $name) => BranchLocation::where('branch_id', $branches[$branch] ?? 0)
            ->where('name', $name)->value('id');

        $P = WorkLog::CATEGORY_PERBAIKAN;
        $I = WorkLog::CATEGORY_INSTALASI;
        $M = WorkLog::CATEGORY_MAINTENANCE;

        // [no, tanggal, mulai, selesai, outlet, cabang, in charge, assist, kategori, detail, hasil, note]
        $rows = [
            [78, '2026-10-01', '11:00', '18:00', 'Zodiac', null, 'Bankat', null, $P,
                'Perbaikan kunci pintu utama zodiac, tutup jalur tikus, & perbaikan lampu dining serta room pink & bar',
                'Kunci pintu utama diganti unit baru & lampu all area diganti unit baru', null],
            [79, '2026-10-02', '13:44', '01:00', 'Ask for Patty', 'Gandaria City', 'Toni', null, $I,
                'Perapihan stop kontak AFP gancit',
                'Perapihan dan pemasangan stok kontak yang berantakan dari vendor', null],
            [80, '2026-10-02', '16:00', '22:10', 'Zodiac', null, 'Bankat', null, $P,
                'Cek 2 speaker passive & perbaikan pintuk samping untuk masuk gas',
                'Pintu besi sedang proses perbaikan untuk dilakukan pemotongan pakai alat gurindra', null],
            [81, '2026-10-04', '21:50', '22:45', 'Patty and Sons', null, 'Widy', null, $I,
                'Pemasangan kabel duct kabel LAN & isi freont',
                'Kabel duct dipasang agar kabel LANnya rapi & isi freon upright chiller supaya suhu kembali normal', null],
            [82, '2026-10-04', '22:00', '02:00', 'Ask for Patty', null, 'Toni', null, $P,
                'Perbaiki logo Ask for patty',
                'Logo Ask for patty tidak lurus dan sudah diperbaiki', null],
            [83, '2026-10-05', '13:00', '23:46', 'Zodiac', null, 'Toni', null, $P,
                'Perbaikan buka tutup pintu samping zodiac',
                'Pintu samping zodiac dibongkar dan dipasang ulang, tidak bisa dibuka karena tertutup bangunan trotoar', null],
            [84, '2026-10-06', '13:00', '00:35', 'Zodiac', null, 'Toni', null, $P,
                'Penutupan jalu2 tikus',
                'Pintu outdoor ditutup karna akses tikut masuk ke outlet', null],
            [85, '2026-10-07', '13:00', '14:00', 'Central Kitchen', null, 'Toni', null, $P,
                'Perbaikan chest freezer butcher',
                'Kabel kelistrikannya putus dan sudah disambungkan ulang', null],
            [86, '2026-10-07', '14:30', '21:00', 'Zodiac', null, 'Toni', 'Widy', $P,
                'Perbaikan CCTV',
                'Masalah di kabel LANnya, tidak terkoneksi ke monitor hik connect', 'Pembelian kabel LAN baru'],
            [87, '2026-10-07', '22:00', '00:45', 'Patty and Sons', null, 'Toni', 'Widy', $P,
                'Perbaikan keramik kitchen',
                'Keramik sudah terpasang dan lebih rapi kembali dindingnya', null],
            [88, '2026-10-07', '23:00', '02:40', 'The Cutler', null, 'Bankat', null, $M,
                'Deep cleaning',
                'Cleaning 5 motor Fan & ducting', null],
            [89, '2026-10-09', '09:00', '10:00', 'The Cutler', null, 'Bankat', null, $P,
                'Pengerjaan lubang jalur tikus',
                'Lantai kitchen yang berlubang sudah ditutup dengan sikaboom', null],
            [90, '2026-10-10', '11:00', '14:00', 'Central Kitchen', null, 'Toni', 'Widy', $I,
                'Pasang tabung mesin pompa air',
                'Pompa air diganti unit baru', null],
            [91, '2026-10-10', '15:00', '18:00', 'Ironiku', null, 'Toni', 'Widy', $P,
                'Las kursi bar',
                'Kursi bar yang copot bagian pijakan kakinya, sudah dilas dan bisa digunakan kembali', null],
            [92, '2026-10-10', '17:00', '00:00', 'Zodiac', null, 'Toni', 'Widy', $P,
                'Las kursi bar',
                'Kursi bar copot & sudah dilas, serta perbaikan meja bar', null],
            [93, '2026-10-11', '14:00', '15:00', 'Ironiku', null, 'Toni', null, $P,
                'Perbaikan hexaust toilet',
                'Kapasitornya diganti dan sudah kembali normal', null],
            [94, '2026-10-13', '11:00', '12:00', 'Zodiac', null, 'Toni', 'Widy', $P,
                'Perbaikan sink nyetrum',
                'Stop kontaknya sudah rusak, jadi kena air dan itu yang mengakibatkan sinknya nyetrum (sementara sudah disetting sembari nunggu stop kontak baru)',
                'Akan diganti stop kontaknya & sudah diajukan pembeliannya'],
            [95, '2026-10-14', '14:00', '15:00', 'Central Kitchen', null, 'Toni', 'Widy', $P,
                'Perbaikan trolly',
                'Roda trolly diganti unit baru', null],
            [96, '2026-10-14', '16:00', '20:00', 'Patty and Sons', null, 'Toni', 'Widy', $P,
                'Perbaikan kran sink & lampu abuaye',
                'Kran sinknya airnya tidak keluar dengan normal karena ada saluran pipa yang rusak & lampu abuaye jalur listriknya ada yang putus dan sudah di perbaiki', null],
            [97, '2026-10-14', '21:00', '05:10', 'Patty and Sons', null, 'Toni', 'Bankat', $I,
                'Instalasi saklar & lampu PNS',
                'Lampu indoor & outdoor diganti, serta pemasangan saklar baru', null],
            [98, '2026-10-15', '16:30', '17:30', 'Zodiac', null, 'Toni', 'Widy', $P,
                'Perbaikan tutup closet toilet',
                'Tutup toilet kendor, jadi goyang saat didudkin, bautnya sudah diperbaiki', null],
            [99, '2026-10-20', '14:00', '15:00', 'Zodiac', null, 'Bankat', null, $P,
                'Perbaikan up freezer suhu',
                'Mati karena trip MCBnya mati, serta sudah dilakukan cleaning unit condesor & filter', null],
            [100, '2026-10-20', '22:00', '23:00', 'Ironiku', null, 'Bankat', 'Widy', $P,
                'Perbaikan keramik bak kontrol',
                'Dilakukan pemasangan plat bodres untuk menghindari jeblos',
                'Saat ini sudah diajukan pengajuan keramik & sedang dalam proses pembuatan ukurannya.'],
            [101, '2026-10-20', '23:20', '00:00', 'The Cutler', null, 'Bankat', 'Widy', $P,
                'Perbaikan lampu dinding',
                'Kabel putus & sudah disambung kembali', null],
            [102, '2026-10-21', '21:30', '22:00', 'Patty and Sons', null, 'Widy', 'Bankat', $P,
                'Perbaikan pintu kitchen',
                'Sudah diperbaiki, pintu beradu karena engsel pintu sebelahnya lepas', null],
            [103, '2026-10-21', '22:00', '22:35', 'The Cutler', null, 'Widy', 'Bankat', $P,
                'Perbaikan roda trolly',
                'Roda trolly diganti unit baru', null],
        ];

        foreach ($rows as [$no, $date, $start, $end, $branchName, $locName, $ic, $assist, $category, $detail, $result, $note]) {
            $branchId = $branches[$branchName] ?? throw new RuntimeException("Outlet '{$branchName}' (no. {$no}) tidak ditemukan.");

            WorkLog::firstOrCreate(
                [
                    'work_date' => $date,
                    'start_time' => $start,
                    'branch_id' => $branchId,
                    'work_detail' => $detail,
                ],
                [
                    'end_time' => $end,
                    'category' => $category,
                    'branch_location_id' => $locName ? $location($branchName, $locName) : null,
                    'technician_in_charge' => $ic,
                    'technician_assist' => $assist,
                    'work_result' => $result,
                    'notes' => $note,
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
