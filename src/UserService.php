<?php

require_once __DIR__ . '/Database.php';

/**
 * Service untuk mengelola data pengguna (User)
 * Memisahkan logika bisnis dari presentasi
 */
class UserService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Validasi input email
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validasi panjang password minimal
     */
    public function isValidPassword(string $password, int $minLength = 6): bool
    {
        return strlen($password) >= $minLength;
    }

    /**
     * Validasi nama pengguna (tidak boleh kosong & min 3 karakter)
     */
    public function isValidNama(string $nama): bool
    {
        $nama = trim($nama);
        return strlen($nama) >= 3;
    }

    /**
     * Registrasi pengguna baru
     * @return array ['success' => bool, 'message' => string]
     */
    public function register(string $nama, string $email, string $password): array
    {
        // Validasi input
        if (!$this->isValidNama($nama)) {
            return ['success' => false, 'message' => 'Nama minimal 3 karakter'];
        }

        if (!$this->isValidEmail($email)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        if (!$this->isValidPassword($password)) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }

        // Cek apakah email sudah terdaftar
        $escapedEmail = $this->db->escapeString($email);
        $result = $this->db->query("SELECT * FROM users WHERE email='$escapedEmail'");

        if ($this->db->numRows($result) > 0) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }

        // Hash password dan simpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $escapedNama    = $this->db->escapeString($nama);
        $escapedPwd     = $this->db->escapeString($hashedPassword);

        $insertResult = $this->db->query(
            "INSERT INTO users VALUES(NULL,'$escapedNama','$escapedEmail','$escapedPwd')"
        );

        if ($insertResult) {
            return ['success' => true, 'message' => 'Registrasi berhasil'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan data: ' . $this->db->getLastError()];
    }

    /**
     * Login pengguna
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email dan password harus diisi', 'user' => null];
        }

        $escapedEmail = $this->db->escapeString($email);
        $result       = $this->db->query("SELECT * FROM users WHERE email='$escapedEmail'");
        $user         = $this->db->fetchAssoc($result);

        if ($user && password_verify($password, $user['password'])) {
            return ['success' => true, 'message' => 'Login berhasil', 'user' => $user];
        }

        return ['success' => false, 'message' => 'Email atau password salah', 'user' => null];
    }

    /**
     * Cari pengguna berdasarkan email
     */
    public function findByEmail(string $email): ?array
    {
        $escapedEmail = $this->db->escapeString($email);
        $result = $this->db->query("SELECT * FROM users WHERE email='$escapedEmail'");
        return $this->db->fetchAssoc($result);
    }
}
