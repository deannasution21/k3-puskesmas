<?php

namespace Database\Seeders;

use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PuskesmasSeeder extends Seeder
{
    /**
     * Data resmi 23 Puskesmas se-Kota Pontianak (data-puskesmas-pontianak.pdf).
     * Setiap Puskesmas otomatis dibuatkan 1 akun login (username = slug nama singkat).
     * Password default sementara, wajib diganti oleh Dinas setelah login pertama.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('puskesmas123');

        $puskesmasList = [
            ['nama' => 'Kampung Bali', 'alamat' => 'Jl. Jend Urip No. 79 Ptk Rt 02 Rw 04, Kel. Tengah, Kec. Pontianak Kota', 'kepala_puskesmas' => 'drg. Popong Solihat', 'no_hp' => '0561-738554', 'email' => 'puskesmaskampungbali@gmail.com', 'kode_puskesmas' => '1000080878'],
            ['nama' => 'Alianyang', 'alamat' => 'Jl. Pangeran Nata Kusuma, Kel. Sui Bangkong, Kec. Pontianak Kota', 'kepala_puskesmas' => 'drg. Alfonza Nunuk Utari', 'no_hp' => '0561-760407', 'email' => 'alianyang.pnkkota@gmail.com', 'kode_puskesmas' => '1000080775'],
            ['nama' => 'Pal Tiga', 'alamat' => 'Jl. H. Rais Arahman Rw. 25 Rt. 01, Kelurahan Sungai Jawi, Kec. Pontianak Kota', 'kepala_puskesmas' => 'Riris Ariestiasany, S.Farm., Apt', 'no_hp' => '0561-774151', 'email' => 'pkm.pal3@gmail.com', 'kode_puskesmas' => '1000080713'],
            ['nama' => 'Karya Mulia', 'alamat' => 'Jl. Ampera, Kel. Sungai Jawi, Kec. Pontianak Kota', 'kepala_puskesmas' => 'Patricia Ami Dameuli, SKM', 'no_hp' => '0561-6590702', 'email' => 'puskkaryamulia@gmail.com', 'kode_puskesmas' => '1000080799'],
            ['nama' => 'Perumnas I', 'alamat' => 'Jl. M Yusuf Komp Perum I No. 1 Rt 01/26, Sungai Jawi Luar, Kec. Pontianak Barat', 'kepala_puskesmas' => 'dr. Insanul Kamilah', 'no_hp' => '0561-771494', 'email' => 'pkmperumnas1@gmail.com', 'kode_puskesmas' => '1000080921'],
            ['nama' => 'Perumnas II', 'alamat' => 'Jl. Hasyim Ahmad Rt 03/06, Kelurahan Sungai Beliung, Kec. Pontianak Barat', 'kepala_puskesmas' => 'Titin Widyaningsih, SKM, S.Tr Keb', 'no_hp' => '0561-776043', 'email' => 'puskesmas.perumii@gmail.com', 'kode_puskesmas' => '1000080816'],
            ['nama' => 'Kom Yos Sudarso', 'alamat' => 'Jl. Tabrani Ahmad (Komp. Perkantoran Camat Kecamatan Pontianak Barat), Kel. Sungai Jawi Dalam, Kec. Pontianak Barat', 'kepala_puskesmas' => 'Mirtha Widiarty, S.ST, M.Keb', 'no_hp' => '0561-774144/8128434', 'email' => 'puskesmaskomyos62@gmail.com', 'kode_puskesmas' => '1000080787'],
            ['nama' => 'Pal Lima', 'alamat' => 'Jl. Husein Hamzah Gg. Mufakat Rt 03/03 Pal 5, Kec. Pontianak Barat', 'kepala_puskesmas' => 'dr. Sri Samariah', 'no_hp' => '0561-778957', 'email' => 'puskesmas.pal5@gmail.com', 'kode_puskesmas' => '11000080804'],
            ['nama' => 'Gang Sehat', 'alamat' => 'Jl. Tani Makmur Rt 02/29 Parit Tokaya, Kec. Pontianak Selatan', 'kepala_puskesmas' => 'dr. Astari Nurtilawati', 'no_hp' => '0561-8102656', 'email' => 'puskesmasggsehat@gmail.com', 'kode_puskesmas' => '1000080866'],
            ['nama' => 'Purnama', 'alamat' => 'Jl. Letjen Sutoyo, Kelurahan Parit Tokaya, Kec. Pontianak Selatan', 'kepala_puskesmas' => 'Sumini, SKM, M.Kes', 'no_hp' => '0561-768459', 'email' => 'puskesmaspurnama1992@gmail.com', 'kode_puskesmas' => '1000080854'],
            ['nama' => 'Kampung Bangka', 'alamat' => 'Jl. Abdul Rahman Saleh (BLKI) Blok Naisyah No. 2 Rt 01/Rw 04, Kelurahan Bangka Belitung Laut, Kec. Pontianak Tenggara', 'kepala_puskesmas' => 'dr. Mery Lolita', 'no_hp' => '0561-762362', 'email' => 'uptdpuskesmas.kpbangka@gmail.com', 'kode_puskesmas' => '1000080828'],
            ['nama' => 'Parit Haji Husin II', 'alamat' => 'Jl. Parit H. Husein II Komp Pemda Jalur 2/3, Kel. Bansir Darat, Kec. Pontianak Tenggara', 'kepala_puskesmas' => 'Mustika Corry Kurniawaty', 'no_hp' => '0561-712750', 'email' => 'puskesmasparisdua@gmail.com', 'kode_puskesmas' => '1000080751'],
            ['nama' => 'Saigon', 'alamat' => 'Jl. Tanjung Raya II, Kec. Pontianak Timur', 'kepala_puskesmas' => 'dr. Mardiah', 'no_hp' => '0561-6593859', 'email' => 'pusk.saigon@yahoo.co.id', 'kode_puskesmas' => '1060216'],
            ['nama' => 'Kampung Dalam', 'alamat' => 'Jl. Tanjung Raya I, Kelurahan Dalam Bugis, Kec. Pontianak Timur', 'kepala_puskesmas' => 'dr. Mishermaliyani', 'no_hp' => '0561-570919', 'email' => 'pusk.kpdalam@gmail.com', 'kode_puskesmas' => '1060215'],
            ['nama' => 'Tambelan Sampit', 'alamat' => 'Jl. H. Abu Naim Rt 04/01, Tambelan Sampit, Kec. Pontianak Timur', 'kepala_puskesmas' => 'dr. Mishermaliyani', 'no_hp' => '0561-6593553', 'email' => 'tambelansampit123@gmail.com', 'kode_puskesmas' => '1060220'],
            ['nama' => 'Banjar Serasan', 'alamat' => 'Jl. Tanjung Harapan Rt 03/02, Banjar Serasan, Kec. Pontianak Timur', 'kepala_puskesmas' => 'Rusnaini, SKM, MPH', 'no_hp' => '0561-6593050', 'email' => 'puskesmasbanjarserasan@gmail.com', 'kode_puskesmas' => '1060219'],
            ['nama' => 'Tanjung Hulu', 'alamat' => 'Jl. YM Sabran Rt 03/12, Tanjung Hulu, Kec. Pontianak Timur', 'kepala_puskesmas' => 'Eko Budi Santoso, SKM, MPH', 'no_hp' => '0561-6592177', 'email' => 'puskesmas.tanjunghulu@gmail.com', 'kode_puskesmas' => '1060218'],
            ['nama' => 'Parit Mayor', 'alamat' => 'Jl. Tanjung Raya II Gg. Nusa Indah, Kel. Parit Mayor, Kec. Pontianak Timur', 'kepala_puskesmas' => 'Sulistiyo Adhi Purnomo, S.Gz', 'no_hp' => '0561-6593095', 'email' => 'bludpuskesparma@gmail.com', 'kode_puskesmas' => '1060217'],
            ['nama' => 'Siantan Hilir', 'alamat' => 'Jl. Telok Sahang 1, Kelurahan Siantan Hilir, Kec. Pontianak Utara', 'kepala_puskesmas' => 'Tri Lestari, S.ST', 'no_hp' => '0561-881212', 'email' => 'puskesmas_siantanhilir@yahoo.co.id', 'kode_puskesmas' => '1000080701'],
            ['nama' => 'Siantan Tengah', 'alamat' => 'Jl. Selat Sumba No. 40 Rt 4/15, Siantan Tengah, Kec. Pontianak Utara', 'kepala_puskesmas' => 'Sudarmanto, SKM, MPH', 'no_hp' => '0561-8124306', 'email' => 'pkmsiantantengah@gmail.com', 'kode_puskesmas' => '1000080907'],
            ['nama' => 'Siantan Hulu', 'alamat' => 'Jl. Parit Pangeran Rt 04/06, Siantan Hulu, Kec. Pontianak Utara', 'kepala_puskesmas' => 'Eka Wahyuni, S.Kep, Ners.', 'no_hp' => '0561-882679', 'email' => 'puskesmassiantanhulu@gmail.com', 'kode_puskesmas' => '1000080725'],
            ['nama' => 'Telaga Biru', 'alamat' => 'Jl. 28 Oktober Gg. Marga Utama No. 1 Rt 03/14, Kec. Pontianak Utara', 'kepala_puskesmas' => 'dr. Toni Mas Irwanda', 'no_hp' => '0561-884949', 'email' => 'puskesmastelagabiru@gmail.com', 'kode_puskesmas' => '1000080763'],
            ['nama' => 'Khatulistiwa', 'alamat' => 'Jl. Khatulistiwa Rt 03/09, Batu Layang, Kec. Pontianak Utara', 'kepala_puskesmas' => 'Hakimah, S.ST', 'no_hp' => '0561-884891', 'email' => 'pusk.khatulistiwa@gmail.com', 'kode_puskesmas' => '1000080737'],
        ];

        foreach ($puskesmasList as $data) {
            $puskesmas = Puskesmas::updateOrCreate(
                ['nama' => 'UPT Puskesmas '.$data['nama']],
                [
                    'alamat' => $data['alamat'],
                    'kepala_puskesmas' => $data['kepala_puskesmas'],
                    'no_hp' => $data['no_hp'],
                    'email' => $data['email'],
                    'kode_puskesmas' => $data['kode_puskesmas'],
                ],
            );

            $username = Str::slug($data['nama'], '');

            User::updateOrCreate(
                ['puskesmas_id' => $puskesmas->id],
                [
                    'username' => $username,
                    'password' => $defaultPassword,
                    'role' => 'puskesmas',
                ],
            );
        }
    }
}
