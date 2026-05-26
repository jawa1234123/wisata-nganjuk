<?php

/**
 * Interface untuk koneksi database
 * Digunakan agar bisa di-mock saat testing
 */
interface DatabaseInterface
{
    public function query(string $sql): mixed;
    public function fetchAssoc(mixed $result): ?array;
    public function numRows(mixed $result): int;
    public function escapeString(string $str): string;
    public function getLastError(): string;
}

/**
 * Implementasi nyata DatabaseInterface menggunakan MySQLi
 */
class Database implements DatabaseInterface
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function query(string $sql): mixed
    {
        return mysqli_query($this->conn, $sql);
    }

    public function fetchAssoc(mixed $result): ?array
    {
        $row = mysqli_fetch_assoc($result);
        return $row !== false ? $row : null;
    }

    public function numRows(mixed $result): int
    {
        return mysqli_num_rows($result);
    }

    public function escapeString(string $str): string
    {
        return mysqli_real_escape_string($this->conn, $str);
    }

    public function getLastError(): string
    {
        return mysqli_error($this->conn);
    }
}
