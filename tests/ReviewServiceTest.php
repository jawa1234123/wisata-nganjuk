<?php

/**
 * ============================================================
 * UNIT TEST: ReviewServiceTest
 * ============================================================
 * Menguji class ReviewService untuk fitur review & rating wisata.
 * Berfokus pada:
 *   - STUB : simulasi database untuk review
 *   - MOCK : verifikasi pemanggilan database
 *   - Pure function testing (hitungRataRating, formatBintang)
 *   - Boundary Value Analysis untuk validasi rating (1-5)
 * ============================================================
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ReviewService.php';
require_once __DIR__ . '/TestDoubles.php';

class ReviewServiceTest extends TestCase
{
    // ==========================================================
    // BAGIAN 1: Test validasi review (tanpa database - DUMMY)
    // ==========================================================

    /**
     * @test
     * @group validasi-review
     * Boundary Value Analysis: batas bawah dan atas rating
     */
    public function testRatingValidDiBatasBawahDanAtas(): void
    {
        $service = new ReviewService(new DummyDatabase());

        // Tepat di batas bawah (1) -> valid
        $this->assertTrue($service->isValidRating(1));

        // Tepat di batas atas (5) -> valid
        $this->assertTrue($service->isValidRating(5));

        // Di tengah -> valid
        $this->assertTrue($service->isValidRating(3));
    }

    /**
     * @test
     * @group validasi-review
     * Boundary Value Analysis: di luar batas rating
     */
    public function testRatingTidakValidDiLuarBatas(): void
    {
        $service = new ReviewService(new DummyDatabase());

        // Tepat 1 di bawah batas bawah (0) -> tidak valid
        $this->assertFalse($service->isValidRating(0));

        // Tepat 1 di atas batas atas (6) -> tidak valid
        $this->assertFalse($service->isValidRating(6));

        // Negatif -> tidak valid
        $this->assertFalse($service->isValidRating(-1));

        // Sangat besar -> tidak valid
        $this->assertFalse($service->isValidRating(100));
    }

    /**
     * @test
     * @group validasi-review
     */
    public function testKomentarValidMinimalLimaKarakter(): void
    {
        $service = new ReviewService(new DummyDatabase());

        // Tepat 5 karakter -> valid
        $this->assertTrue($service->isValidKomentar('Bagus'));

        // Lebih dari 5 -> valid
        $this->assertTrue($service->isValidKomentar('Tempat yang sangat indah dan menarik!'));
    }

    /**
     * @test
     * @group validasi-review
     * Boundary Value Analysis: komentar terlalu pendek
     */
    public function testKomentarTidakValidKurangDariLimaKarakter(): void
    {
        $service = new ReviewService(new DummyDatabase());

        // 4 karakter (1 di bawah batas) -> tidak valid
        $this->assertFalse($service->isValidKomentar('Oke!'));

        // Kosong -> tidak valid
        $this->assertFalse($service->isValidKomentar(''));

        // Spasi saja -> tidak valid
        $this->assertFalse($service->isValidKomentar('    '));
    }

    // ==========================================================
    // BAGIAN 2: Test tambahReview dengan STUB
    // ==========================================================

    /**
     * @test
     * @group tambah-review
     * STUB: insert berhasil
     */
    public function testTambahReviewBerhasilDenganDataValid(): void
    {
        $stubDb  = new DatabaseStub(querySuccess: true);
        $service = new ReviewService($stubDb);

        $result = $service->tambahReview(
            userId:   1,
            wisataId: 3,
            rating:   5,
            komentar: 'Tempat yang sangat indah dan sejuk!'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('Review berhasil ditambahkan', $result['message']);
    }

    /**
     * @test
     * @group tambah-review
     * STUB: insert gagal
     */
    public function testTambahReviewGagalJikaDatabaseGagal(): void
    {
        $stubDb  = new DatabaseStub(querySuccess: false);
        $service = new ReviewService($stubDb);

        $result = $service->tambahReview(1, 3, 4, 'Cukup menyenangkan');

        $this->assertFalse($result['success']);
        $this->assertEquals('Gagal menyimpan review', $result['message']);
    }

    /**
     * @test
     * @group tambah-review
     * Equivalence Partitioning: berbagai kondisi input tidak valid
     */
    public function testTambahReviewGagalDenganInputTidakValid(): void
    {
        $stubDb  = new DatabaseStub();
        $service = new ReviewService($stubDb);

        // User ID tidak valid
        $result = $service->tambahReview(0, 3, 5, 'Komentar panjang');
        $this->assertFalse($result['success']);
        $this->assertEquals('User tidak valid', $result['message']);

        // Wisata ID tidak valid
        $result = $service->tambahReview(1, -1, 5, 'Komentar panjang');
        $this->assertFalse($result['success']);
        $this->assertEquals('ID wisata tidak valid', $result['message']);

        // Rating tidak valid
        $result = $service->tambahReview(1, 3, 0, 'Komentar panjang');
        $this->assertFalse($result['success']);
        $this->assertEquals('Rating harus antara 1 sampai 5', $result['message']);

        // Komentar terlalu pendek
        $result = $service->tambahReview(1, 3, 5, 'Oke');
        $this->assertFalse($result['success']);
        $this->assertEquals('Komentar minimal 5 karakter', $result['message']);
    }

    // ==========================================================
    // BAGIAN 3: Test hitungRataRating (pure function - tanpa DB)
    // ==========================================================

    /**
     * @test
     * @group hitung-rating
     */
    public function testHitungRataRatingDenganBeberpaReview(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $reviews = [
            ['rating' => 5],
            ['rating' => 4],
            ['rating' => 3],
            ['rating' => 4],
            ['rating' => 4],
        ];

        $rata = $service->hitungRataRating($reviews);

        // (5+4+3+4+4) / 5 = 20/5 = 4.0
        $this->assertEquals(4.0, $rata);
    }

    /**
     * @test
     * @group hitung-rating
     */
    public function testHitungRataRatingDenganSatuReview(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $reviews = [['rating' => 3]];
        $rata    = $service->hitungRataRating($reviews);

        $this->assertEquals(3.0, $rata);
    }

    /**
     * @test
     * @group hitung-rating
     * Boundary Value Analysis: array kosong
     */
    public function testHitungRataRatingDenganArrayKosongMengembalikanNol(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $rata = $service->hitungRataRating([]);

        $this->assertEquals(0.0, $rata);
    }

    /**
     * @test
     * @group hitung-rating
     */
    public function testHitungRataRatingDibulatkanSatuDesimal(): void
    {
        $service = new ReviewService(new DummyDatabase());

        $reviews = [
            ['rating' => 5],
            ['rating' => 4],
            ['rating' => 5],
        ];

        // (5+4+5)/3 = 14/3 = 4.666... -> dibulatkan jadi 4.7
        $rata = $service->hitungRataRating($reviews);
        $this->assertEquals(4.7, $rata);
    }

    // ==========================================================
    // BAGIAN 4: Test formatBintang (pure function)
    // ==========================================================

    /**
     * @test
     * @group format-bintang
     */
    public function testFormatBintangLimaMenghasilkanLimaBintangPenuh(): void
    {
        $service = new ReviewService(new DummyDatabase());
        $result  = $service->formatBintang(5.0);
        $this->assertEquals('★★★★★', $result);
    }

    /**
     * @test
     * @group format-bintang
     */
    public function testFormatBintangNolMenghasilkanLimaBintangKosong(): void
    {
        $service = new ReviewService(new DummyDatabase());
        $result  = $service->formatBintang(0.0);
        $this->assertEquals('☆☆☆☆☆', $result);
    }

    /**
     * @test
     * @group format-bintang
     */
    public function testFormatBintangTigaMenghasilkanTigaPenuhDuaKosong(): void
    {
        $service = new ReviewService(new DummyDatabase());
        $result  = $service->formatBintang(3.0);
        $this->assertEquals('★★★☆☆', $result);
    }

    /**
     * @test
     * @group format-bintang
     */
    public function testFormatBintangDenganRataRataDesimal(): void
    {
        $service = new ReviewService(new DummyDatabase());

        // 4.3 -> dibulatkan ke 4 -> ★★★★☆
        $result = $service->formatBintang(4.3);
        $this->assertEquals('★★★★☆', $result);

        // 4.6 -> dibulatkan ke 5 -> ★★★★★
        $result = $service->formatBintang(4.6);
        $this->assertEquals('★★★★★', $result);
    }

    // ==========================================================
    // BAGIAN 5: Test getReviewByWisata dengan MOCK
    // ==========================================================

    /**
     * @test
     * @group mock-review
     * MOCK: verifikasi query dipanggil dengan wisata_id yang benar
     */
    public function testGetReviewByWisataMemanggilQueryDenganIdBenar(): void
    {
        $mockDb = $this->createMock(DatabaseInterface::class);

        $mockDb->expects($this->once())
            ->method('query')
            ->with($this->stringContains("wisata_id='5'"))
            ->willReturn(true);

        $mockDb->method('fetchAssoc')->willReturn(null);

        $service = new ReviewService($mockDb);
        $result  = $service->getReviewByWisata(5);

        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group mock-review
     * MOCK: verifikasi hasil review dikembalikan dengan benar
     */
    public function testGetReviewByWisataMengembalikanDaftarReview(): void
    {
        $mockDb   = $this->createMock(DatabaseInterface::class);
        $reviews  = [
            ['id' => 1, 'wisata_id' => 5, 'rating' => 5, 'komentar' => 'Sangat indah!'],
            ['id' => 2, 'wisata_id' => 5, 'rating' => 4, 'komentar' => 'Rekomendasikan!'],
        ];

        $mockDb->method('query')->willReturn(true);

        // MOCK: fetchAssoc dipanggil berulang, kembalikan data satu per satu lalu null
        $mockDb->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls($reviews[0], $reviews[1], null);

        $service = new ReviewService($mockDb);
        $result  = $service->getReviewByWisata(5);

        $this->assertCount(2, $result);
        $this->assertEquals('Sangat indah!', $result[0]['komentar']);
        $this->assertEquals('Rekomendasikan!', $result[1]['komentar']);
    }

    // ==========================================================
    // BAGIAN 6: Test tambahReview dengan SPY
    // ==========================================================

    /**
     * @test
     * @group spy-review
     * SPY: verifikasi komentar di-escape sebelum disimpan ke database
     */
    public function testTambahReviewMelakukanEscapeKomentar(): void
    {
        $delegate = new DatabaseStub(querySuccess: true);
        $spy      = new DatabaseSpy($delegate);
        $service  = new ReviewService($spy);

        $komentar = "Tempat yang 'indah' dan \"menarik\"!";
        $service->tambahReview(1, 2, 5, $komentar);

        // SPY: verifikasi komentar di-escape
        $escapeLog = $spy->getEscapeLog();
        $this->assertContains($komentar, $escapeLog, 'Komentar harus di-escape sebelum disimpan');
    }

    /**
     * @test
     * @group spy-review
     * SPY: verifikasi query INSERT dieksekusi dengan benar
     */
    public function testTambahReviewMengeksekusiInsertQuery(): void
    {
        $delegate = new DatabaseStub(querySuccess: true);
        $spy      = new DatabaseSpy($delegate);
        $service  = new ReviewService($spy);

        $service->tambahReview(3, 7, 4, 'Pemandangannya indah sekali!');

        $this->assertTrue(
            $spy->wasQueryExecuted('INSERT INTO review'),
            'Harus ada query INSERT INTO review'
        );

        $queryLog = $spy->getQueryLog();
        $this->assertStringContainsString('3', $queryLog[0]);   // userId
        $this->assertStringContainsString('7', $queryLog[0]);   // wisataId
        $this->assertStringContainsString('4', $queryLog[0]);   // rating
    }
}
