<?php

namespace Database\Seeders;

use App\Models\QuestionnaireItem;
use Illuminate\Database\Seeder;

class QuestionnaireItemSeeder extends Seeder
{
    /**
     * Data awal dari Lampiran 6 - Kuesioner Penelitian, diperbarui sesuai revisi
     * klien (revisi-program-perbaikan.pdf): item lama dipertahankan apa adanya
     * (urutan & teks tidak diubah agar riwayat jawaban lama tidak terganggu),
     * item baru ditambahkan di akhir tiap kategori, dan satu item yang diminta
     * dihapus klien dinonaktifkan via kolom `aktif` (bukan dihapus fisik) supaya
     * jawaban historis yang sudah terlanjur tersimpan tetap bisa dibuka.
     *
     * Pencocokan baris pakai (kategori_kode, pertanyaan) persis, bukan nomor -
     * supaya nomor boleh berubah/ditambah tanpa pernah menimpa teks pertanyaan
     * milik item lama yang sudah punya jawaban.
     */
    public function run(): void
    {
        $kategoriList = [
            'A' => [
                'nama' => 'Kebijakan, Organisasi, dan SDM K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab II; UU No. 1 Tahun 1970 Pasal 3 dan 9',
                'items' => [
                    // -- item lama (jangan diubah) --
                    'Puskesmas memiliki kebijakan/program K3 tertulis.',
                    'Puskesmas memiliki Tim/Petugas K3 yang ditunjuk secara resmi (Surat Keputusan).',
                    'Puskesmas pernah memberikan pelatihan/sosialisasi K3 kepada pegawai dalam 1 tahun terakhir.',
                    'Puskesmas memiliki SOP pencatatan dan pelaporan kecelakaan kerja serta penyakit akibat kerja (PAK).',
                    // -- item baru (revisi) --
                    'Kebijakan K3 ditetapkan dalam Keputusan Kepala Puskesmas dan ditandatangani oleh pimpinan tertinggi Puskesmas.',
                    'Komitmen/kebijakan K3 tertulis disosialisasikan kepada seluruh SDM dan diletakkan di tempat strategis yang dapat dibaca pengunjung.',
                    'Tersedia sumber daya untuk pelaksanaan program K3 Puskesmas.',
                    'SDM Puskesmas (khususnya Tim/Penanggung Jawab K3) pernah mengikuti pelatihan/peningkatan kompetensi K3 yang sesuai standar kurikulum dan sertifikasi Kementerian Kesehatan.',
                    'Puskesmas memiliki rencana kerja/program K3 (tahunan) dengan sasaran yang jelas dan terukur.',
                    'Perencanaan K3 Puskesmas melibatkan unit terkait.',
                    'Program K3 Puskesmas dilaksanakan sesuai rencana kerja.',
                    'Puskesmas melaksanakan pemeriksaan kesehatan berkala bagi SDM minimal 1 (satu) tahun sekali.',
                    'Puskesmas membudayakan perilaku hidup bersih dan sehat (PHBS) di tempat kerja, termasuk larangan merokok.',
                ],
            ],
            'B' => [
                'nama' => 'Pencatatan dan Pelaporan Insiden K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab IV; UU No. 17 Tahun 2023 tentang Kesehatan; ILO (2013) tentang underreporting kecelakaan kerja',
                'items' => [
                    // -- item lama (jangan diubah) --
                    'Pelaporan K3 di Puskesmas masih dilakukan secara manual (kertas) atau spreadsheet terpisah.',
                    'Data pelaporan K3 antar unit/poli di Puskesmas belum terintegrasi dalam satu sistem.',
                    'Puskesmas belum memiliki dashboard/monitoring insiden K3 secara real-time.',
                    'Puskesmas pernah mengalami keterlambatan, kehilangan, atau duplikasi data laporan K3.',
                    'Analisis tren kecelakaan kerja dan PAK sulit dilakukan dengan sistem pelaporan yang berjalan saat ini.',
                    // -- item baru (revisi) --
                    'Puskesmas melakukan pencatatan dan pelaporan penyelenggaraan K3 secara tahunan (seluruh pelaksanaan kegiatan K3 selama 1 tahun).',
                    'Laporan semester mencakup jumlah SDM, SDM sakit, kasus penyakit umum, dugaan PAK, PAK, kecelakaan akibat kerja, kejadian hampir celaka (near miss), dan hari absen karena sakit.',
                    'Laporan K3 diketahui/ditandatangani oleh Kepala Puskesmas dan Ketua Tim/Penanggung Jawab K3.',
                    'Puskesmas melakukan investigasi pada setiap kejadian penyakit akibat kerja dan kecelakaan akibat kerja.',
                    'Puskesmas mencatat kejadian hampir celaka (near miss) pada SDM.',
                    'Insiden dan kecelakaan kerja dicatat dan dilaporkan.',
                ],
            ],
            'C' => [
                'nama' => 'Identifikasi dan Pengendalian Risiko Kerja',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab III (bahaya biologi, kimia, fisik, ergonomi, psikososial); ISO 45001:2018',
                'items' => [
                    // -- item lama (jangan diubah) --
                    'Puskesmas melakukan identifikasi bahaya biologis (agen infeksius) secara berkala.',
                    'Puskesmas melakukan identifikasi bahaya kimia (disinfektan/obat) secara berkala.',
                    'Puskesmas melakukan identifikasi bahaya fisik dan ergonomi (postur kerja) secara berkala.',
                    'Puskesmas menyediakan Alat Pelindung Diri (APD) yang memadai bagi tenaga kesehatan.',
                    'Puskesmas memiliki mekanisme pemantauan risiko psikososial (stres/beban kerja) pegawai.',
                    // -- item baru (revisi) --
                    'Puskesmas melakukan penilaian risiko (analisis dan evaluasi tingkat risiko tinggi, sedang, rendah) terhadap bahaya yang telah diidentifikasi.',
                    'Puskesmas memiliki peta/daftar risiko K3 untuk setiap ruangan/unit pelayanan.',
                    'Puskesmas menerapkan prinsip ergonomi (penanganan beban manual, postur kerja, gerakan berulang, pengaturan shift dan durasi kerja, tata letak ruang kerja).',
                    'Puskesmas melakukan pengawasan dan pemeliharaan sarana dan prasarana (gedung, listrik, pencahayaan, grounding, ventilasi, sanitasi) dari aspek K3.',
                    'Puskesmas mengelola peralatan medis dari aspek K3 (inventaris, penandaan, uji fungsi, kalibrasi, dan pemeliharaan berkala).',
                    'Puskesmas melakukan simulasi kondisi darurat/bencana dan simulasi kebakaran minimal 1 (satu) tahun sekali pada setiap gedung.',
                    'SDM Puskesmas mampu menggunakan APAR dan kondisi/masa pakai APAR dipantau minimal 2 (dua) kali dalam setahun.',
                    'Puskesmas mengelola bahan dan limbah B3 (inventarisasi, penyimpanan, lembar data keselamatan SDS/MSDS, spill kit, rambu/simbol, SOP) sesuai ketentuan.',
                    'Penggunaan APD diterapkan sesuai standar.',
                    'Terdapat prosedur tanggap darurat yang diterapkan.',
                ],
            ],
            'D' => [
                'nama' => 'Pemantauan, Evaluasi, dan Pemanfaatan Data K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab V; ISO 31000; Obasi & Benson (2025); Benson dkk. (2024)',
                'items' => [
                    // -- item lama (jangan diubah) --
                    'Puskesmas melakukan evaluasi program K3 secara berkala (bulanan/triwulan).',
                    'Hasil pencatatan K3 dimanfaatkan sebagai dasar pengambilan keputusan manajemen.',
                    'Puskesmas mengalami kendala dalam menganalisis data K3 akibat sistem yang belum digital.',
                    // -- item baru (revisi) --
                    'Puskesmas melakukan inspeksi tempat kerja secara teratur oleh Tim/Penanggung Jawab K3 menggunakan daftar periksa (checklist).',
                    'Puskesmas melakukan penilaian internal K3 oleh penanggung jawab paling sedikit setiap 6 (enam) bulan sekali.',
                    'Puskesmas mengevaluasi capaian program K3 minimal 1 (satu) kali dalam setahun berdasarkan rencana kegiatan tahunan.',
                    'Puskesmas melakukan peninjauan kinerja K3 setiap tahun terhadap kebijakan, perencanaan, pelaksanaan rencana, serta pemantauan dan evaluasi.',
                    'Puskesmas menetapkan indikator kinerja K3 yang akan dicapai setiap tahun.',
                    'Hasil peninjauan dan evaluasi ditindaklanjuti dengan tindakan perbaikan dan peningkatan kinerja K3.',
                    'Rekomendasi hasil audit/penilaian internal K3 ditindaklanjuti.',
                    'Perubahan kebijakan K3 dilakukan berdasarkan hasil evaluasi.',
                ],
            ],
            'E' => [
                'nama' => 'Kebutuhan Pengembangan Sistem Digital Pelaporan K3',
                'rujukan' => 'Laudon & Laudon (2018); Obasi & Benson (2025); Cetak Biru Transformasi Digital Kesehatan Kemenkes RI 2024-2029',
                'items' => [
                    // -- item lama (jangan diubah, urutan/nomor dipertahankan persis) --
                    // Catatan: item nomor 1 di bawah ini diminta dihapus klien. Teksnya
                    // sengaja TETAP dicantumkan di posisi aslinya (bukan dihapus dari
                    // array) supaya nomor item-item lain di bawahnya tidak ikut bergeser
                    // dan bentrok dengan unique constraint (kategori_kode, nomor). Setelah
                    // loop di bawah, item ini dinonaktifkan lewat kolom `aktif`.
                    'Puskesmas membutuhkan aplikasi digital untuk mempermudah pencatatan dan pelaporan K3.',
                    'Puskesmas membutuhkan fitur notifikasi/monitoring tindak lanjut insiden K3 secara real-time.',
                    'Puskesmas membutuhkan sistem yang dapat merekam data risiko berdasarkan jenis bahaya (biologis, kimia, fisik, ergonomi, psikososial) secara terpisah.',
                    'Puskesmas bersedia menjadi lokasi uji coba aplikasi pelaporan K3 berbasis mobile dan web apabila disediakan.',
                    // -- item baru (revisi) --
                    'Puskesmas membutuhkan aplikasi yang dapat menghasilkan laporan K3 semester (kasus K3, PAK, kecelakaan kerja, near miss, hari absen) secara otomatis.',
                    'Puskesmas membutuhkan aplikasi yang dapat merekap laporan tahunan seluruh kegiatan K3 Puskesmas.',
                    'Puskesmas membutuhkan sistem pelaporan berjenjang yang langsung terhubung ke Dinas Kesehatan Kabupaten/Kota.',
                ],
            ],
        ];

        foreach ($kategoriList as $kode => $kategori) {
            foreach ($kategori['items'] as $index => $pertanyaan) {
                QuestionnaireItem::updateOrCreate(
                    ['kategori_kode' => $kode, 'pertanyaan' => $pertanyaan],
                    [
                        'kategori' => $kategori['nama'],
                        'nomor' => $index + 1,
                        'rujukan' => $kategori['rujukan'],
                        'aktif' => true,
                    ],
                );
            }
        }

        // Item yang diminta dihapus klien: nonaktifkan saja (jangan delete fisik)
        // supaya jawaban historis yang sudah tersimpan tetap bisa dibuka apa adanya.
        QuestionnaireItem::where('kategori_kode', 'E')
            ->where('pertanyaan', 'Puskesmas membutuhkan aplikasi digital untuk mempermudah pencatatan dan pelaporan K3.')
            ->update(['aktif' => false]);
    }
}
