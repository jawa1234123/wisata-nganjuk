<?php

/**
 * ============================================================
 * UNIT TEST: WisataServiceTest
 * ============================================================
 * Menguji class WisataService untuk operasi CRUD wisata.
 * Menggunakan teknik:
 *   - STUB   : simulasi respon database yang dikonfigurasi
 *   - SPY    : rekam dan verifikasi interaksi database
 *   - MOCK   : verifikasi pemanggilan metode yang spesifik
 *   - Equivalence Partitioning & Boundary Value Analysis
 * ============================================================
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/WisataService.php';
require_once __DIR__ . '/TestDoubles.php';

class WisataServiceTest extends TestCase
{
    // ==========================================================
    // BAGIAN 1: Test validasi data wisata
    // Menggunakan DUMMY (tidak perlu database)
    // ==========================================================

    private function makeService(DatabaseInterface $db): WisataService
    {
        return new WisataService($db);
    }

    /**
     * @test
     * @group validasi-wisata
     */
    public function testValidasiWisataDataLengkapValid(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $data     = [
            'nama'      => 'Air Terjun Roro Kuning',
            'deskripsi' => 'Wisata alam yang indah di Nganjuk',
            'lokasi'    => 'Desa Bajulan, Loceret, Nganjuk',
            'kategori'  => 'alam',
            'latitude'  => '-7.5834',
            'longitude' => '111.8956',
        ];
        $validasi = $service->validateWisata($data);

        $this->assertTrue($validasi['valid']);
        $this->assertEmpty($validasi['errors']);
    }

    /**
     * @test
     * @group validasi-wisata
     * Equivalence Partitioning: kategori tidak valid
     */
    public function testValidasiWisataKategoriTidakValid(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $data     = [
            'nama'      => 'Tempat Test',
            'deskripsi' => 'Deskripsi test',
            'lokasi'    => 'Lokasi test',
            'kategori'  => 'pantai', // tidak ada dalam daftar valid
        ];
        $validasi = $service->validateWisata($data);

        $this->assertFalse($validasi['valid']);
        $this->assertContains('Kategori tidak valid. Pilih: alam, buatan, atau religi', $validasi['errors']);
    }

    /**
     * @test
     * @group validasi-wisata
     * Equivalence Partitioning: semua kategori valid
     */
    public function testValidasiSemuaKategoriValid(): void
    {
        $service     = $this->makeService(new DummyDatabase());
        $baseData    = [
            'nama'      => 'Test',
            'deskripsi' => 'Deskripsi',
            'lokasi'    => 'Lokasi',
        ];

        foreach (['alam', 'buatan', 'religi'] as $kategori) {
            $data     = array_merge($baseData, ['kategori' => $kategori]);
            $validasi = $service->validateWisata($data);
            $this->assertTrue($validasi['valid'], "Kategori '$kategori' harus valid");
        }
    }

    /**
     * @test
     * @group validasi-wisata
     */
    public function testValidasiWisataFieldWajibKosong(): void
    {
        $service  = $this->makeService(new DummyDatabase());
        $validasi = $service->validateWisata([
            'nama'      => '',
            'deskripsi' => '',
            'lokasi'    => '',
            'kategori'  => 'alam',
        ]);

        $this->assertFalse($validasi['valid']);
        $this->assertCount(3, $validasi['errors']); // 3 field kosong
    }

    /**
     * @test
     * @group validasi-wisata
     * Boundary Value Analysis: koordinat valid vs tidak valid
     */
    public function testValidasiKoordinatNumerik(): void
    {
        $service = $this->makeService(new DummyDatabase());

        // Valid: angka (positif & negatif)
        $this->assertTrue($service->isValidKoordinat('-7.5834'));
        $this->assertTrue($service->isValidKoordinat('111.8956'));
        $this->assertTrue($service->isValidKoordinat('0'));

        // Tidak valid: bukan angka
        $this->assertFalse($service->isValidKoordinat('tujuh-koma-lima'));
        $this->assertFalse($service->isValidKoordinat('12.34.56'));
        $this->assertFalse($service->isValidKoordinat(''));
    }

    /**
     * @test
     * @group validasi-wisata
     */
    public function testValidasiKoordinatTidakValidMenambahError(): void
    {
        $service  = $this->makeService(new DummyDatabase());
        $validasi = $service->validateWisata([
            'nama'      => 'Test',
            'deskripsi' => 'Deskripsi',
            'lokasi'    => 'Lokasi',
            'kategori'  => 'alam',
            'latitude'  => 'tidak-valid-lat',
            'longitude' => 'tidak-valid-long',
        ]);

        $this->assertFalse($validasi['valid']);
        $this->assertCount(2, $validasi['errors']); // 2 error koordinat
    }

    // ==========================================================
    // BAGIAN 2: Test tambahWisata menggunakan STUB
    // ==========================================================

    /**
     * @test
     * @group tambah-wisata
     * STUB: database insert berhasil
     */
    public function testTambahWisataBerhasilDenganDataValid(): void
    {
        $stubDb  = new DatabaseStub(querySuccess: true);
        $service = $this->makeService($stubDb);

        $result = $service->tambahWisata([
            'nama'      => 'Air Terjun Roro Kuning',
            'deskripsi' => 'Keindahan alam Nganjuk yang tiada duanya',
            'lokasi'    => 'Desa Bajulan, Loceret, Nganjuk',
            'kategori'  => 'alam',
            'gambar'    => 'roro_kuning.jpg',
            'latitude'  => '-7.5834',
            'longitude' => '111.8956',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Wisata berhasil ditambahkan', $result['message']);
    }

    /**
     * @test
     * @group tambah-wisata
     * STUB: database insert gagal
     */
    public function testTambahWisataGagalJikaDatabaseGagal(): void
    {
        $stubDb  = new DatabaseStub(querySuccess: false);
        $service = $this->makeService($stubDb);

        $result = $service->tambahWisata([
            'nama'      => 'Air Terjun Roro Kuning',
            'deskripsi' => 'Deskripsi wisata',
            'lokasi'    => 'Nganjuk',
            'kategori'  => 'alam',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal menyimpan', $result['message']);
    }

    /**
     * @test
     * @group tambah-wisata
     */
    public function testTambahWisataGagalJikaDataTidakValid(): void
    {
        $stubDb  = new DatabaseStub(querySuccess: true);
        $service = $this->makeService($stubDb);

        $result = $service->tambahWisata([
            'nama'      => '',       // kosong -> tidak valid
            'deskripsi' => 'Desc',
            'lokasi'    => 'Lokasi',
            'kategori'  => 'alam',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Nama wisata tidak boleh kosong', $result['message']);
    }

    // ==========================================================
    // BAGIAN 3: Test getWisataById dengan STUB
    // ==========================================================

    /**
     * @test
     * @group get-wisata
     * STUB: data wisata ditemukan
     */
    public function testGetWisataByIdMenemukanData(): void
    {
        $wisataRow = [
            'id'        => 5,
            'nama'      => 'Makam Sunan Nganjuk',
            'kategori'  => 'religi',
            'lokasi'    => 'Nganjuk Kota',
            'deskripsi' => 'Situs religi bersejarah',
        ];

        $stubDb  = new DatabaseStub(stubbedRow: $wisataRow);
        $service = $this->makeService($stubDb);

        $result = $service->getWisataById(5);

        $this->assertNotNull($result);
        $this->assertEquals('Makam Sunan Nganjuk', $result['nama']);
        $this->assertEquals('religi', $result['kategori']);
    }

    /**
     * @test
     * @group get-wisata
     * STUB: data tidak ditemukan (null)
     */
    public function testGetWisataByIdMengembalikanNullJikaTidakAda(): void
    {
        $stubDb  = new DatabaseStub(stubbedRow: null);
        $service = $this->makeService($stubDb);

        $result = $service->getWisataById(999);

        $this->assertNull($result);
    }

    /**
     * @test
     * @group get-wisata
     * Boundary Value Analysis: ID tidak valid (0 dan negatif)
     */
    public function testGetWisataByIdDenganIdTidakValid(): void
    {
        $stubDb  = new DatabaseStub();
        $service = $this->makeService($stubDb);

        $this->assertNull($service->getWisataById(0));
        $this->assertNull($service->getWisataById(-1));
        $this->assertNull($service->getWisataById(-999));
    }

    // ==========================================================
    // BAGIAN 4: Test filterHasKoordinat (pure function - tanpa DB)
    // ==========================================================

    /**
     * @test
     * @group filter
     */
    public function testFilterHasKoordinatHanyaMengembalikanDataBerkoordinat(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $wisataList = [
            ['id' => 1, 'nama' => 'Tempat A', 'latitude' => '-7.123', 'longitude' => '111.456'],
            ['id' => 2, 'nama' => 'Tempat B', 'latitude' => '',       'longitude' => ''],
            ['id' => 3, 'nama' => 'Tempat C', 'latitude' => '-7.456', 'longitude' => '111.789'],
            ['id' => 4, 'nama' => 'Tempat D', 'latitude' => null,     'longitude' => null],
        ];

        $result = $service->filterHasKoordinat($wisataList);
        $result = array_values($result); // reset keys

        $this->assertCount(2, $result);
        $this->assertEquals('Tempat A', $result[0]['nama']);
        $this->assertEquals('Tempat C', $result[1]['nama']);
    }

    /**
     * @test
     * @group filter
     */
    public function testFilterHasKoordinatMengembalikanArrayKosongJikaSemuaTanpaKoordinat(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $wisataList = [
            ['id' => 1, 'nama' => 'A', 'latitude' => '', 'longitude' => ''],
            ['id' => 2, 'nama' => 'B', 'latitude' => '', 'longitude' => ''],
        ];

        $result = $service->filterHasKoordinat($wisataList);
        $this->assertEmpty($result);
    }

    // ==========================================================
    // BAGIAN 5: Test formatNamaGambar
    // ==========================================================

    /**
     * @test
     * @group format-gambar
     */
    public function testFormatNamaGambarMenghasilkanFormatBerTimestamp(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $result = $service->formatNamaGambar('foto wisata.JPG');

        // Harus mengandung timestamp (angka) di awal
        $this->assertMatchesRegularExpression('/^\d+_/', $result);

        // Ekstensi harus lowercase
        $this->assertStringEndsWith('.jpg', $result);
    }

    /**
     * @test
     * @group format-gambar
     */
    public function testFormatNamaGambarMembersihkanKarakterSpesial(): void
    {
        $service = $this->makeService(new DummyDatabase());

        $result = $service->formatNamaGambar('gambar (1) wisata!.png');

        // Tidak boleh ada spasi atau tanda kurung
        $this->assertDoesNotMatchRegularExpression('/[\s\(\)!]/', $result);
    }

    // ==========================================================
    // BAGIAN 6: Test dengan SPY untuk hapusWisata dan updateWisata
    // ==========================================================

    /**
     * @test
     * @group spy-wisata
     * SPY: verifikasi query DELETE dieksekusi saat hapus wisata
     */
    public function testHapusWisataMengeksekusiQueryDelete(): void
    {
        $delegate = new DatabaseStub(querySuccess: true);
        $spy      = new DatabaseSpy($delegate);
        $service  = $this->makeService($spy);

        $result = $service->hapusWisata(3);

        $this->assertTrue($result['success']);
        $this->assertTrue(
            $spy->wasQueryExecuted('DELETE FROM wisata'),
            'Query DELETE harus dieksekusi'
        );
        $this->assertTrue(
            $spy->wasQueryExecuted("WHERE id='3'"),
            'Query harus menggunakan ID yang benar'
        );
    }

    /**
     * @test
     * @group spy-wisata
     * SPY: verifikasi hapus dengan ID tidak valid tidak menyentuh database
     */
    public function testHapusWisataDenganIdTidakValidTidakMenyentuhDatabase(): void
    {
        $delegate = new DatabaseStub(querySuccess: true);
        $spy      = new DatabaseSpy($delegate);
        $service  = $this->makeService($spy);

        $result = $service->hapusWisata(0);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $spy->getQueryCount(), 'Tidak boleh ada query jika ID tidak valid');
    }

    /**
     * @test
     * @group spy-wisata
     * SPY: verifikasi query UPDATE dieksekusi dengan data yang benar
     */
    public function testUpdateWisataMengeksekusiQueryUpdate(): void
    {
        $delegate = new DatabaseStub(querySuccess: true);
        $spy      = new DatabaseSpy($delegate);
        $service  = $this->makeService($spy);

        $data = [
            'nama'      => 'Bendungan Semantok',
            'deskripsi' => 'Bendungan terbesar di Asia Tenggara',
            'lokasi'    => 'Nganjuk',
            'kategori'  => 'buatan',
            'latitude'  => '-7.6102',
            'longitude' => '111.9245',
        ];

        $result = $service->updateWisata(2, $data);

        $this->assertTrue($result['success']);
        $this->assertTrue(
            $spy->wasQueryExecuted('UPDATE wisata'),
            'Query UPDATE harus dieksekusi'
        );
    }

    // ==========================================================
    // BAGIAN 7: Test dengan MOCK untuk getAllWisata
    // ==========================================================

    /**
     * @test
     * @group mock-wisata
     * MOCK: verifikasi query dipanggil tanpa filter kategori
     */
    public function testGetAllWisataTanpaFilterMemanggilQueryYangBenar(): void
    {
        $mockDb = $this->createMock(DatabaseInterface::class);

        $mockDb->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT * FROM wisata'))
            ->willReturn(true);

        $mockDb->method('fetchAssoc')->willReturn(null); // tidak ada data

        $service = $this->makeService($mockDb);
        $result  = $service->getAllWisata();

        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group mock-wisata
     * MOCK: verifikasi filter kategori digunakan dalam query
     */
    public function testGetAllWisataDenganKategoriMenggunakanFilter(): void
    {
        $mockDb = $this->createMock(DatabaseInterface::class);

        $mockDb->method('escapeString')->willReturnArgument(0);

        $mockDb->expects($this->once())
            ->method('query')
            ->with($this->stringContains("AND kategori='alam'"))
            ->willReturn(true);

        $mockDb->method('fetchAssoc')->willReturn(null);

        $service = $this->makeService($mockDb);
        $service->getAllWisata('alam');
    }
}
