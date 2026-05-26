<?php

/**
 * ============================================================
 * UNIT TEST: UserServiceTest
 * ============================================================
 * File ini menguji class UserService menggunakan berbagai
 * teknik test double:
 *   - STUB   : untuk simulasi respon database
 *   - FAKE   : untuk simulasi penyimpanan data in-memory
 *   - SPY    : untuk verifikasi interaksi dengan database
 *   - DUMMY  : untuk objek yang tidak dipakai dalam test
 *   - MOCK   : menggunakan PHPUnit createMock() untuk verifikasi perilaku
 *
 * Teknik pengujian:
 *   - Equivalence Partitioning : membagi input valid & tidak valid
 *   - Boundary Value Analysis  : nilai batas (misal: panjang password)
 * ============================================================
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/TestDoubles.php';

class UserServiceTest extends TestCase
{
    // ==========================================================
    // BAGIAN 1: Test validasi (tidak perlu database sama sekali)
    // Menggunakan DUMMY karena database tidak dipakai
    // ==========================================================

    /**
     * @test
     * @group validasi
     */
    public function testEmailValidDenganFormatBenar(): void
    {
        // DUMMY: database tidak dipakai, hanya penuhi parameter constructor
        $dummyDb = new DummyDatabase();
        $service  = new UserService($dummyDb);

        $this->assertTrue($service->isValidEmail('user@example.com'));
        $this->assertTrue($service->isValidEmail('budi.santoso@gmail.com'));
        $this->assertTrue($service->isValidEmail('wisata@nganjuk.go.id'));
    }

    /**
     * @test
     * @group validasi
     * Equivalence Partitioning: partisi TIDAK VALID untuk email
     */
    public function testEmailTidakValidDenganFormatSalah(): void
    {
        $dummyDb = new DummyDatabase();
        $service  = new UserService($dummyDb);

        $this->assertFalse($service->isValidEmail('bukan-email'));
        $this->assertFalse($service->isValidEmail('tanpa-at-sign.com'));
        $this->assertFalse($service->isValidEmail('@tanpadomain.com'));
        $this->assertFalse($service->isValidEmail(''));
    }

    /**
     * @test
     * @group validasi
     * Boundary Value Analysis: batas minimum panjang password
     */
    public function testPasswordValidDiBatasMinimum(): void
    {
        $dummyDb = new DummyDatabase();
        $service  = new UserService($dummyDb);

        // Tepat di batas bawah (6 karakter) -> valid
        $this->assertTrue($service->isValidPassword('abc123'));

        // Di atas batas -> valid
        $this->assertTrue($service->isValidPassword('password123'));
    }

    /**
     * @test
     * @group validasi
     * Boundary Value Analysis: di bawah batas minimum password
     */
    public function testPasswordTidakValidDiBawahBatas(): void
    {
        $dummyDb = new DummyDatabase();
        $service  = new UserService($dummyDb);

        // 5 karakter (1 di bawah batas 6) -> tidak valid
        $this->assertFalse($service->isValidPassword('abc12'));

        // Kosong -> tidak valid
        $this->assertFalse($service->isValidPassword(''));
    }

    /**
     * @test
     * @group validasi
     */
    public function testNamaValidMinimalTigaKarakter(): void
    {
        $dummyDb = new DummyDatabase();
        $service  = new UserService($dummyDb);

        $this->assertTrue($service->isValidNama('Budi'));
        $this->assertTrue($service->isValidNama('Ani'));   // tepat 3 karakter
        $this->assertFalse($service->isValidNama('Ab'));   // 2 karakter
        $this->assertFalse($service->isValidNama('  '));   // spasi saja
    }

    // ==========================================================
    // BAGIAN 2: Test registrasi dengan STUB
    // STUB: database mengembalikan data tetap (pre-configured)
    // ==========================================================

    /**
     * @test
     * @group registrasi
     * STUB: numRows = 0 -> email belum terdaftar, insert berhasil
     */
    public function testRegistrasiBerhasilJikaEmailBelumTerdaftar(): void
    {
        // STUB: simulasikan email belum ada di database (numRows=0) dan insert berhasil
        $stubDb  = new DatabaseStub(stubbedNumRows: 0, querySuccess: true);
        $service = new UserService($stubDb);

        $result = $service->register('Budi Santoso', 'budi@email.com', 'password123');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registrasi berhasil', $result['message']);
    }

    /**
     * @test
     * @group registrasi
     * STUB: numRows = 1 -> email sudah terdaftar
     */
    public function testRegistrasiGagalJikaEmailSudahTerdaftar(): void
    {
        // STUB: simulasikan email SUDAH ada (numRows=1)
        $stubDb  = new DatabaseStub(stubbedNumRows: 1, querySuccess: true);
        $service = new UserService($stubDb);

        $result = $service->register('Budi Santoso', 'budi@email.com', 'password123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email sudah terdaftar', $result['message']);
    }

    /**
     * @test
     * @group registrasi
     */
    public function testRegistrasiGagalJikaEmailTidakValid(): void
    {
        $stubDb  = new DatabaseStub();
        $service = new UserService($stubDb);

        $result = $service->register('Budi', 'emailsalah', 'password123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Format email tidak valid', $result['message']);
    }

    /**
     * @test
     * @group registrasi
     */
    public function testRegistrasiGagalJikaPasswordTerlalupendek(): void
    {
        $stubDb  = new DatabaseStub();
        $service = new UserService($stubDb);

        $result = $service->register('Budi', 'budi@email.com', '123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Password minimal 6 karakter', $result['message']);
    }

    /**
     * @test
     * @group registrasi
     */
    public function testRegistrasiGagalJikaNamaTerlalupendek(): void
    {
        $stubDb  = new DatabaseStub();
        $service = new UserService($stubDb);

        $result = $service->register('AB', 'budi@email.com', 'password123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Nama minimal 3 karakter', $result['message']);
    }

    /**
     * @test
     * @group registrasi
     * STUB: querySuccess=false -> insert gagal
     */
    public function testRegistrasiGagalJikaInsertDatabaseGagal(): void
    {
        // STUB: email belum ada (numRows=0), tapi insert gagal (querySuccess=false)
        $stubDb = new DatabaseStub(stubbedNumRows: 0, querySuccess: false);
        // Perlu stub yang query pertama (SELECT) sukses tapi query kedua (INSERT) gagal
        // Kita gunakan custom stub sederhana
        $stubDb2 = new class implements DatabaseInterface {
            private int $queryCount = 0;

            public function query(string $sql): mixed
            {
                $this->queryCount++;
                // Query pertama (SELECT) -> sukses, query kedua (INSERT) -> gagal
                return $this->queryCount === 1 ? true : false;
            }
            public function fetchAssoc(mixed $result): ?array { return null; }
            public function numRows(mixed $result): int { return 0; } // email belum ada
            public function escapeString(string $str): string { return $str; }
            public function getLastError(): string { return 'Duplicate entry'; }
        };

        $service = new UserService($stubDb2);
        $result  = $service->register('Budi Santoso', 'budi@email.com', 'password123');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal menyimpan data', $result['message']);
    }

    // ==========================================================
    // BAGIAN 3: Test login dengan STUB
    // ==========================================================

    /**
     * @test
     * @group login
     * STUB: mengembalikan data user dengan password yang sudah di-hash
     */
    public function testLoginBerhasilDenganKredensialBenar(): void
    {
        $passwordAsli   = 'password123';
        $passwordHashed = password_hash($passwordAsli, PASSWORD_DEFAULT);

        $userRow = [
            'id'       => 1,
            'nama'     => 'Budi Santoso',
            'email'    => 'budi@email.com',
            'password' => $passwordHashed,
        ];

        // STUB: database mengembalikan data user yang ada
        $stubDb  = new DatabaseStub(stubbedRow: $userRow);
        $service = new UserService($stubDb);

        $result = $service->login('budi@email.com', $passwordAsli);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login berhasil', $result['message']);
        $this->assertNotNull($result['user']);
        $this->assertEquals('Budi Santoso', $result['user']['nama']);
    }

    /**
     * @test
     * @group login
     * STUB: fetchAssoc mengembalikan null -> user tidak ditemukan
     */
    public function testLoginGagalJikaEmailTidakDitemukan(): void
    {
        // STUB: tidak ada user yang ditemukan
        $stubDb  = new DatabaseStub(stubbedRow: null);
        $service = new UserService($stubDb);

        $result = $service->login('tidakada@email.com', 'password123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email atau password salah', $result['message']);
        $this->assertNull($result['user']);
    }

    /**
     * @test
     * @group login
     * STUB: user ada tapi password salah
     */
    public function testLoginGagalJikaPasswordSalah(): void
    {
        $userRow = [
            'id'       => 1,
            'nama'     => 'Budi',
            'email'    => 'budi@email.com',
            'password' => password_hash('passwordBenar', PASSWORD_DEFAULT),
        ];

        $stubDb  = new DatabaseStub(stubbedRow: $userRow);
        $service = new UserService($stubDb);

        // Mencoba login dengan password yang salah
        $result = $service->login('budi@email.com', 'passwordSalah');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email atau password salah', $result['message']);
    }

    /**
     * @test
     * @group login
     */
    public function testLoginGagalJikaEmailKosong(): void
    {
        $stubDb  = new DatabaseStub();
        $service = new UserService($stubDb);

        $result = $service->login('', 'password123');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('harus diisi', $result['message']);
    }

    // ==========================================================
    // BAGIAN 4: Test dengan FAKE (in-memory database)
    // ==========================================================

    /**
     * @test
     * @group fake
     * FAKE: menggunakan FakeDatabase yang menyimpan data di memori
     */
    public function testRegistrasiDanLoginMenggunakanFakeDatabase(): void
    {
        $fakeDb  = new FakeDatabase();
        $fakeDb->seedTable('users', []); // Mulai dengan tabel kosong
        $service = new UserService($fakeDb);

        // Registrasi
        $regResult = $service->register('Siti Aminah', 'siti@email.com', 'password999');
        $this->assertTrue($regResult['success']);

        // Cek user tersimpan di fake DB
        $usersTable = $fakeDb->getTable('users');
        $this->assertCount(1, $usersTable);
        $this->assertEquals('Siti Aminah', $usersTable[0]['nama']);
        $this->assertEquals('siti@email.com', $usersTable[0]['email']);
    }

    /**
     * @test
     * @group fake
     * FAKE: registrasi email duplikat menggunakan FakeDatabase
     */
    public function testRegistrasiDuplikatMenggunakanFakeDatabase(): void
    {
        $fakeDb = new FakeDatabase();
        $fakeDb->seedTable('users', [
            [
                'id'       => 1,
                'nama'     => 'Existing User',
                'email'    => 'ada@email.com',
                'password' => password_hash('pass', PASSWORD_DEFAULT),
            ],
        ]);

        $service   = new UserService($fakeDb);
        $regResult = $service->register('User Baru', 'ada@email.com', 'password123');

        $this->assertFalse($regResult['success']);
        $this->assertEquals('Email sudah terdaftar', $regResult['message']);
    }

    // ==========================================================
    // BAGIAN 5: Test dengan SPY - verifikasi interaksi
    // ==========================================================

    /**
     * @test
     * @group spy
     * SPY: verifikasi bahwa escapeString dipanggil (sanitasi input)
     */
    public function testRegistrasiMelakukanEscapeStringUntukKeamanan(): void
    {
        $delegate = new DatabaseStub(stubbedNumRows: 0, querySuccess: true);
        $spyDb    = new DatabaseSpy($delegate);
        $service  = new UserService($spyDb);

        $service->register('Budi Santoso', 'budi@email.com', 'password123');

        // SPY: verifikasi bahwa escapeString dipanggil (penting untuk keamanan SQL injection)
        $escapeLog = $spyDb->getEscapeLog();
        $this->assertNotEmpty($escapeLog, 'escapeString harus dipanggil untuk sanitasi input');

        // Email harus di-escape
        $this->assertContains('budi@email.com', $escapeLog);
    }

    /**
     * @test
     * @group spy
     * SPY: verifikasi bahwa query SELECT dieksekusi saat registrasi
     */
    public function testRegistrasiMengeksekusiQuerySelectUntukCekEmail(): void
    {
        $delegate = new DatabaseStub(stubbedNumRows: 0, querySuccess: true);
        $spyDb    = new DatabaseSpy($delegate);
        $service  = new UserService($spyDb);

        $service->register('Budi', 'budi@email.com', 'password123');

        // SPY: verifikasi query SELECT dijalankan (untuk cek duplikat email)
        $this->assertTrue(
            $spyDb->wasQueryExecuted('SELECT'),
            'Query SELECT harus dieksekusi untuk mengecek duplikat email'
        );
    }

    /**
     * @test
     * @group spy
     * SPY: verifikasi query INSERT dieksekusi setelah validasi lolos
     */
    public function testRegistrasiBerhasilMengeksekusiInsert(): void
    {
        $delegate = new DatabaseStub(stubbedNumRows: 0, querySuccess: true);
        $spyDb    = new DatabaseSpy($delegate);
        $service  = new UserService($spyDb);

        $service->register('Budi', 'budi@email.com', 'password123');

        // SPY: verifikasi INSERT dieksekusi
        $this->assertTrue(
            $spyDb->wasQueryExecuted('INSERT INTO users'),
            'Query INSERT harus dieksekusi untuk menyimpan user baru'
        );

        // Total harus 2 query: SELECT (cek email) + INSERT (simpan user)
        $this->assertEquals(2, $spyDb->getQueryCount());
    }

    /**
     * @test
     * @group spy
     * SPY: verifikasi INSERT TIDAK dieksekusi jika validasi gagal
     */
    public function testRegistrasiGagalTidakMengeksekusiInsert(): void
    {
        $delegate = new DatabaseStub(stubbedNumRows: 0, querySuccess: true);
        $spyDb    = new DatabaseSpy($delegate);
        $service  = new UserService($spyDb);

        // Input tidak valid (email salah)
        $service->register('Budi', 'emailsalah', 'password123');

        // SPY: verifikasi tidak ada query database yang dieksekusi
        $this->assertEquals(
            0,
            $spyDb->getQueryCount(),
            'Tidak boleh ada query database jika validasi gagal'
        );
    }

    // ==========================================================
    // BAGIAN 6: Test dengan MOCK (PHPUnit Mock)
    // MOCK: memverifikasi bahwa metode dipanggil dengan argumen PERSIS
    // ==========================================================

    /**
     * @test
     * @group mock
     * MOCK: verifikasi escapeString dipanggil untuk setiap input yang perlu di-escape
     */
    public function testLoginMemanggilEscapeStringUntukEmail(): void
    {
        // MOCK: buat mock dari DatabaseInterface
        $mockDb = $this->createMock(DatabaseInterface::class);

        // MOCK: definisikan ekspektasi - escapeString dipanggil dengan email yang tepat
        $mockDb->expects($this->once())
            ->method('escapeString')
            ->with($this->equalTo('budi@email.com'))
            ->willReturn('budi@email.com');

        // MOCK: simulasikan query dan fetchAssoc mengembalikan null (user tidak ditemukan)
        $mockDb->expects($this->once())
            ->method('query')
            ->willReturn(true);

        $mockDb->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn(null);

        $service = new UserService($mockDb);
        $service->login('budi@email.com', 'password123');
    }

    /**
     * @test
     * @group mock
     * MOCK: verifikasi bahwa query tidak dipanggil jika email & password kosong
     */
    public function testLoginTidakMemanggilQueryJikaInputKosong(): void
    {
        $mockDb = $this->createMock(DatabaseInterface::class);

        // MOCK: query TIDAK BOLEH dipanggil jika input kosong
        $mockDb->expects($this->never())
            ->method('query');

        $service = new UserService($mockDb);
        $result  = $service->login('', '');

        $this->assertFalse($result['success']);
    }

    /**
     * @test
     * @group mock
     * MOCK: verifikasi urutan pemanggilan metode saat registrasi sukses
     */
    public function testRegistrasiBerhasilMemanggilQueryDuaKali(): void
    {
        $mockDb = $this->createMock(DatabaseInterface::class);

        // MOCK: escapeString dipanggil lebih dari sekali (untuk nama, email, password)
        $mockDb->method('escapeString')->willReturnArgument(0);

        // MOCK: query dipanggil tepat 2 kali (SELECT + INSERT)
        $mockDb->expects($this->exactly(2))
            ->method('query')
            ->willReturn(true);

        // MOCK: numRows = 0 (email belum ada)
        $mockDb->expects($this->once())
            ->method('numRows')
            ->willReturn(0);

        $service = new UserService($mockDb);
        $result  = $service->register('Budi Santoso', 'budi@email.com', 'password123');

        $this->assertTrue($result['success']);
    }
}
