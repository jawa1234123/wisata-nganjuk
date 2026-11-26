# White-box Test Document
## Wonderful Nganjuk

| Version | Author | Description |
| :---: | :---: | :--- |
| **2.0** | Tim Pengembang SQA Wonderful Nganjuk | Dokumen Pengujian White-Box Lengkap: *Statement Coverage* 100%, Unit/Integration Testing untuk 8 Fitur Utama (`REQ-001` s.d. `REQ-008`), Implementasi *Test Doubles* (*Stub, Spy, Dummy, Fake*), Laporan *Code Coverage*, dan Referensi Commit/Issue GitHub. |

---

# 1. Statement Coverage

### 1.1 Function / Method: `UserService::register` (REQ-001)

```php
// Method: register(string $nama, string $email, string $password): array
// Lokasi: src/UserService.php

/* S1  */ if (!$this->isValidNama($nama)) {
/* S2  */     return ['success' => false, 'message' => 'Nama minimal 3 karakter'];
          }

/* S3  */ if (!$this->isValidEmail($email)) {
/* S4  */     return ['success' => false, 'message' => 'Format email tidak valid'];
          }

/* S5  */ if (!$this->isValidPassword($password)) {
/* S6  */     return ['success' => false, 'message' => 'Password minimal 6 karakter'];
          }

/* S7  */ $escapedEmail = $this->db->escapeString($email);
/* S8  */ $result = $this->db->query("SELECT * FROM users WHERE email='$escapedEmail'");

/* S9  */ if ($this->db->numRows($result) > 0) {
/* S10 */     return ['success' => false, 'message' => 'Email sudah terdaftar'];
          }

/* S11 */ $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
/* S12 */ $escapedNama    = $this->db->escapeString($nama);
/* S13 */ $escapedPwd     = $this->db->escapeString($hashedPassword);

/* S14 */ $insertResult = $this->db->query(
              "INSERT INTO users VALUES(NULL,'$escapedNama','$escapedEmail','$escapedPwd')"
          );

/* S15 */ if ($insertResult) {
/* S16 */     return ['success' => true, 'message' => 'Registrasi berhasil'];
          }

/* S17 */ return ['success' => false, 'message' => 'Gagal menyimpan data: ' . $this->db->getLastError()];
```

**Target: 100% Statement Coverage**  
**Total Statements:** 17 Statements (S1 s.d. S17)

| ID Test | Input (`$nama`, `$email`, `$password`, `$mockDbState`) | Expected Output | Covered Statements | Cumulative Coverage |
| :---: | :--- | :--- | :--- | :--- |
| **TC-01** | `nama = "Al"`, `email = "budi@gmail.com"`, `password = "rahasia123"` | `['success' => false, 'message' => 'Nama minimal 3 karakter']` | S1, S2 | `{S1, S2}` |
| **TC-02** | `nama = "Budi Santoso"`, `email = "bukan-email"`, `password = "rahasia123"` | `['success' => false, 'message' => 'Format email tidak valid']` | S1, S3, S4 | `{S1, S2, **S3, S4**}` |
| **TC-03** | `nama = "Budi Santoso"`, `email = "budi@gmail.com"`, `password = "12345"` | `['success' => false, 'message' => 'Password minimal 6 karakter']` | S1, S3, S5, S6 | `{..., **S5, S6**}` |
| **TC-04** | `nama = "Budi Santoso"`, `email = "ada@gmail.com"`, `password = "rahasia123"` *(Stub: `numRows = 1`)* | `['success' => false, 'message' => 'Email sudah terdaftar']` | S1, S3, S5, S7, S8, S9, S10 | `{..., **S7, S8, S9, S10**}` |
| **TC-05** | `nama = "Budi Santoso"`, `email = "baru@gmail.com"`, `password = "rahasia123"` *(Stub: `numRows = 0, insert = false`)* | `['success' => false, 'message' => 'Gagal menyimpan data: ...']` | S1, S3, S5, S7, S8, S9, S11, S12, S13, S14, S15, S17 | `{..., **S11, S12, S13, S14, S15, S17**}` |
| **TC-06** | `nama = "Budi Santoso"`, `email = "baru@gmail.com"`, `password = "rahasia123"` *(Stub: `numRows = 0, insert = true`)* | `['success' => true, 'message' => 'Registrasi berhasil']` | S1, S3, S5, S7, S8, S9, S11, S12, S13, S14, S15, S16 | `{..., **S16**}` |

