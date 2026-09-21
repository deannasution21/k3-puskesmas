<?php

namespace Database\Seeders;

use App\Models\ObservationItem;
use Illuminate\Database\Seeder;

class ObservationItemSeeder extends Seeder
{
    /**
     * Data diambil apa adanya dari Lampiran 7 - Observasi Sarana dan Prasarana K3 Puskesmas.
     * Skala kondisi section A-H: Baik/Buruk. Section I-N: Baik/Rusak Ringan/Rusak Berat.
     */
    public function run(): void
    {
        $sumberPermenkes = 'Permenkes No. 52 Tahun 2018 dan Permenkes No. 19 Tahun 2024';
        $sumberAkreditasi = 'Instrumen Akreditasi Puskesmas 2021 & 2023';

        $kategoriList = [
            'A' => [
                'nama' => 'Proteksi Kebakaran dan Kedaruratan',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'APAR tersedia, sesuai kapasitas/kebutuhan, mudah diakses, dan dalam kondisi baik',
                    'Sprinkler tersedia sesuai kebutuhan bangunan',
                    'Heat detector/smoke detector tersedia dan berfungsi',
                    'Sistem alarm kebakaran/bencana tersedia dan berfungsi',
                    'Tangga darurat tersedia dan memenuhi persyaratan keselamatan (bila diperlukan)',
                    'Pintu darurat tersedia, tahan api, dan memiliki tanda EXIT (bila diperlukan)',
                    'Jalur evakuasi tersedia, jelas, dan tidak terhalang',
                    'Rambu keselamatan dan tanda pintu/jalur darurat tersedia',
                    'Titik kumpul (assembly point) tersedia dan ditandai',
                    'Lift memenuhi standar keselamatan (bila tersedia)',
                ],
            ],
            'B' => [
                'nama' => 'Bangunan dan Utilitas Umum',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Struktur bangunan/gedung dalam kondisi aman dan layak digunakan',
                    'Atap, langit-langit, dinding, lantai, dan jendela dalam kondisi baik',
                    'Instalasi listrik tersedia dan berfungsi dengan aman',
                    'Sistem pencahayaan ruangan memadai',
                    'Sistem grounding/pembumian tersedia dan berfungsi',
                    'Ventilasi alami dan/atau buatan memadai',
                    'Sistem sanitasi, air bersih, dan pembuangan air kotor/limbah tersedia dan berfungsi',
                    'Tempat penampungan sementara sampah tersedia',
                    'Toilet tersedia, higienis, dan jumlahnya memadai',
                    'Tempat parkir tersedia dan memadai',
                    'Tangga bangunan aman digunakan (untuk gedung bertingkat)',
                ],
            ],
            'C' => [
                'nama' => 'Alat Pelindung Diri (APD)',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Penutup kepala tersedia sesuai kebutuhan risiko',
                    'Pelindung pendengaran (ear muff/ear plug) tersedia sesuai kebutuhan risiko',
                    'Kacamata pelindung/safety goggles tersedia',
                    'Pelindung wajah/face shield tersedia',
                    'Masker/respirator tersedia sesuai tingkat risiko',
                    'Sarung tangan tersedia sesuai jenis pekerjaan',
                    'Pelindung kaki/sepatu keselamatan tersedia',
                    'Jas laboratorium tersedia sesuai kebutuhan',
                    'Apron tersedia sesuai kebutuhan',
                    'Coverall tersedia sesuai kebutuhan',
                ],
            ],
            'D' => [
                'nama' => 'Sarana dan Prasarana K3 Laboratorium',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Wastafel dilengkapi sabun/disinfektan kulit dan air mengalir',
                    'Lemari asam (fume hood) dengan sistem exhaust tersedia sesuai kebutuhan',
                    'Pipetting aid/rubber bulb tersedia sesuai kebutuhan',
                    'Safety box/kontainer khusus benda tajam tersedia',
                    'Emergency shower tersedia sesuai risiko',
                    'Kabinet keamanan biologis (biosafety cabinet) tersedia sesuai kebutuhan',
                    'Eye wash/body wash tersedia sesuai risiko',
                ],
            ],
            'E' => [
                'nama' => 'Pengelolaan Bahan dan Limbah B3',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Daftar inventaris bahan dan limbah B3 tersedia',
                    'Tempat penyimpanan/pewadahan B3 sesuai karakteristik bahan',
                    'Lembar Data Keselamatan Bahan (MSDS/SDS) tersedia',
                    'Spill kit untuk penanganan tumpahan/kebocoran B3 tersedia',
                    'Rambu dan simbol B3 tersedia',
                    'APD tersedia sesuai karakteristik bahan dan limbah B3',
                ],
            ],
            'F' => [
                'nama' => 'Pengelolaan Limbah Domestik',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Tempat sampah terpilah organik dan nonorganik tersedia dan dilengkapi tutup',
                    'Kantong plastik pelapis tempat sampah tersedia sesuai jenis sampah',
                    'APD petugas kebersihan (masker, sarung tangan, sepatu boots) tersedia',
                ],
            ],
            'G' => [
                'nama' => 'Rantai Dingin Vaksin (Cold Chain)',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Vaccine refrigerator tersedia dan berfungsi',
                    'Cold box tersedia dan layak digunakan',
                    'Vaccine carrier tersedia dan layak digunakan',
                ],
            ],
            'H' => [
                'nama' => 'Sarana Pendukung Ergonomi',
                'skala' => 'baik_buruk',
                'sumber' => $sumberPermenkes,
                'items' => [
                    'Kursi kerja memiliki sandaran dan tinggi yang dapat disesuaikan',
                    'Footrest/pijakan kaki tersedia sesuai kebutuhan',
                    'Document holder/penyangga dokumen tersedia sesuai kebutuhan',
                    'Troli tersedia untuk mengangkut barang berat/tinggi',
                    'Lift barang tersedia sesuai kebutuhan (bila tersedia)',
                ],
            ],
            'I' => [
                'nama' => 'Bangunan dan Aksesibilitas',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'Kondisi bangunan dan ruangan Puskesmas layak dan aman digunakan',
                    'Pencahayaan ruangan memadai',
                    'Ventilasi/penghawaan ruangan memadai',
                    'Kamar mandi/WC tersedia dan dalam kondisi layak',
                    'Akses masuk/jalur kursi roda (ramp) tersedia',
                    'Handrail/pegangan tangan tersedia pada area yang membutuhkan',
                    'Tempat parkir tersedia dan memadai',
                ],
            ],
            'J' => [
                'nama' => 'Sistem Utilitas dan Prasarana Pendukung',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'Sistem kelistrikan tersedia dan berfungsi',
                    'Sumber listrik cadangan (genset/UPS) tersedia dan dapat digunakan',
                    'Sistem air bersih tersedia dan berfungsi',
                    'Sistem sanitasi dan hygiene tersedia dan terpelihara',
                    'Sistem komunikasi (telepon/internet) tersedia',
                    'Sistem gas medik/oksigen tersedia sesuai kebutuhan pelayanan',
                    'Cadangan gas medik tersedia sesuai kebutuhan',
                    'Sistem proteksi petir tersedia dan berfungsi',
                    'Sistem pengelolaan limbah cair/IPAL tersedia dan berfungsi',
                ],
            ],
            'K' => [
                'nama' => 'Keselamatan, Kebakaran, dan Evakuasi',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'APAR tersedia, mudah diakses, dan berfungsi',
                    'Sistem deteksi dini dan/atau alarm kebakaran tersedia',
                    'Jalur evakuasi tersedia, jelas, dan tidak terhalang',
                    'Tanda/rambu jalur dan pintu darurat tersedia',
                    'Titik kumpul (assembly point) tersedia dan ditandai',
                    'Rambu larangan merokok tersedia',
                    'Rambu keselamatan/K3 tersedia pada area yang diperlukan',
                    'Kotak P3K tersedia dan mudah diakses',
                ],
            ],
            'L' => [
                'nama' => 'Pengelolaan Limbah dan B3',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'Tempat sampah medis/infeksius dan nonmedis tersedia secara terpisah',
                    'TPS limbah B3 tersedia dan sesuai ketentuan',
                    'Spill kit untuk penanganan tumpahan B3 tersedia',
                    'Pengelolaan dan pemilahan limbah B3 dilaksanakan sesuai prosedur',
                ],
            ],
            'M' => [
                'nama' => 'Peralatan Kesehatan dan Pemeliharaan',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'Peralatan kesehatan utama tersedia sesuai kebutuhan pelayanan/ASPAK',
                    'Inventarisasi peralatan kesehatan dilakukan',
                    'Inspeksi dan pengujian peralatan kesehatan dilakukan secara berkala',
                    'Pemeliharaan peralatan kesehatan dilakukan secara berkala',
                    'Kalibrasi peralatan kesehatan dilakukan secara berkala',
                ],
            ],
            'N' => [
                'nama' => 'Manajemen Fasilitas dan Keselamatan (MFK)',
                'skala' => 'baik_rusak_ringan_rusak_berat',
                'sumber' => $sumberAkreditasi,
                'items' => [
                    'Terdapat petugas/PJ yang bertanggung jawab terhadap MFK',
                    'Program MFK ditetapkan berdasarkan identifikasi risiko',
                    'Identifikasi area berisiko dilakukan',
                    'Inspeksi fasilitas bangunan, prasarana, dan peralatan dilakukan secara berkala',
                    'Simulasi tanggap darurat/kode darurat dilakukan secara berkala',
                    'Program pencegahan dan penanggulangan kebakaran dilaksanakan',
                    'Simulasi dan evaluasi kesiapsiagaan bencana/kebakaran dilakukan secara berkala',
                    'Program MFK dievaluasi dan ditindaklanjuti',
                ],
            ],
        ];

        foreach ($kategoriList as $kode => $kategori) {
            foreach ($kategori['items'] as $index => $itemTeks) {
                ObservationItem::updateOrCreate(
                    ['kategori_kode' => $kode, 'nomor' => $index + 1],
                    [
                        'kategori' => $kategori['nama'],
                        'item_teks' => $itemTeks,
                        'skala_kondisi' => $kategori['skala'],
                        'sumber' => $kategori['sumber'],
                    ],
                );
            }
        }
    }
}
