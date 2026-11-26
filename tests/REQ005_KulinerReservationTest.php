<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-005 Kuliner Table Reservation
 */
class REQ005_KulinerReservationTest extends TestCase
{
    // FITUR 5: REQ-005 Kuliner Table Reservation
    // ====================================================================

    /**
     * @test
     * @group req-005
     * REQ-005: Reservasi meja kuliner berhasil, otomatis terkonfirmasi dan total tagihan Rp 0
     */
    public function testReq005ReservasiKulinerBerhasilTotalNol(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new BookingService($spyDb);

        $besok = date('Y-m-d', strtotime('+2 days'));
        $result = $service->reserveKuliner(1, 5, $besok, 4);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['total_harga']);
        $this->assertEquals('terkonfirmasi', $result['status']);
        $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO pemesanan'));
    }

    /**
     * @test
     * @group req-005
     * REQ-005: Reservasi kuliner ditolak jika jumlah meja di luar batas 1..20 orang (BVA: 0 & 21)
     */
    public function testReq005ReservasiKulinerJumlahOrangDiLuarBatas(): void
    {
        $service = new BookingService(new DummyDatabase());
        $besok = date('Y-m-d', strtotime('+2 days'));

        $resNol = $service->reserveKuliner(1, 5, $besok, 0);
        $this->assertFalse($resNol['success']);
        $this->assertEquals('Jumlah reservasi meja harus antara 1 sampai 20 orang', $resNol['message']);

        $resLebih = $service->reserveKuliner(1, 5, $besok, 21);
        $this->assertFalse($resLebih['success']);
        $this->assertEquals('Jumlah reservasi meja harus antara 1 sampai 20 orang', $resLebih['message']);
    }


}
