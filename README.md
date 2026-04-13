# RT Finance API

Backend REST API berbasis Laravel 12 untuk administrasi warga, hunian, iuran, pembayaran, dan pengeluaran lingkungan RT/perumahan.

## Fitur Utama

- Autentikasi API menggunakan Laravel Sanctum
- Simple RBAC dengan role `admin`, `staff`, dan `penghuni`
- Manajemen data penghuni, rumah, hunian, iuran, kategori, pembayaran, dan pengeluaran
- Upload gambar KTP ke Cloudinary
- Endpoint generate tagihan pembayaran massal
- Response API JSON yang konsisten untuk data kosong, data tidak ditemukan, dan validasi

## Stack

- PHP 8.2+
- Laravel 12
- MySQL atau SQLite
- Laravel Sanctum
- Cloudinary API untuk upload gambar KTP

## Struktur API

Resource utama yang tersedia:

- `auth`
- `penghuni`
- `rumah`
- `hunian`
- `iuran`
- `payment`
- `outcome`
- `category`

## Prasyarat

Pastikan environment lokal sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- Database MySQL atau SQLite

## Instalasi

1. Clone repository

```bash
git clone <repository-url>
cd backend-project-test
```

2. Install dependency backend dan frontend

```bash
composer install
npm install
```

3. Copy file environment

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Atur koneksi database di `.env`

Contoh MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rt_finance
DB_USERNAME=root
DB_PASSWORD=
```

Contoh SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

6. Atur kredensial Cloudinary di `.env`

```env
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_FOLDER=residents/ktp
```

7. Jalankan migrasi

```bash
php artisan migrate
```

8. Bersihkan cache konfigurasi bila diperlukan

```bash
php artisan optimize:clear
```

9. Jalankan server development

```bash
php artisan serve
```

Jika frontend asset juga dipakai:

```bash
npm run dev
```

## Menjalankan Dengan Script Composer

Project ini juga menyediakan script:

```bash
composer run setup
composer run dev
composer run test
```

## Konfigurasi RBAC

Role yang digunakan:

- `admin`
- `staff`
- `penghuni`

Aturan default:

- User pertama yang register otomatis menjadi `admin`
- User berikutnya default menjadi `penghuni`
- Admin dapat membuat user dengan role lain melalui request body `role`

## Alur Autentikasi

1. Register user
2. Login dan ambil Bearer token
3. Kirim token pada request protected

Header yang dipakai:

```http
Accept: application/json
Authorization: Bearer <token>
```

## Endpoint Auth

### Register

`POST /api/auth/register`

Body JSON:

```json
{
  "name": "Admin RT",
  "email": "admin@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Contoh response:

```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin RT",
      "email": "admin@example.com",
      "role": "admin",
      "created_at": "2026-04-13T02:00:00.000000Z",
      "updated_at": "2026-04-13T02:00:00.000000Z"
    },
    "token": "1|plain_text_token_example"
  }
}
```

### Login

`POST /api/auth/login`

Body JSON:

```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

Contoh response:

```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin RT",
      "email": "admin@example.com",
      "role": "admin"
    },
    "token": "2|plain_text_token_example"
  }
}
```

### Me

`GET /api/auth/me`

### Logout

`POST /api/auth/logout`

## Endpoint Penghuni

### List penghuni

`GET /api/penghuni`

Contoh response saat ada data:

```json
{
  "status": "success",
  "message": "Residents retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Budi Santoso",
        "ktp_image": "https://res.cloudinary.com/demo/image/upload/sample.jpg",
        "status": "tetap",
        "no_telp": "081234567890",
        "is_married": true,
        "created_at": "2026-04-13T02:10:00.000000Z",
        "updated_at": "2026-04-13T02:10:00.000000Z"
      }
    ],
    "per_page": 10,
    "total": 1
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

Contoh response saat kosong:

```json
{
  "status": "success",
  "message": "No residents found",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 10,
    "total": 0
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 0
  }
}
```

### Tambah penghuni

`POST /api/penghuni`

Body JSON:

