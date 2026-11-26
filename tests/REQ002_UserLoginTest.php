<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-002 User Login
 */
class REQ002_UserLoginTest extends TestCase
{
    // FITUR 2: REQ-002 User Login
    // ====================================================================

    /**
     * @test
     * @group req-002
     * REQ-002: Login berhasil dengan email dan password yang cocok
     */
    public function testReq002LoginBerhasilDenganKredensialBenar(): void
    {
        $hash = password_hash('Rahasia123', PASSWORD_DEFAULT);
        $stubUser = [
            'id' => 1,
            'nama' => 'Faizal Qadri',
            'email' => 'faizal@gmail.com',
            'password' => $hash,
            'role' => 'user'
        ];

        $stubDb = new DatabaseStub($stubUser, 1, true);
        $service = new UserService($stubDb);

        $result = $service->login('faizal@gmail.com', 'Rahasia123');

        $this->assertTrue($result['success']);
        $this->assertEquals('Login berhasil', $result['message']);
        $this->assertNotNull($result['user']);
        $this->assertEquals('Faizal Qadri', $result['user']['nama']);
    }

    /**
     * @test
     * @group req-002
     * REQ-002: Login gagal jika email tidak terdaftar di basis data
     */
    public function testReq002LoginGagalEmailTidakDitemukan(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $service = new UserService($stubDb);

        $result = $service->login('tidakada@gmail.com', 'Rahasia123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email atau password salah', $result['message']);
        $this->assertNull($result['user']);
    }

    /**
     * @test
     * @group req-002
     * REQ-002: Login gagal jika password salah (Password Mismatch)
     */
    public function testReq002LoginGagalPasswordSalah(): void
    {
        $hash = password_hash('RahasiaAsli', PASSWORD_DEFAULT);
        $stubUser = ['id' => 1, 'nama' => 'Faizal', 'email' => 'faizal@gmail.com', 'password' => $hash];

        $stubDb = new DatabaseStub($stubUser, 1, true);
        $service = new UserService($stubDb);

        $result = $service->login('faizal@gmail.com', 'PasswordKeliru');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email atau password salah', $result['message']);
    }

    /**
     * @test
     * @group req-002
     * REQ-002: Login gagal jika email atau password kosong
     */
    public function testReq002LoginGagalInputKosong(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->login('', '');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email dan password harus diisi', $result['message']);
    }


}
