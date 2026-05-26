<?php

require_once __DIR__ . '/Database.php';

/**
 * Service untuk mengelola Review dan Rating wisata
 */
class ReviewService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Validasi rating (harus antara 1 s.d. 5)
     */
    public function isValidRating(mixed $rating): bool
    {
        $rating = (int) $rating;
        return $rating >= 1 && $rating <= 5;
    }

    /**
     * Validasi komentar (tidak boleh kosong dan minimal 5 karakter)
     */
    public function isValidKomentar(string $komentar): bool
    {
        return strlen(trim($komentar)) >= 5;
    }

    /**
     * Tambah review baru
     * @return array ['success' => bool, 'message' => string]
     */
    public function tambahReview(int $userId, int $wisataId, int $rating, string $komentar): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'User tidak valid'];
        }

        if ($wisataId <= 0) {
            return ['success' => false, 'message' => 'ID wisata tidak valid'];
        }

        if (!$this->isValidRating($rating)) {
            return ['success' => false, 'message' => 'Rating harus antara 1 sampai 5'];
        }

        if (!$this->isValidKomentar($komentar)) {
            return ['success' => false, 'message' => 'Komentar minimal 5 karakter'];
        }

        $escapedKomentar = $this->db->escapeString($komentar);

        $result = $this->db->query(
            "INSERT INTO review VALUES(NULL, $userId, $wisataId, $rating, '$escapedKomentar')"
        );

        if ($result) {
            return ['success' => true, 'message' => 'Review berhasil ditambahkan'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan review'];
    }

    /**
     * Ambil semua review untuk wisata tertentu
     */
    public function getReviewByWisata(int $wisataId): array
    {
        $result = $this->db->query(
            "SELECT * FROM review WHERE wisata_id='$wisataId' ORDER BY id DESC"
        );

        $reviews = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $reviews[] = $row;
        }

        return $reviews;
    }

    /**
     * Hitung rata-rata rating sebuah wisata
     */
    public function hitungRataRating(array $reviews): float
    {
        if (empty($reviews)) {
            return 0.0;
        }

        $total = array_sum(array_column($reviews, 'rating'));
        return round($total / count($reviews), 1);
    }

    /**
     * Format tampilan rating sebagai bintang (★)
     */
    public function formatBintang(float $rating): string
    {
        $filled  = (int) round($rating);
        $empty   = 5 - $filled;
        return str_repeat('★', $filled) . str_repeat('☆', $empty);
    }
}