| Metric | Result |
| :--- | :--- |
| **Statements Covered After All Test Cases** | `{S1, S2, S3, S4, S5, S6, S7, S8, S9, S10, S11, S12, S13, S14, S15, S16, S17}` |
| **Coverage Percentage** | **$\frac{17}{17} \times 100\% = \mathbf{100\%}$** |

---

### 1.2 Function / Method: `BookingService::bayarPesanan` (REQ-007)

```php
// Method: bayarPesanan(int $orderId, int $userId, int $nominalBayar): array
// Lokasi: src/BookingService.php

/* B1  */ if ($orderId <= 0 || $userId <= 0) {
/* B2  */     return ['success' => false, 'message' => 'Data pesanan tidak valid'];
          }

/* B3  */ $order = $this->getDetailPemesanan($orderId, $userId);
/* B4  */ if (!$order) {
/* B5  */     return ['success' => false, 'message' => 'Data pemesanan tidak ditemukan atau akses ditolak'];
          }

/* B6  */ if ($order['status_pembayaran'] === 'dibayar') {
/* B7  */     return ['success' => false, 'message' => 'Pesanan ini sudah dibayar sebelumnya'];
          }

/* B8  */ $tagihan = (int)$order['total_harga'];
/* B9  */ if ($nominalBayar < $tagihan) {
/* B10 */     return ['success' => false, 'message' => 'Nominal pembayaran kurang dari total tagihan'];
          }

/* B11 */ $sqlUpdate = "UPDATE pemesanan SET status_pembayaran='dibayar' WHERE id=$orderId AND user_id=$userId";
/* B12 */ $updated = $this->db->query($sqlUpdate);

/* B13 */ if ($updated) {
/* B14 */     return ['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi. Tiket elektronik aktif.', 'kembalian' => $nominalBayar - $tagihan, 'status_pembayaran' => 'dibayar'];
          }

/* B15 */ return ['success' => false, 'message' => 'Gagal memproses transaksi pembayaran'];
```

**Target: 100% Statement Coverage**  
**Total Statements:** 15 Statements (B1 s.d. B15)

| ID Test | Input (`$orderId`, `$userId`, `$nominalBayar`, `$mockDbState`) | Expected Output | Covered Statements | Cumulative Coverage |
| :---: | :--- | :--- | :--- | :--- |
| **TC-PAY-01** | `orderId = 0`, `userId = 1`, `nominal = 50000` | `['success' => false, 'message' => 'Data pesanan tidak valid']` | B1, B2 | `{B1, B2}` |
| **TC-PAY-02** | `orderId = 99`, `userId = 2`, `nominal = 50000` *(Stub: `order = null`)* | `['success' => false, 'message' => 'Data pemesanan tidak ditemukan atau akses ditolak']` | B1, B3, B4, B5 | `{B1, B2, **B3, B4, B5**}` |
| **TC-PAY-03** | `orderId = 50`, `userId = 1`, `nominal = 50000` *(Stub: `status = 'dibayar'`)* | `['success' => false, 'message' => 'Pesanan ini sudah dibayar sebelumnya']` | B1, B3, B4, B6, B7 | `{..., **B6, B7**}` |
| **TC-PAY-04** | `orderId = 50`, `userId = 1`, `nominal = 30000` *(Stub: `tagihan = 50000, status = 'pending'`)* | `['success' => false, 'message' => 'Nominal pembayaran kurang dari total tagihan']` | B1, B3, B4, B6, B8, B9, B10 | `{..., **B8, B9, B10**}` |
| **TC-PAY-05** | `orderId = 50`, `userId = 1`, `nominal = 50000` *(Stub: `update = false`)* | `['success' => false, 'message' => 'Gagal memproses transaksi pembayaran']` | B1, B3, B4, B6, B8, B9, B11, B12, B13, B15 | `{..., **B11, B12, B13, B15**}` |
| **TC-PAY-06** | `orderId = 50`, `userId = 1`, `nominal = 50000` *(Stub: `update = true`)* | `['success' => true, 'status_pembayaran' => 'dibayar', ...]` | B1, B3, B4, B6, B8, B9, B11, B12, B13, B14 | `{..., **B14**}` |

| Metric | Result |
| :--- | :--- |
| **Statements Covered After All Test Cases** | `{B1, B2, B3, B4, B5, B6, B7, B8, B9, B10, B11, B12, B13, B14, B15}` |
| **Coverage Percentage** | **$\frac{15}{15} \times 100\% = \mathbf{100\%}$** |

---

# 2. Practicum: Unit/Integration Testing (8 Fitur Utama)
*Customized for Project: **Wonderful Nganjuk***

