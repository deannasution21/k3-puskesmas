<?php

use App\Models\Puskesmas;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:seed-once', function () {
    if (Puskesmas::count() > 0) {
        $this->comment('Data puskesmas sudah ada, lewati seeding.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->call('db:seed', ['--class' => 'DummyHistorySeeder', '--force' => true]);
    $this->comment('Seeding awal selesai (master data + dummy history 3 bulan untuk demo).');
})->purpose('Seed database sekali saat pertama kali deploy — aman dijalankan berulang di setiap start container');
