<?php

// Interface (Abstraksi)
interface ExploreRepository {
    public function getLatestData($limit);
}

class WisataRepository implements ExploreRepository {
    private $db;
    public function __construct($conn) { $this->db = $conn; }
    
    public function getLatestData($limit) {
        $result = mysqli_query($this->db, "SELECT * FROM wisata ORDER BY id DESC LIMIT $limit");
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['id'],
                'judul' => $row['nama'],           // Diseragamkan menjadi 'judul'
                'deskripsi' => $row['lokasi'],     // Diseragamkan menjadi 'deskripsi'
                'gambar' => $row['gambar']
            ];
        }
        return $data;
    }
}

class KulinerRepository implements ExploreRepository {
    private $db;
    public function __construct($conn) { $this->db = $conn; }

    public function getLatestData($limit) {
        $result = mysqli_query($this->db, "SELECT * FROM kuliner ORDER BY id DESC LIMIT $limit");
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['id'],
                'judul' => $row['nama_kuliner'],   // Diseragamkan menjadi 'judul'
                'deskripsi' => $row['deskripsi'],
                'gambar' => $row['gambar']
            ];
        }
        return $data;
    }
}

class EventRepository implements ExploreRepository {
    private $db;
    public function __construct($conn) { $this->db = $conn; }

    public function getLatestData($limit) {
        $result = mysqli_query($this->db, "SELECT * FROM event ORDER BY id DESC LIMIT $limit");
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['id'],
                'judul' => $row['judul'],          
                'deskripsi' => $row['deskripsi'],
                'gambar' => $row['gambar']
            ];
        }
        return $data;
    }
}
?>
