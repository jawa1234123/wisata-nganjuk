<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-001 User Registration
 */
class REQ001_UserRegistrationTest extends TestCase
{
    // FITUR 1: REQ-001 User Registration
    // ====================================================================

    /**
     * @test
     * @group req-001
     * REQ-001: Registrasi berhasil dengan data valid (Nama >= 3 char, sandi >= 6 char, email unik)
     */
    public function testReq001RegistrasiBerhasilDenganDataValid(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new UserService($spyDb);

        $result = $service->register('Faizal Qadri', 'faizal@gmail.com', 'Sandi123!');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registrasi berhasil', $result['message']);
        $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO users'));
    }

    /**
     * @test
     * @group req-001
     * REQ-001: Registrasi gagal jika panjang nama < 3 karakter (BVA Bawah: 2 char)
     */
    public function testReq001RegistrasiGagalNamaTerlaluPendek(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->register('Al', 'al@gmail.com', 'Sandi123!');

        $this->assertFalse($result['success']);
        $this->assertEquals('Nama minimal 3 karakter', $result['message']);
    }

    /**
     * @test
     * @group req-001
     * REQ-001: Registrasi gagal jika format email tidak valid (EP Invalid)
     */
    public function testReq001RegistrasiGagalFormatEmailTidakValid(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->register('Faizal Qadri', 'faizal-bukan-email', 'Sandi123!');

        $this->assertFalse($result['success']);
        $this->assertEquals('Format email tidak valid', $result['message']);
    }

    /**
     * @test
     * @group req-001
     * REQ-001: Registrasi gagal jika password < 6 karakter (BVA Bawah: 5 char)
     */
    public function testReq001RegistrasiGagalPasswordKurangDariEnamKarakter(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->register('Faizal Qadri', 'faizal@gmail.com', '12345');

        $this->assertFalse($result['success']);
        $this->assertEquals('Password minimal 6 karakter', $result['message']);
    }

    /**
     * @test
     * @group req-001
     * REQ-001: Registrasi gagal jika email sudah terdaftar di sistem (Cek Keunikan Email)
     */
    public function testReq001RegistrasiGagalEmailSudahTerdaftar(): void
    {
        // DatabaseStub mengembalikan numRows = 1 (email terduplikasi)
        $stubDb = new DatabaseStub(null, 1, true);
        $service = new UserService($stubDb);

        $result = $service->register('Faizal Qadri', 'duplikat@gmail.com', 'Sandi123!');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email sudah terdaftar', $result['message']);
    }


}
