<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-003 Forgot Password
 */
class REQ003_ForgotPasswordTest extends TestCase
{
    // FITUR 3: REQ-003 Forgot Password
    // ====================================================================

    /**
     * @test
     * @group req-003
     * REQ-003: Reset password berhasil dengan email valid dan sandi baru cocok
     */
    public function testReq003ResetPasswordBerhasil(): void
    {
        $stubUser = ['id' => 1, 'email' => 'faizal@gmail.com', 'nama' => 'Faizal'];
        $stubDb = new DatabaseStub($stubUser, 1, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new UserService($spyDb);

        $result = $service->resetPassword('faizal@gmail.com', 'PasswordBaru123', 'PasswordBaru123');

        $this->assertTrue($result['success']);
        $this->assertEquals('Password berhasil diubah', $result['message']);
        $this->assertTrue($spyDb->wasQueryExecuted('UPDATE users SET password='));
    }

    /**
     * @test
     * @group req-003
     * REQ-003: Reset password gagal jika konfirmasi password tidak cocok
     */
    public function testReq003ResetPasswordGagalKonfirmasiTidakCocok(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->resetPassword('faizal@gmail.com', 'Password123', 'PasswordBerbeda456');

        $this->assertFalse($result['success']);
        $this->assertEquals('Konfirmasi password tidak cocok', $result['message']);
    }

    /**
     * @test
     * @group req-003
     * REQ-003: Reset password gagal jika password baru < 6 karakter
     */
    public function testReq003ResetPasswordGagalPasswordKurangDariEnamKarakter(): void
    {
        $service = new UserService(new DummyDatabase());

        $result = $service->resetPassword('faizal@gmail.com', '123', '123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Password baru minimal 6 karakter', $result['message']);
    }

    /**
     * @test
     * @group req-003
     * REQ-003: Reset password gagal jika email tidak ditemukan
     */
    public function testReq003ResetPasswordGagalEmailTidakDitemukan(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $service = new UserService($stubDb);

        $result = $service->resetPassword('tidakada@gmail.com', 'PasswordBaru123', 'PasswordBaru123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email tidak terdaftar', $result['message']);
    }


}
