<?php

/**
 * ============================================================
 * STUB: DatabaseStub
 * ============================================================
 * Stub adalah implementasi palsu dari sebuah interface yang
 * mengembalikan data "tetap" (hard-coded) tanpa benar-benar
 * memanggil database asli.
 *
 * Digunakan untuk: mengisolasi kode yang diuji dari database,
 * sehingga test dapat berjalan cepat dan tanpa koneksi nyata.
 */
class DatabaseStub implements DatabaseInterface
{
    /** Data yang akan dikembalikan oleh fetchAssoc() */
    private ?array $stubbedRow;

    /** Jumlah baris yang dikembalikan oleh numRows() */
    private int $stubbedNumRows;

    /** Apakah query() dianggap berhasil */
    private bool $querySuccess;

    public function __construct(
        ?array $stubbedRow    = null,
        int    $stubbedNumRows = 0,
        bool   $querySuccess   = true
    ) {
        $this->stubbedRow     = $stubbedRow;
        $this->stubbedNumRows = $stubbedNumRows;
        $this->querySuccess   = $querySuccess;
    }

    public function query(string $sql): mixed
    {
        // Stub: mengembalikan true/false tanpa eksekusi SQL asli
        return $this->querySuccess ? true : false;
    }

    public function fetchAssoc(mixed $result): ?array
    {
        // Stub: mengembalikan data yang sudah ditentukan
        return $this->stubbedRow;
    }

    public function numRows(mixed $result): int
    {
        // Stub: mengembalikan jumlah baris yang sudah ditentukan
        return $this->stubbedNumRows;
    }

    public function escapeString(string $str): string
    {
        // Stub: hanya mengembalikan string tanpa escape asli
        return $str;
    }

    public function getLastError(): string
    {
        return '';
    }
}


/**
 * ============================================================
 * FAKE: FakeDatabase
 * ============================================================
 * Fake adalah implementasi yang berfungsi penuh namun menggunakan
 * cara yang lebih sederhana (in-memory array sebagai pengganti DB).
 *
 * Digunakan untuk: test integrasi ringan tanpa database nyata,
 * namun tetap dapat menyimpan dan mengambil data.
 */
class FakeDatabase implements DatabaseInterface
{
    /** Penyimpanan data in-memory (sebagai pengganti tabel database) */
    private array $tables = [];

    /** Menyimpan result dari query terakhir */
    private array $lastResult = [];

    /** Pointer untuk iterasi fetchAssoc */
    private int $fetchPointer = 0;

    /**
     * Seed data awal ke "tabel" fake
     */
    public function seedTable(string $tableName, array $rows): void
    {
        $this->tables[$tableName] = $rows;
    }

    /**
     * Ambil semua data dari tabel fake
     */
    public function getTable(string $tableName): array
    {
        return $this->tables[$tableName] ?? [];
    }

    public function query(string $sql): mixed
    {
        // Parse query sederhana untuk simulasi INSERT & SELECT
        $sql = trim($sql);

        if (stripos($sql, 'INSERT INTO users') !== false) {
            preg_match("/VALUES\(NULL,'(.+?)','(.+?)','(.+?)'\)/", $sql, $m);
            if ($m) {
                $this->tables['users'][] = [
                    'id'       => count($this->tables['users'] ?? []) + 1,
                    'nama'     => $m[1],
                    'email'    => $m[2],
                    'password' => $m[3],
                ];
            }
            $this->lastResult  = [];
            $this->fetchPointer = 0;
            return true;
        }

        if (stripos($sql, 'SELECT * FROM users WHERE email=') !== false) {
            preg_match("/email='(.+?)'/", $sql, $m);
            $email = $m[1] ?? '';

            $this->lastResult = array_values(
                array_filter($this->tables['users'] ?? [], fn($u) => $u['email'] === $email)
            );
            $this->fetchPointer = 0;
            return true;
        }

        $this->lastResult  = [];
        $this->fetchPointer = 0;
        return true;
    }

    public function fetchAssoc(mixed $result): ?array
    {
        if ($this->fetchPointer < count($this->lastResult)) {
            return $this->lastResult[$this->fetchPointer++];
        }
        return null;
    }

    public function numRows(mixed $result): int
    {
        return count($this->lastResult);
    }

    public function escapeString(string $str): string
    {
        return addslashes($str);
    }

    public function getLastError(): string
    {
        return '';
    }
}


/**
 * ============================================================
 * SPY: DatabaseSpy
 * ============================================================
 * Spy adalah test double yang merekam semua interaksi yang terjadi
 * padanya. Digunakan untuk memverifikasi bahwa metode tertentu
 * dipanggil dengan argumen yang benar.
 *
 * Spy bisa juga meneruskan panggilan ke implementasi nyata (partial spy),
 * tapi di sini kita buat spy murni untuk logging.
 */
class DatabaseSpy implements DatabaseInterface
{
    /** Log semua query yang dieksekusi */
    private array $queryLog = [];

    /** Log panggilan escapeString */
    private array $escapeLog = [];

    /** Banyaknya panggilan ke fetchAssoc() */
    private int $fetchCallCount = 0;

    /** Data yang dikembalikan (delegasi ke stub) */
    private DatabaseStub $delegate;

    public function __construct(DatabaseStub $delegate)
    {
        $this->delegate = $delegate;
    }

    public function query(string $sql): mixed
    {
        // Spy: catat query sebelum delegasikan ke stub
        $this->queryLog[] = $sql;
        return $this->delegate->query($sql);
    }

    public function fetchAssoc(mixed $result): ?array
    {
        // Spy: hitung berapa kali fetchAssoc dipanggil
        $this->fetchCallCount++;
        return $this->delegate->fetchAssoc($result);
    }

    public function numRows(mixed $result): int
    {
        return $this->delegate->numRows($result);
    }

    public function escapeString(string $str): string
    {
        // Spy: catat semua string yang di-escape
        $this->escapeLog[] = $str;
        return $this->delegate->escapeString($str);
    }

    public function getLastError(): string
    {
        return $this->delegate->getLastError();
    }

    // ---- Metode untuk verifikasi dalam test ----

    /** Kembalikan semua query yang sudah dieksekusi */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /** Cek apakah query tertentu pernah dieksekusi */
    public function wasQueryExecuted(string $partialSql): bool
    {
        foreach ($this->queryLog as $q) {
            if (stripos($q, $partialSql) !== false) {
                return true;
            }
        }
        return false;
    }

    /** Kembalikan berapa kali fetchAssoc dipanggil */
    public function getFetchCallCount(): int
    {
        return $this->fetchCallCount;
    }

    /** Kembalikan semua string yang pernah di-escape */
    public function getEscapeLog(): array
    {
        return $this->escapeLog;
    }

    /** Total query yang sudah dieksekusi */
    public function getQueryCount(): int
    {
        return count($this->queryLog);
    }
}


/**
 * ============================================================
 * DUMMY: DummyDatabase
 * ============================================================
 * Dummy adalah objek yang diteruskan sebagai parameter tapi
 * tidak pernah benar-benar digunakan dalam test.
 * Sering digunakan untuk memenuhi signature konstruktor atau fungsi.
 */
class DummyDatabase implements DatabaseInterface
{
    public function query(string $sql): mixed          { return null; }
    public function fetchAssoc(mixed $result): ?array  { return null; }
    public function numRows(mixed $result): int        { return 0; }
    public function escapeString(string $str): string  { return $str; }
    public function getLastError(): string             { return ''; }
}
