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
            ['nama' => 'Kampung Bali', 'alamat' => 'Jl. Jend Urip No. 79 Ptk Rt 02 Rw 04, Kel. Tengah, Kec. Pontianak Kota'],
            ['nama' => 'Alianyang', 'alamat' => 'Jl. Pangeran Nata Kusuma, Kel. Sui Bangkong, Kec. Pontianak Kota'],
            ['nama' => 'Pal Tiga', 'alamat' => 'Jl. H. Rais Arahman Rw. 25 Rt. 01, Kelurahan Sungai Jawi, Kec. Pontianak Kota'],
            ['nama' => 'Karya Mulia', 'alamat' => 'Jl. Ampera, Kel. Sungai Jawi, Kec. Pontianak Kota'],
            ['nama' => 'Perumnas I', 'alamat' => 'Jl. M Yusuf Komp Perum I No. 1 Rt 01/26, Sungai Jawi Luar, Kec. Pontianak Barat'],
            ['nama' => 'Perumnas II', 'alamat' => 'Jl. Hasyim Ahmad Rt 03/06, Kelurahan Sungai Beliung, Kec. Pontianak Barat'],
            ['nama' => 'Kom Yos Sudarso', 'alamat' => 'Jl. Tabrani Ahmad (Komp. Perkantoran Camat Kecamatan Pontianak Barat), Kel. Sungai Jawi Dalam, Kec. Pontianak Barat'],
            ['nama' => 'Pal Lima', 'alamat' => 'Jl. Husein Hamzah Gg. Mufakat Rt 03/03 Pal 5, Kec. Pontianak Barat'],
            ['nama' => 'Gang Sehat', 'alamat' => 'Jl. Tani Makmur Rt 02/29 Parit Tokaya, Kec. Pontianak Selatan'],
            ['nama' => 'Purnama', 'alamat' => 'Jl. Letjen Sutoyo, Kelurahan Parit Tokaya, Kec. Pontianak Selatan'],
            ['nama' => 'Kampung Bangka', 'alamat' => 'Jl. Abdul Rahman Saleh (BLKI) Blok Naisyah No. 2 Rt 01/Rw 04, Kelurahan Bangka Belitung Laut, Kec. Pontianak Tenggara'],
            ['nama' => 'Parit Haji Husin II', 'alamat' => 'Jl. Parit H. Husein II Komp Pemda Jalur 2/3, Kel. Bansir Darat, Kec. Pontianak Tenggara'],
            ['nama' => 'Saigon', 'alamat' => 'Jl. Tanjung Raya II, Kec. Pontianak Timur'],
            ['nama' => 'Kampung Dalam', 'alamat' => 'Jl. Tanjung Raya I, Kelurahan Dalam Bugis, Kec. Pontianak Timur'],
            ['nama' => 'Tambelan Sampit', 'alamat' => 'Jl. H. Abu Naim Rt 04/01, Tambelan Sampit, Kec. Pontianak Timur'],
            ['nama' => 'Banjar Serasan', 'alamat' => 'Jl. Tanjung Harapan Rt 03/02, Banjar Serasan, Kec. Pontianak Timur'],
            ['nama' => 'Tanjung Hulu', 'alamat' => 'Jl. YM Sabran Rt 03/12, Tanjung Hulu, Kec. Pontianak Timur'],
            ['nama' => 'Parit Mayor', 'alamat' => 'Jl. Tanjung Raya II Gg. Nusa Indah, Kel. Parit Mayor, Kec. Pontianak Timur'],
            ['nama' => 'Siantan Hilir', 'alamat' => 'Jl. Telok Sahang 1, Kelurahan Siantan Hilir, Kec. Pontianak Utara'],
            ['nama' => 'Siantan Tengah', 'alamat' => 'Jl. Selat Sumba No. 40 Rt 4/15, Siantan Tengah, Kec. Pontianak Utara'],
            ['nama' => 'Siantan Hulu', 'alamat' => 'Jl. Parit Pangeran Rt 04/06, Siantan Hulu, Kec. Pontianak Utara'],
            ['nama' => 'Telaga Biru', 'alamat' => 'Jl. 28 Oktober Gg. Marga Utama No. 1 Rt 03/14, Kec. Pontianak Utara'],
            ['nama' => 'Khatulistiwa', 'alamat' => 'Jl. Khatulistiwa Rt 03/09, Batu Layang, Kec. Pontianak Utara'],
        ];

        foreach ($puskesmasList as $data) {
            $puskesmas = Puskesmas::updateOrCreate(
                ['nama' => 'UPT Puskesmas '.$data['nama']],
                ['alamat' => $data['alamat']],
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
