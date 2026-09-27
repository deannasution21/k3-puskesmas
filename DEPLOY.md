# Deploy ke Railway

Project ini sudah disiapkan untuk deploy sebagai satu service (frontend Vue
digabung ke dalam Laravel lewat `public/spa`), jadi tidak perlu setup CORS
atau domain terpisah. Build dilakukan lewat `Dockerfile` di root repo.

## 1. Push ke GitHub

Pastikan `Dockerfile`, `railway.json`, `docker/start.sh`, dan `.dockerignore`
di root repo ikut ter-commit.

## 2. Buat project di Railway

1. Buka [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo**
2. Pilih repo ini. Railway akan otomatis mendeteksi `Dockerfile` (dikonfirmasi
   lewat `railway.json`) dan build dari root repo.

## 3. Tambahkan database MySQL

1. Di dalam project Railway yang sama, klik **New** → **Database** → **Add MySQL**
2. Buka tab **Variables** dari plugin MySQL tersebut untuk melihat kredensialnya
   (host, port, database, username, password).

## 4. Set environment variable di service backend

Buka service hasil deploy dari Dockerfile → tab **Variables** → isi berdasarkan
`backend/.env.railway.example`:

| Variable | Nilai |
|---|---|
| `APP_NAME` | `Monitoring K3 Puskesmas` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | hasil dari `php artisan key:generate --show` (jalankan lokal) |
| `APP_URL` | domain Railway, mis. `https://k3-puskesmas.up.railway.app` |
| `SESSION_DOMAIN` | `null` |
| `SANCTUM_STATEFUL_DOMAINS` | domain Railway **tanpa** `https://`, mis. `k3-puskesmas.up.railway.app` |
| `FRONTEND_URL` | sama dengan `APP_URL` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | dari plugin MySQL (langkah 3) |

Railway otomatis menyediakan `PORT` — sudah ditangani oleh `docker/start.sh`,
tidak perlu diisi manual.

## 5. Deploy

Railway akan build & jalankan otomatis setelah variable diisi. Setiap kali
container start, `docker/start.sh` menjalankan berurutan:

1. `php artisan migrate --force` — bikin semua tabel (aman dijalankan
   berkali-kali, migration yang sudah jalan otomatis dilewati)
2. `php artisan deploy:seed-once` — **hanya jalan kalau tabel puskesmas masih
   kosong** (deploy pertama). Otomatis mengisi:
   - Akun Dinas Kesehatan (superadmin) + 23 akun Puskesmas asli
   - 21 pertanyaan Kuesioner K3 & 96 item Observasi
   - Data dummy pengisian 3 bulan ke belakang, supaya dashboard & rekap
     langsung terlihat terisi saat presentasi

Jadi begitu deploy pertama selesai, database **sudah langsung terisi
otomatis** — tidak perlu perintah manual apa pun. Redeploy/restart berikutnya
tidak akan mengulang seeding (dicek dulu datanya sudah ada atau belum), jadi
tidak akan bentrok data.

## 6. Cek

Buka domain Railway di browser → halaman login harus muncul dengan logo &
tema hijau. Health check tersedia di `/up`. Kredensial default:

- Dinas: username `admindinas`, password `dinas123`
- Puskesmas: lihat `backend/database/seeders/PuskesmasSeeder.php` untuk daftar
  username tiap puskesmas (password default `puskesmas123`)

## Catatan masa aktif

Free trial credit Railway biasanya cukup untuk traffic ringan selama
5–10 hari presentasi. Kalau mendekati habis, cek sisa credit di dashboard
Railway → **Usage**.