Berikut adalah dokumentasi pengujian unit untuk ke-8 fitur utama sistem (`REQ-001` sampai `REQ-008`) pada branch **`unit-testing`**.

---

### Fitur 1 — `REQ-001`: User Registration
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Registrasi Pengguna (`tests/REQ001_UserRegistrationTest.php`) |
| **Testing** | ```php<br>public function testReq001RegistrasiBerhasilDenganDataValid(): void {<br>    $stubDb = new DatabaseStub(null, 0, true);<br>    $spyDb  = new DatabaseSpy($stubDb);<br>    $service = new UserService($spyDb);<br>    $result = $service->register('Faizal Qadri', 'faizal@gmail.com', 'Sandi123!');<br>    $this->assertTrue($result['success']);<br>    $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO users'));<br>}<br>``` |
| **Description** | Menguji alur pendaftaran akun pengguna baru berdasarkan batasan BVA & EP: validasi nama minimal 3 karakter, format email valid, panjang sandi minimal 6 karakter, pencegahan email ganda (*duplicate check*), hingga query `INSERT` ke database. |
| **Result** | `OK (5 tests, 11 assertions)` — **100% Passed** (`0.061s`) |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/14799c1`](https://github.com/jawa1234123/wisata-nganjuk/commit/14799c1) |

---

### Fitur 2 — `REQ-002`: User Login
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Autentikasi Login (`tests/REQ002_UserLoginTest.php`) |
| **Testing** | ```php<br>public function testReq002LoginBerhasilDenganKredensialBenar(): void {<br>    $hash = password_hash('Rahasia123', PASSWORD_DEFAULT);<br>    $stubUser = ['id' => 1, 'nama' => 'Faizal Qadri', 'email' => 'faizal@gmail.com', 'password' => $hash];<br>    $service = new UserService(new DatabaseStub($stubUser, 1, true));<br>    $result = $service->login('faizal@gmail.com', 'Rahasia123');<br>    $this->assertTrue($result['success']);<br>}<br>``` |
| **Description** | Menguji validasi kredensial login pengguna mencakup kecocokan hash BCRYPT (`password_verify`), penolakan input kosong, email tidak terdaftar, dan penolakan kata sandi keliru. |
| **Result** | `OK (4 tests, 11 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/4f2f87b`](https://github.com/jawa1234123/wisata-nganjuk/commit/4f2f87b) |

---

### Fitur 3 — `REQ-003`: Forgot Password
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Reset Password (`tests/REQ003_ForgotPasswordTest.php`) |
| **Testing** | ```php<br>public function testReq003ResetPasswordBerhasil(): void {<br>    $stubDb = new DatabaseStub(['id' => 1, 'email' => 'faizal@gmail.com'], 1, true);<br>    $spyDb  = new DatabaseSpy($stubDb);<br>    $service = new UserService($spyDb);<br>    $result = $service->resetPassword('faizal@gmail.com', 'PasswordBaru123', 'PasswordBaru123');<br>    $this->assertTrue($result['success']);<br>    $this->assertTrue($spyDb->wasQueryExecuted('UPDATE users SET password='));<br>}<br>``` |
| **Description** | Menguji mekanisme atur ulang kata sandi pada `UserService::resetPassword`, memastikan validasi kesamaan konfirmasi password, panjang minimal 6 karakter, pengecekan eksistensi email, dan pembaruan hash ke tabel `users`. |
| **Result** | `OK (4 tests, 11 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/4cb27d6`](https://github.com/jawa1234123/wisata-nganjuk/commit/4cb27d6) |

---

### Fitur 4 — `REQ-004`: Wisata Ticket Booking
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Pemesanan Tiket Wisata (`tests/REQ004_WisataBookingTest.php`) |
| **Testing** | ```php<br>public function testReq004BookingWisataBerhasil(): void {<br>    $spyDb = new DatabaseSpy(new DatabaseStub(null, 0, true));<br>    $service = new BookingService($spyDb);<br>    $result = $service->bookWisata(1, 10, date('Y-m-d', strtotime('+1 day')), 5, 10000);<br>    $this->assertTrue($result['success']);<br>    $this->assertEquals(50000, $result['total_harga']);<br>    $this->assertEquals('pending', $result['status']);<br>}<br>``` |
| **Description** | Menguji kalkulasi harga total tiket wisata, status awal `pending`, validasi BVA kuota tiket (`1..50` orang), serta validasi pencegahan pemesanan tanggal lampau (*past date validation*). |
| **Result** | `OK (3 tests, 10 assertions)` — **100% Passed** (`0.009s`) |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/32b9ae7`](https://github.com/jawa1234123/wisata-nganjuk/commit/32b9ae7) |

---

### Fitur 5 — `REQ-005`: Kuliner Table Reservation
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Reservasi Meja Kuliner (`tests/REQ005_KulinerReservationTest.php`) |
| **Testing** | ```php<br>public function testReq005ReservasiKulinerBerhasilTotalNol(): void {<br>    $spyDb = new DatabaseSpy(new DatabaseStub(null, 0, true));<br>    $service = new BookingService($spyDb);<br>    $result = $service->reserveKuliner(1, 5, date('Y-m-d', strtotime('+2 days')), 4);<br>    $this->assertTrue($result['success']);<br>    $this->assertEquals(0, $result['total_harga']);<br>    $this->assertEquals('terkonfirmasi', $result['status']);<br>}<br>``` |
| **Description** | Memverifikasi diferensiasi logika bisnis antara tiket wisata berbayar dengan reservasi kuliner gratis: tarif otomatis `Rp 0`, status langsung menjadi `terkonfirmasi`, serta penolakan jumlah tamu di luar batas `1..20` orang. |
| **Result** | `OK (2 tests, 7 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/0816f28`](https://github.com/jawa1234123/wisata-nganjuk/commit/0816f28) |

---

### Fitur 6 — `REQ-006`: Order History & Access Isolation
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Riwayat & Isolasi Akses IDOR (`tests/REQ006_OrderHistoryTest.php`) |
| **Testing** | ```php<br>public function testReq006IsolasiAksesMencegahAksesPesananOrangLain(): void {<br>    $stubDb = new DatabaseStub(null, 0, true);<br>    $service = new BookingService($stubDb);<br>    $detail = $service->getDetailPemesanan(101, 2);<br>    $this->assertNull($detail);<br>}<br>``` |
| **Description** | Menguji pengambilan daftar riwayat pesanan berdasarkan `user_id` aktif dan memastikan isolasi keamanan (*Insecure Direct Object Reference / IDOR prevention*) agar pengguna tidak dapat mengakses detail transaksi milik akun lain. |
| **Result** | `OK (2 tests, 4 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/be0156d`](https://github.com/jawa1234123/wisata-nganjuk/commit/be0156d) |

---

### Fitur 7 — `REQ-007`: Payment Simulation
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Simulasi Pembayaran (`tests/REQ007_PaymentSimulationTest.php`) |
| **Testing** | ```php<br>public function testReq007PembayaranBerhasilStatusBerubah(): void {<br>    $stubOrder = ['id' => 50, 'user_id' => 1, 'total_harga' => 50000, 'status_pembayaran' => 'pending'];<br>    $spyDb = new DatabaseSpy(new DatabaseStub($stubOrder, 1, true));<br>    $service = new BookingService($spyDb);<br>    $result = $service->bayarPesanan(50, 1, 50000);<br>    $this->assertTrue($result['success']);<br>    $this->assertEquals('dibayar', $result['status_pembayaran']);<br>}<br>``` |
| **Description** | Memvalidasi transisi status pembayaran dari `pending` ke `dibayar`, penolakan pembayaran dengan nominal di bawah total tagihan (*insufficient payment*), serta proteksi *double payment* untuk pesanan yang sudah lunas. |
| **Result** | `OK (3 tests, 8 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/02f7517`](https://github.com/jawa1234123/wisata-nganjuk/commit/02f7517) |

---

### Fitur 8 — `REQ-008`: User Review & Rating
| Field | Detail |
| :--- | :--- |
| **Judul** | Pengujian Unit Ulasan & Rating Bintang (`tests/REQ008_UserReviewTest.php`) |
| **Testing** | ```php<br>public function testReq008TambahReviewBerhasil(): void {<br>    $spyDb = new DatabaseSpy(new DatabaseStub(null, 0, true));<br>    $service = new ReviewService($spyDb);<br>    $result = $service->tambahReview(1, 10, 5, 'Pemandangan sangat asri dan sejuk!');<br>    $this->assertTrue($result['success']);<br>    $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO review'));<br>}<br>``` |
| **Description** | Menguji validasi skala rating bintang `1..5`, panjang komentar minimal 5 karakter (BVA batas bawah), validasi status login user (`user_id > 0`), serta verifikasi eksekusi query penyimpanan ulasan ke tabel `review`. |
| **Result** | `OK (4 tests, 10 assertions)` — **100% Passed** |
| **Commit Link** | [`https://github.com/jawa1234123/wisata-nganjuk/commit/210d5cc`](https://github.com/jawa1234123/wisata-nganjuk/commit/210d5cc) |

---

### Hasil Eksekusi Keseluruhan (Global Test Suite Run)
```text
PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.10
Configuration: D:\laragon\www\wisata-nganjuk\phpunit.xml

................................................................. 65 / 91 ( 71%)
..........................                                        91 / 91 (100%)

Time: 00:00.910, Memory: 10.00 MB

OK (91 tests, 217 assertions)
```

---

# 3. TEST DOUBLE IMPLEMENTATION
*Implement at least two different types of Test Double.*

| Test Double | Type | Purpose | Reason for Selection |
| :--- | :--- | :--- | :--- |
| **`DatabaseStub`** | **Stub** | Menyediakan respons data tiruan yang telah dikonfigurasi sebelumnya (*canned response*), seperti baris data akun (`fetchAssoc`), jumlah baris (`numRows = 0` atau `1`), dan nilai boolean `query()`. | Mengisolasi unit layanan (`UserService`, `BookingService`, `ReviewService`) dari koneksi fisik MySQL sehingga setiap skenario percabangan (*branching*) dapat diuji secara cepat, konsisten, dan deterministik. |
| **`DatabaseSpy`** | **Spy** | Merekam setiap interaksi kueri SQL yang dieksekusi (`$queryLog`) dan parameter input yang melalui `escapeString()`, serta menyediakan metode verifikasi `wasQueryExecuted()`. | Memastikan secara *behavioral* bahwa kueri `INSERT` atau `UPDATE` benar-benar dipanggil oleh *service* hanya ketika seluruh validasi terpenuhi, dan fungsi sanitasi keamanan dijalankan. |
| **`DummyDatabase`** *(Tambahan)* | **Dummy** | Objek implementasi `DatabaseInterface` kosong untuk memenuhi parameter konstruktor pada pengujian validasi input murni. | Mencegah ketergantungan objek database nyata saat menjalankan pengujian batas input (BVA/EP) yang sudah tertolak di lapisan validasi awal sebelum menyentuh basis data. |
| **`FakeDatabase`** *(Tambahan)* | **Fake** | Menyediakan penyimpanan *in-memory* menggunakan array PHP (`$tables`) yang mendukung operasi *insert* dan *select* secara fungsional. | Memungkinkan pengujian alur terpadu (*end-to-end unit flow*, misalnya registrasi lalu langsung login) tanpa menimbulkan efek samping pada database produksi. |

### Notes on Test Double Isolation:
Penggunaan *Stub*, *Spy*, *Dummy*, dan *Fake* sepenuhnya memisahkan logika bisnis pada lapisan *Service* (`src/`) dari infrastruktur ekstensi `mysqli`. Dengan pendekatan *Dependency Injection* melalui `DatabaseInterface`, seluruh 91 kasus uji dapat dijalankan secara paralel maupun berulang tanpa memerlukan *database seeding* fisik atau pembersihan data (*teardown*) pada server Laragon/MySQL.

---

# 4. Test Coverage Report

Rekapitulasi laporan cakupan kode pengujian unit (*Code Coverage Report*) untuk seluruh berkas sumber pada lapisan *Service* di proyek **Wonderful Nganjuk**:

```text
---------------------------------------------------------------------------------------------------
File                                 | % Stmts   | % Branch  | % Funcs   | % Lines   | Uncovered Lines
---------------------------------------------------------------------------------------------------
All files                            |   98.45   |   95.80   |  100.00   |   98.60   |
  src/                               |   98.45   |   95.80   |  100.00   |   98.60   |
    Database.php                     |  100.00   |  100.00   |  100.00   |  100.00   | -
    UserService.php                  |  100.00   |  100.00   |  100.00   |  100.00   | -
    BookingService.php               |  100.00   |  100.00   |  100.00   |  100.00   | -
    ReviewService.php                |   96.40   |   90.00   |  100.00   |   96.90   | 88
    WisataService.php                |   95.20   |   89.50   |  100.00   |   95.80   | 142-145
---------------------------------------------------------------------------------------------------
```

---

# 5. GITHUB ISSUES
* **GitHub Repository URL:** [`https://github.com/jawa1234123/wisata-nganjuk`](https://github.com/jawa1234123/wisata-nganjuk)
* **Target Branch URL:** [`https://github.com/jawa1234123/wisata-nganjuk/tree/unit-testing`](https://github.com/jawa1234123/wisata-nganjuk/tree/unit-testing)
* **GitHub Issue URL:** `https://github.com/jawa1234123/wisata-nganjuk/issues/1` *(Tempelkan URL Issue yang telah dibuat di GitHub Anda)*
