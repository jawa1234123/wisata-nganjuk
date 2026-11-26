<?php

require_once __DIR__ . '/Database.php';

/**
 * Service untuk mengelola transaksi pemesanan (Wisata & Kuliner),
 * riwayat transaksi, serta simulasi pembayaran
 */
class BookingService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Validasi kuota tiket wisata (1 s.d. 50 orang)
     */
    public function isValidWisataJumlahOrang(int $jumlah): bool
    {
        return $jumlah >= 1 && $jumlah <= 50;
    }

    /**
     * Validasi kuota tamu meja kuliner (1 s.d. 20 orang)
     */
    public function isValidKulinerJumlahOrang(int $jumlah): bool
    {
        return $jumlah >= 1 && $jumlah <= 20;
    }

    /**
     * Validasi batas tanggal booking (tidak boleh tanggal lampau dan maksimal 1 tahun ke depan)
     */
    public function isValidTanggalBooking(string $tanggal, string $today = ''): bool
    {
        if (empty($tanggal)) {
            return false;
        }

        $inputTime = strtotime($tanggal);
        if ($inputTime === false) {
            return false;
        }

        $year = (int)date('Y', $inputTime);
        if ($year < 2000 || $year > 2100) {
            return false;
        }

        $todayTime = !empty($today) ? strtotime($today) : strtotime(date('Y-m-d'));
        if ($inputTime < $todayTime) {
            return false; // Tanggal lampau tidak diizinkan
        }

        $maxTime = strtotime('+365 days', $todayTime);
        if ($inputTime > $maxTime) {
            return false; // Melebihi 365 hari
        }

        return true;
    }

    /**
     * REQ-004: Pemesanan Tiket Wisata Berbayar
     */
    public function bookWisata(int $userId, int $wisataId, string $tanggal, int $jumlahOrang, int $hargaTiket): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'User wajib login'];
        }

        if ($wisataId <= 0) {
            return ['success' => false, 'message' => 'Destinasi wisata tidak valid'];
        }

        if (!$this->isValidTanggalBooking($tanggal)) {
            return ['success' => false, 'message' => 'Tanggal booking tidak valid atau tanggal lampau'];
        }

        if (!$this->isValidWisataJumlahOrang($jumlahOrang)) {
            return ['success' => false, 'message' => 'Jumlah tiket harus antara 1 sampai 50 orang'];
        }

        if ($hargaTiket < 0) {
            return ['success' => false, 'message' => 'Tarif tiket tidak valid'];
        }

        $totalHarga = $jumlahOrang * $hargaTiket;
        $statusPembayaran = 'pending';
        $tanggalEscaped = $this->db->escapeString($tanggal);

        $sql = "INSERT INTO pemesanan (user_id, tipe, item_id, tanggal_booking, jumlah_orang, total_harga, status_pembayaran) "
             . "VALUES ($userId, 'wisata', $wisataId, '$tanggalEscaped', $jumlahOrang, $totalHarga, '$statusPembayaran')";

        $result = $this->db->query($sql);
        if ($result) {
            return [
                'success' => true,
                'message' => 'Pemesanan tiket wisata berhasil dibuat, silakan lakukan pembayaran',
                'total_harga' => $totalHarga,
                'status' => $statusPembayaran
            ];
        }

        return ['success' => false, 'message' => 'Gagal memproses pemesanan tiket wisata: ' . $this->db->getLastError()];
    }

    /**
     * REQ-005: Reservasi Meja Kuliner Non-Bayar (Gratis)
     */
    public function reserveKuliner(int $userId, int $kulinerId, string $tanggal, int $jumlahOrang): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'User wajib login'];
        }

        if ($kulinerId <= 0) {
            return ['success' => false, 'message' => 'Sentra kuliner tidak valid'];
        }

        if (!$this->isValidTanggalBooking($tanggal)) {
            return ['success' => false, 'message' => 'Tanggal reservasi tidak valid atau tanggal lampau'];
        }

        if (!$this->isValidKulinerJumlahOrang($jumlahOrang)) {
            return ['success' => false, 'message' => 'Jumlah reservasi meja harus antara 1 sampai 20 orang'];
        }

        $totalHarga = 0;
        $statusPembayaran = 'terkonfirmasi'; // Otomatis terkonfirmasi tanpa pembayaran
        $tanggalEscaped = $this->db->escapeString($tanggal);

        $sql = "INSERT INTO pemesanan (user_id, tipe, item_id, tanggal_booking, jumlah_orang, total_harga, status_pembayaran) "
             . "VALUES ($userId, 'kuliner', $kulinerId, '$tanggalEscaped', $jumlahOrang, $totalHarga, '$statusPembayaran')";

        $result = $this->db->query($sql);
        if ($result) {
            return [
                'success' => true,
                'message' => 'Reservasi meja kuliner berhasil dikonfirmasi (Gratis Reservasi)',
                'total_harga' => 0,
                'status' => $statusPembayaran
            ];
        }

        return ['success' => false, 'message' => 'Gagal memproses reservasi kuliner: ' . $this->db->getLastError()];
    }

    /**
     * REQ-006: Riwayat Pemesanan & Isolasi Akses Data (Mencegah IDOR)
     */
    public function getRiwayatUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $sql = "SELECT p.*, COALESCE(w.nama, k.nama_kuliner) as nama_item "
             . "FROM pemesanan p "
             . "LEFT JOIN wisata w ON p.tipe='wisata' AND p.item_id = w.id "
             . "LEFT JOIN kuliner k ON p.tipe='kuliner' AND p.item_id = k.id "
             . "WHERE p.user_id = $userId "
             . "ORDER BY p.id DESC";

        $result = $this->db->query($sql);
        $list = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $list[] = $row;
        }

        return $list;
    }

    /**
     * REQ-006: Verifikasi kepemilikan pesanan (Isolasi data spesifik pesanan)
     */
    public function getDetailPemesanan(int $orderId, int $currentUserId): ?array
    {
        if ($orderId <= 0 || $currentUserId <= 0) {
            return null;
        }

        $sql = "SELECT * FROM pemesanan WHERE id = $orderId AND user_id = $currentUserId";
        $result = $this->db->query($sql);

        return $this->db->fetchAssoc($result);
    }

    /**
     * REQ-007: Simulasi Pembayaran Tagihan Tiket
     */
    public function bayarPesanan(int $orderId, int $userId, int $nominalBayar): array
    {
        if ($orderId <= 0 || $userId <= 0) {
            return ['success' => false, 'message' => 'Data pesanan tidak valid'];
        }

        // Ambil data tagihan
        $order = $this->getDetailPemesanan($orderId, $userId);
        if (!$order) {
            return ['success' => false, 'message' => 'Data pemesanan tidak ditemukan atau akses ditolak'];
        }

        if ($order['status_pembayaran'] === 'dibayar') {
            return ['success' => false, 'message' => 'Pesanan ini sudah dibayar sebelumnya'];
        }

        $tagihan = (int)$order['total_harga'];
        if ($nominalBayar < $tagihan) {
            return [
                'success' => false,
                'message' => 'Nominal pembayaran kurang dari total tagihan'
            ];
        }

        $sqlUpdate = "UPDATE pemesanan SET status_pembayaran='dibayar' WHERE id=$orderId AND user_id=$userId";
        $updated = $this->db->query($sqlUpdate);

        if ($updated) {
            return [
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi. Tiket elektronik aktif.',
                'kembalian' => $nominalBayar - $tagihan,
                'status_pembayaran' => 'dibayar'
            ];
        }

        return ['success' => false, 'message' => 'Gagal memproses transaksi pembayaran'];
    }
}
