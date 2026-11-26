<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-006 Order History & Access Isolation
 */
class REQ006_OrderHistoryTest extends TestCase
{
    // FITUR 6: REQ-006 Order History & Access Isolation
    // ====================================================================

    /**
     * @test
     * @group req-006
     * REQ-006: Menampilkan daftar riwayat pemesanan hanya milik user yang sedang aktif
     */
    public function testReq006RiwayatPemesananSesuaiUserAktif(): void
    {
        $stubRows = [
            ['id' => 101, 'user_id' => 1, 'tipe' => 'wisata', 'nama_item' => 'Air Terjun Sedudo', 'total_harga' => 20000],
            ['id' => 102, 'user_id' => 1, 'tipe' => 'kuliner', 'nama_item' => 'Nasi Becek Nganjuk', 'total_harga' => 0]
        ];

        $stubDb = new class($stubRows) implements DatabaseInterface {
            private array $rows;
            private int $pointer = 0;
            public function __construct(array $rows) { $this->rows = $rows; }
            public function query(string $sql): mixed { $this->pointer = 0; return true; }
            public function fetchAssoc(mixed $result): ?array {
                return $this->pointer < count($this->rows) ? $this->rows[$this->pointer++] : null;
            }
            public function numRows(mixed $result): int { return count($this->rows); }
            public function escapeString(string $str): string { return $str; }
            public function getLastError(): string { return ''; }
        };

        $service = new BookingService($stubDb);
        $riwayat = $service->getRiwayatUser(1);

        $this->assertCount(2, $riwayat);
        $this->assertEquals('Air Terjun Sedudo', $riwayat[0]['nama_item']);
        $this->assertEquals('Nasi Becek Nganjuk', $riwayat[1]['nama_item']);
    }

    /**
     * @test
     * @group req-006
     * REQ-006: Isolasi akses IDOR - Detail pemesanan bernilai null jika diakses user yang tidak berhak
     */
    public function testReq006IsolasiAksesMencegahAksesPesananOrangLain(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $service = new BookingService($stubDb);

        // User ID 2 mencoba mengakses order ID 101 milik User ID 1
        $detail = $service->getDetailPemesanan(101, 2);

        $this->assertNull($detail);
    }


}
