<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/BookingService.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

/**
 * Test Suite untuk REQ-008 User Review & Rating
 */
class REQ008_UserReviewTest extends TestCase
{
    // FITUR 8: REQ-008 User Review & Rating
    // ====================================================================

    /**
     * @test
     * @group req-008
     * REQ-008: Menambahkan rating dan review berhasil dengan bintang dan komentar valid
     */
    public function testReq008TambahReviewBerhasil(): void
    {
        $stubDb = new DatabaseStub(null, 0, true);
        $spyDb  = new DatabaseSpy($stubDb);
        $service = new ReviewService($spyDb);

        $result = $service->tambahReview(1, 10, 5, 'Pemandangan sangat asri dan sejuk!');

        $this->assertTrue($result['success']);
        $this->assertEquals('Review berhasil ditambahkan', $result['message']);
        $this->assertTrue($spyDb->wasQueryExecuted('INSERT INTO review'));
    }

    /**
     * @test
     * @group req-008
     * REQ-008: Rating ditolak jika di luar skala 1 s.d. 5 (BVA: 0 & 6)
     */
    public function testReq008RatingDiLuarSkalaDitolak(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $resNol = $service->tambahReview(1, 10, 0, 'Komentar cukup panjang');
        $this->assertFalse($resNol['success']);
        $this->assertEquals('Rating harus antara 1 sampai 5', $resNol['message']);

        $resEnam = $service->tambahReview(1, 10, 6, 'Komentar cukup panjang');
        $this->assertFalse($resEnam['success']);
        $this->assertEquals('Rating harus antara 1 sampai 5', $resEnam['message']);
    }

    /**
     * @test
     * @group req-008
     * REQ-008: Komentar ditolak jika kurang dari batas minimal 5 karakter (BVA: 4 char)
     */
    public function testReq008KomentarKurangDariLimaKarakterDitolak(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $result = $service->tambahReview(1, 10, 5, 'Baik'); // 4 karakter

        $this->assertFalse($result['success']);
        $this->assertEquals('Komentar minimal 5 karakter', $result['message']);
    }

    /**
     * @test
     * @group req-008
     * REQ-008: Review ditolak jika user belum login / invalid ID (User ID <= 0)
     */
    public function testReq008ReviewUserBelumLoginDitolak(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $result = $service->tambahReview(0, 10, 5, 'Wisata sangat menyenangkan');

        $this->assertFalse($result['success']);
        $this->assertEquals('User tidak valid', $result['message']);
    }
}