```json
{
  "name": "Budi Santoso",
  "ktp_image": "https://res.cloudinary.com/demo/image/upload/sample.jpg",
  "status": "tetap",
  "no_telp": "081234567890",
  "is_married": true
}
```

### Detail penghuni

`GET /api/penghuni/{resident}`

### Update penghuni

`PATCH /api/penghuni/{resident}`

Body JSON:

```json
{
  "no_telp": "081111111111"
}
```

### Hapus penghuni

`DELETE /api/penghuni/{resident}`

## Upload Gambar KTP

### Upload ke Cloudinary

`POST /api/penghuni/upload-ktp`

Gunakan `form-data` di Postman:

- key: `ktp_image`
- type: `File`

Contoh response:

```json
{
  "status": "success",
  "message": "KTP image uploaded successfully",
  "data": {
    "public_id": "residents/ktp/ktp_example",
    "url": "https://res.cloudinary.com/demo/image/upload/v1710000000/residents/ktp/ktp_example.jpg",
    "original_filename": "ktp_example",
    "width": 1200,
    "height": 800,
    "format": "jpg"
  }
}
```

## Endpoint Payment

### Buat tagihan/payment umum

`POST /api/payment`

Body JSON:

```json
{
  "housing_id": 1,
  "fee_id": 1,
  "month": 4,
  "year": 2026,
  "duration": 3
}
```

Keterangan:

- `duration` opsional
- jika `duration` lebih dari 1, sistem membuat tagihan beberapa bulan ke depan
- status awal payment dibuat `is_paid = false`

Contoh response:

```json
{
  "status": "success",
  "message": "Payments created successfully",
  "data": {
    "housing_id": 1,
    "fee": {
      "id": 1,
      "name": "Satpam"
    },
    "requested_duration": 3,
    "created": [
      {
        "id": 10,
        "housing_id": 1,
        "fee_id": 1,
        "month": 4,
        "year": 2026,
        "nominal": "100000.00",
        "payment_date": null,
        "is_paid": false
      }
    ],
    "skipped": []
  }
}
```

### Generate tagihan massal

`POST /api/payment/generate`

Body JSON:

```json
{
  "fee_id": 1,
  "month": 4,
  "year": 2026
}
```

Endpoint ini membuat tagihan unpaid untuk semua hunian aktif yang memenuhi periode tersebut.

### Update payment

`PATCH /api/payment/{payment}`

Contoh pelunasan:

```json
{
  "is_paid": true
}
```

Jika `is_paid` diubah ke `true` dan `payment_date` tidak dikirim, sistem akan mengisi waktu sekarang secara otomatis.

### Detail payment

`GET /api/payment/{payment}`

### Hapus payment

`DELETE /api/payment/{payment}`

## Endpoint Outcome

### Tambah pengeluaran

`POST /api/outcome`

Body JSON:

```json
{
  "description": "Perbaikan selokan",
  "category_id": 1,
  "total": 500000,
  "outcome_date": "2026-04-13"
}
```

## Response Error

### Data tidak ditemukan

```json
{
  "status": "error",
  "message": "Data not found"
}
```

### Endpoint tidak ditemukan

```json
{
  "status": "error",
  "message": "Endpoint not found"
}
```

### Tidak terautentikasi

```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

### Forbidden karena role

```json
{
  "status": "error",
  "message": "Forbidden"
}
```

### Validasi gagal

Contoh:

```json
{
  "message": "The email field is required.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

## Catatan Penting

- Endpoint protected wajib memakai Bearer token
- Role route sudah dibatasi di backend, jadi akses tanpa role yang sesuai akan ditolak
- Upload Cloudinary belum bisa dipakai sebelum kredensial `.env` diisi
- Jika struktur migration berubah setelah database sempat dibuat, jalankan migration yang sesuai atau gunakan database baru

## Pengembangan Selanjutnya

Beberapa hal yang cocok dikembangkan berikutnya:

- Seeder awal untuk admin, kategori, dan iuran
- Dokumentasi Postman collection
- Laporan bulanan dan tahunan pemasukan/pengeluaran
- Scheduler untuk generate tagihan otomatis bulanan
- Unit test untuk auth, payment, dan upload KTP
