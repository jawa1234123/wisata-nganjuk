# Unit Test - Wisata Nganjuk

Panduan menjalankan unit test untuk proyek ini.

## Instalasi

```bash
# Install dependencies (PHPUnit)
composer install
```

## Menjalankan Test

```bash
# Jalankan semua test
vendor/bin/phpunit

# Jalankan dengan output detail
vendor/bin/phpunit --testdox

# Jalankan test tertentu
vendor/bin/phpunit tests/UserServiceTest.php
vendor/bin/phpunit tests/WisataServiceTest.php
vendor/bin/phpunit tests/ReviewServiceTest.php

# Filter berdasarkan group
vendor/bin/phpunit --group validasi
vendor/bin/phpunit --group stub
vendor/bin/phpunit --group fake
vendor/bin/phpunit --group spy
vendor/bin/phpunit --group mock
```

## Struktur Test

```
tests/
├── TestDoubles.php       ← Implementasi test doubles
├── UserServiceTest.php   ← Test: registrasi, login, validasi user
├── WisataServiceTest.php ← Test: CRUD wisata, validasi, filter
└── ReviewServiceTest.php ← Test: review, rating, format bintang
```

## Test Double yang Digunakan

| Double | Kelas | Deskripsi |
|--------|-------|-----------|
| Dummy  | `DummyDatabase` | Tidak dipakai, hanya penuhi signature |
| Stub   | `DatabaseStub`  | Mengembalikan data tetap (hard-coded) |
| Fake   | `FakeDatabase`  | Implementasi in-memory yang nyata |
| Spy    | `DatabaseSpy`   | Merekam semua interaksi database |
| Mock   | PHPUnit createMock | Ekspektasi ketat pada pemanggilan |
