<?php

require_once __DIR__ . '/Database.php';

/**
 * Service untuk mengelola data Wisata
 */
class WisataService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Validasi data wisata sebelum disimpan
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateWisata(array $data): array
    {
        $errors = [];

        if (empty(trim($data['nama'] ?? ''))) {
            $errors[] = 'Nama wisata tidak boleh kosong';
        }

        if (empty(trim($data['deskripsi'] ?? ''))) {
            $errors[] = 'Deskripsi tidak boleh kosong';
        }

        if (empty(trim($data['lokasi'] ?? ''))) {
            $errors[] = 'Lokasi tidak boleh kosong';
        }

        $validKategori = ['alam', 'buatan', 'religi'];
        if (!in_array($data['kategori'] ?? '', $validKategori)) {
            $errors[] = 'Kategori tidak valid. Pilih: alam, buatan, atau religi';
        }

        if (!empty($data['latitude']) && !$this->isValidKoordinat($data['latitude'])) {
            $errors[] = 'Format latitude tidak valid';
        }

        if (!empty($data['longitude']) && !$this->isValidKoordinat($data['longitude'])) {
            $errors[] = 'Format longitude tidak valid';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validasi format koordinat geografis
     */
    public function isValidKoordinat(string $nilai): bool
    {
        return is_numeric($nilai);
    }

    /**
     * Tambah wisata baru
     * @return array ['success' => bool, 'message' => string]
     */
    public function tambahWisata(array $data): array
    {
        $validasi = $this->validateWisata($data);
        if (!$validasi['valid']) {
            return [
                'success' => false,
                'message' => implode(', ', $validasi['errors']),
            ];
        }

        $nama      = $this->db->escapeString($data['nama']);
        $deskripsi = $this->db->escapeString($data['deskripsi']);
        $lokasi    = $this->db->escapeString($data['lokasi']);
        $kategori  = $this->db->escapeString($data['kategori']);
        $gambar    = $this->db->escapeString($data['gambar'] ?? '');
        $latitude  = $this->db->escapeString($data['latitude'] ?? '');
        $longitude = $this->db->escapeString($data['longitude'] ?? '');

        $result = $this->db->query(
            "INSERT INTO wisata (nama, deskripsi, lokasi, kategori, gambar, latitude, longitude)
             VALUES ('$nama','$deskripsi','$lokasi','$kategori','$gambar','$latitude','$longitude')"
        );

        if ($result) {
            return ['success' => true, 'message' => 'Wisata berhasil ditambahkan'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan: ' . $this->db->getLastError()];
    }

    /**
     * Ambil semua data wisata, dengan filter kategori opsional
     * @return array
     */
    public function getAllWisata(string $kategori = ''): array
    {
        $where = "WHERE 1";
        if (!empty($kategori)) {
            $escapedKategori = $this->db->escapeString($kategori);
            $where .= " AND kategori='$escapedKategori'";
        }

        $result = $this->db->query("SELECT * FROM wisata $where");

        $data = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Ambil data wisata berdasarkan ID
     */
    public function getWisataById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $result = $this->db->query("SELECT * FROM wisata WHERE id='$id'");
        return $this->db->fetchAssoc($result);
    }

    /**
     * Update data wisata
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateWisata(int $id, array $data): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID wisata tidak valid'];
        }

        $validasi = $this->validateWisata($data);
        if (!$validasi['valid']) {
            return [
                'success' => false,
                'message' => implode(', ', $validasi['errors']),
            ];
        }

        $nama      = $this->db->escapeString($data['nama']);
        $deskripsi = $this->db->escapeString($data['deskripsi']);
        $lokasi    = $this->db->escapeString($data['lokasi']);
        $kategori  = $this->db->escapeString($data['kategori']);
        $gambar    = $this->db->escapeString($data['gambar'] ?? '');
        $latitude  = $this->db->escapeString($data['latitude'] ?? '');
        $longitude = $this->db->escapeString($data['longitude'] ?? '');

        $result = $this->db->query(
            "UPDATE wisata SET
                nama='$nama',
                deskripsi='$deskripsi',
                lokasi='$lokasi',
                kategori='$kategori',
                gambar='$gambar',
                latitude='$latitude',
                longitude='$longitude'
             WHERE id='$id'"
        );

        if ($result) {
            return ['success' => true, 'message' => 'Wisata berhasil diupdate'];
        }

        return ['success' => false, 'message' => 'Gagal update: ' . $this->db->getLastError()];
    }

    /**
     * Hapus wisata berdasarkan ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function hapusWisata(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID wisata tidak valid'];
        }

        $result = $this->db->query("DELETE FROM wisata WHERE id='$id'");

        if ($result) {
            return ['success' => true, 'message' => 'Wisata berhasil dihapus'];
        }

        return ['success' => false, 'message' => 'Gagal menghapus: ' . $this->db->getLastError()];
    }

    /**
     * Filter wisata yang memiliki koordinat (latitude & longitude)
     */
    public function filterHasKoordinat(array $wisataList): array
    {
        return array_filter($wisataList, function ($item) {
            return !empty($item['latitude']) && !empty($item['longitude']);
        });
    }

    /**
     * Format nama file gambar aman untuk penyimpanan
     */
    public function formatNamaGambar(string $originalName): string
    {
        $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $base = preg_replace('/[^a-z0-9_\-]/', '_', strtolower(pathinfo($originalName, PATHINFO_FILENAME)));
        return time() . '_' . $base . '.' . $ext;
    }
}
