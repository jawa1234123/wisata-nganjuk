<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-004 Wisata Ticket Booking
 */
class REQ004_WisataBookingTest extends TestCase
{
    // FITUR 4: REQ-004 Wisata Ticket Booking
    // ====================================================================

    /**
     * @test
     * @group req-004
     * REQ-004: Pemesanan tiket wisata berhasil pada kuota dan tanggal valid
     */
    public function testReq004BookingWisataBerhasil(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new BookingService($spyDb);

        $besok = date('Y-m-d', strtotime('+1 day'));
        $result = $service->bookWisata(1, 10, $besok, 5, 10000);

        $this->assertTrue($result['success']);
        $this->assertEquals(50000, $result['total_harga']);
        $this->assertEquals('pending', $result['status']);
        $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO pemesanan'));
    }

    /**
     * @test
     * @group req-004
     * REQ-004: Pemesanan tiket wisata ditolak jika jumlah orang di luar batas 1..50 (BVA: 0 & 51)
     */
    public function testReq004BookingWisataJumlahOrangDiLuarBatas(): void
    {
        $service = new BookingService(new DummyDatabase());
        $besok = date('Y-m-d', strtotime('+1 day'));

        $resNol = $service->bookWisata(1, 10, $besok, 0, 10000);
        $this->assertFalse($resNol['success']);
        $this->assertEquals('Jumlah tiket harus antara 1 sampai 50 orang', $resNol['message']);

        $resLebih = $service->bookWisata(1, 10, $besok, 51, 10000);
        $this->assertFalse($resLebih['success']);
        $this->assertEquals('Jumlah tiket harus antara 1 sampai 50 orang', $resLebih['message']);
    }

    /**
     * @test
     * @group req-004
     * REQ-004: Pemesanan tiket wisata ditolak jika tanggal lampau
     */
    public function testReq004BookingWisataTanggalLampauDitolak(): void
    {
        $service = new BookingService(new DummyDatabase());
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        $result = $service->bookWisata(1, 10, $kemarin, 2, 10000);

        $this->assertFalse($result['success']);
        $this->assertEquals('Tanggal booking tidak valid atau tanggal lampau', $result['message']);
    }


}
