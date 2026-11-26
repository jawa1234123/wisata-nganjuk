<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-007 Payment Simulation
 */
class REQ007_PaymentSimulationTest extends TestCase
{
    // FITUR 7: REQ-007 Payment Simulation
    // ====================================================================

    /**
     * @test
     * @group req-007
     * REQ-007: Simulasi pembayaran berhasil dan mengubah status menjadi 'dibayar'
     */
    public function testReq007PembayaranBerhasilStatusBerubah(): void
    {
        $stubOrder = [
            'id' => 50,
            'user_id' => 1,
            'total_harga' => 50000,
            'status_pembayaran' => 'pending'
        ];

        $stubDb = new DatabaseStub($stubOrder, 1, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new BookingService($spyDb);

        $result = $service->bayarPesanan(50, 1, 50000);

        $this->assertTrue($result['success']);
        $this->assertEquals('dibayar', $result['status_pembayaran']);
        $this->assertEquals(0, $result['kembalian']);
        $this->assertTrue($spyDb->wasQueryExecuted("status_pembayaran='dibayar'"));
    }

    /**
     * @test
     * @group req-007
     * REQ-007: Pembayaran ditolak jika nominal bayar kurang dari total tagihan
     */
    public function testReq007PembayaranKurangDitolak(): void
    {
        $stubOrder = [
            'id' => 50,
            'user_id' => 1,
            'total_harga' => 50000,
            'status_pembayaran' => 'pending'
        ];

        $stubDb = new DatabaseStub($stubOrder, 1, true);
        $service = new BookingService($stubDb);

        $result = $service->bayarPesanan(50, 1, 30000); // Kurang 20.000

        $this->assertFalse($result['success']);
        $this->assertEquals('Nominal pembayaran kurang dari total tagihan', $result['message']);
    }

    /**
     * @test
     * @group req-007
     * REQ-007: Pembayaran ditolak jika pesanan sudah berstatus 'dibayar' sebelumnya
     */
    public function testReq007PembayaranPesananSudahLunasDitolak(): void
    {
        $stubOrder = [
            'id' => 50,
            'user_id' => 1,
            'total_harga' => 50000,
            'status_pembayaran' => 'dibayar'
        ];

        $stubDb = new DatabaseStub($stubOrder, 1, true);
        $service = new BookingService($stubDb);

        $result = $service->bayarPesanan(50, 1, 50000);

        $this->assertFalse($result['success']);
        $this->assertEquals('Pesanan ini sudah dibayar sebelumnya', $result['message']);
    }


}
