<?php

namespace Database\Seeders;

use App\Models\QuestionnaireItem;
use Illuminate\Database\Seeder;

class QuestionnaireItemSeeder extends Seeder
{
    /**
     * Data diambil apa adanya dari Lampiran 6 - Kuesioner Penelitian.
     */
    public function run(): void
    {
        $kategoriList = [
            'A' => [
                'nama' => 'Kebijakan, Organisasi, dan SDM K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab II; UU No. 1 Tahun 1970 Pasal 3 dan 9',
                'items' => [
                    'Puskesmas memiliki kebijakan/program K3 tertulis.',
                    'Puskesmas memiliki Tim/Petugas K3 yang ditunjuk secara resmi (Surat Keputusan).',
                    'Puskesmas pernah memberikan pelatihan/sosialisasi K3 kepada pegawai dalam 1 tahun terakhir.',
                    'Puskesmas memiliki SOP pencatatan dan pelaporan kecelakaan kerja serta penyakit akibat kerja (PAK).',
                ],
            ],
            'B' => [
                'nama' => 'Pencatatan dan Pelaporan Insiden K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab IV; UU No. 17 Tahun 2023 tentang Kesehatan; ILO (2013) tentang underreporting kecelakaan kerja',
                'items' => [
                    'Pelaporan K3 di Puskesmas masih dilakukan secara manual (kertas) atau spreadsheet terpisah.',
                    'Data pelaporan K3 antar unit/poli di Puskesmas belum terintegrasi dalam satu sistem.',
                    'Puskesmas belum memiliki dashboard/monitoring insiden K3 secara real-time.',
                    'Puskesmas pernah mengalami keterlambatan, kehilangan, atau duplikasi data laporan K3.',
                    'Analisis tren kecelakaan kerja dan PAK sulit dilakukan dengan sistem pelaporan yang berjalan saat ini.',
                ],
            ],
            'C' => [
                'nama' => 'Identifikasi dan Pengendalian Risiko Kerja',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab III (bahaya biologi, kimia, fisik, ergonomi, psikososial); ISO 45001:2018',
                'items' => [
                    'Puskesmas melakukan identifikasi bahaya biologis (agen infeksius) secara berkala.',
                    'Puskesmas melakukan identifikasi bahaya kimia (disinfektan/obat) secara berkala.',
                    'Puskesmas melakukan identifikasi bahaya fisik dan ergonomi (postur kerja) secara berkala.',
                    'Puskesmas menyediakan Alat Pelindung Diri (APD) yang memadai bagi tenaga kesehatan.',
                    'Puskesmas memiliki mekanisme pemantauan risiko psikososial (stres/beban kerja) pegawai.',
                ],
            ],
            'D' => [
                'nama' => 'Pemantauan, Evaluasi, dan Pemanfaatan Data K3',
                'rujukan' => 'Permenkes RI No. 52 Tahun 2018 Bab V; ISO 31000; Obasi & Benson (2025); Benson dkk. (2024)',
                'items' => [
                    'Puskesmas melakukan evaluasi program K3 secara berkala (bulanan/triwulan).',
                    'Hasil pencatatan K3 dimanfaatkan sebagai dasar pengambilan keputusan manajemen.',
                    'Puskesmas mengalami kendala dalam menganalisis data K3 akibat sistem yang belum digital.',
                ],
            ],
            'E' => [
                'nama' => 'Kebutuhan Pengembangan Sistem Digital Pelaporan K3',
                'rujukan' => 'Laudon & Laudon (2018); Obasi & Benson (2025); Cetak Biru Transformasi Digital Kesehatan Kemenkes RI 2024-2029',
                'items' => [
                    'Puskesmas membutuhkan aplikasi digital untuk mempermudah pencatatan dan pelaporan K3.',
                    'Puskesmas membutuhkan fitur notifikasi/monitoring tindak lanjut insiden K3 secara real-time.',
                    'Puskesmas membutuhkan sistem yang dapat merekam data risiko berdasarkan jenis bahaya (biologis, kimia, fisik, ergonomi, psikososial) secara terpisah.',
                    'Puskesmas bersedia menjadi lokasi uji coba aplikasi pelaporan K3 berbasis mobile dan web apabila disediakan.',
                ],
            ],
        ];

        foreach ($kategoriList as $kode => $kategori) {
            foreach ($kategori['items'] as $index => $pertanyaan) {
                QuestionnaireItem::updateOrCreate(
                    ['kategori_kode' => $kode, 'nomor' => $index + 1],
                    [
                        'kategori' => $kategori['nama'],
                        'pertanyaan' => $pertanyaan,
                        'rujukan' => $kategori['rujukan'],
                    ],
                );
            }
        }
    }
}
