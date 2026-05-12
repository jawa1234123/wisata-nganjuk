<?php

include 'config.php';


// ==============================
// MODEL
// ==============================
class Kuliner {

    private $nama;
    private $lokasi;
    private $deskripsi;
    private $jamBuka;
    private $gambar;

    public function __construct($data) {

        $this->nama = $data['nama_kuliner'] ?? '-';
        $this->lokasi = $data['lokasi'] ?? '-';
        $this->deskripsi = $data['deskripsi'] ?? '-';
        $this->jamBuka = $data['jam_buka'] ?? '-';
        $this->gambar = $data['gambar'] ?? 'default.jpg';
    }

    public function getNama() {
        return $this->nama;
    }

    public function getLokasi() {
        return $this->lokasi;
    }

    public function getDeskripsi() {
        return $this->deskripsi;
    }

    public function getJamBuka() {
        return $this->jamBuka;
    }

    public function getGambar() {
        return $this->gambar;
    }
}


// ==============================
// REPOSITORY
// ==============================
class KulinerRepository {

    private $conn;

    public function __construct($conn) {

        $this->conn = $conn;
    }

    public function getById($id) {

        $id = (int)$id;

        $query = mysqli_query(
            $this->conn,
            "SELECT * FROM kuliner WHERE id=$id"
        );

        $data = mysqli_fetch_assoc($query);

        if(!$data){
            return null;
        }

        return new Kuliner($data);
    }
}


// ==============================
// SERVICE
// ==============================
class KulinerService {

    private $repository;

    public function __construct($repository) {

        $this->repository = $repository;
    }

    public function findKuliner($id) {

        if(!$id){

            die("
                <h2 style='color:white;text-align:center;margin-top:50px'>
                    ID tidak ditemukan
                </h2>
            ");
        }

        $kuliner = $this->repository->getById($id);

        if(!$kuliner){

            die("
                <h2 style='color:white;text-align:center;margin-top:50px'>
                    Data tidak ditemukan
                </h2>
            ");
        }

        return $kuliner;
    }
}


// ==============================
// VIEW
// ==============================
class DetailKulinerView {

    public function render($kuliner) {

?>

<!DOCTYPE html>
<html>
<head>

<title><?= $kuliner->getNama() ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    background:#020617;
    color:white;
    font-family:'Segoe UI', sans-serif;
}

.hero{
    height:75vh;
    position:relative;
    overflow:hidden;
}

.hero img{
    width:100%;
    height:100%;
    object-fit:cover;
    filter:brightness(60%);
}

.overlay{
    position:absolute;
    width:100%;
    height:100%;
    background:linear-gradient(to top, rgba(2,6,23,1), transparent);
}

.hero-text{
    position:absolute;
    bottom:60px;
    left:60px;
}

.hero-text h1{
    font-size:48px;
    font-weight:bold;
}

.container{
    margin-top:-80px;
}

.card-info{
    background: rgba(17,24,39,0.85);
    backdrop-filter: blur(12px);
    border-radius:20px;
    padding:30px;
}

.info-box{
    background:rgba(255,255,255,0.03);
    padding:15px;
    border-radius:12px;
    margin-bottom:15px;
}

.back-btn{
    margin-top:20px;
    display:inline-block;
    padding:10px 20px;
    border-radius:10px;
    background:linear-gradient(135deg,#2563eb,#06b6d4);
    color:white;
    text-decoration:none;
}

</style>

</head>
<body>

<div class="hero">

    <img
        src="assets/img/<?= $kuliner->getGambar() ?>"
        onerror="this.src='assets/img/default.jpg'"
    >

    <div class="overlay"></div>

    <div class="hero-text">

        <h1><?= $kuliner->getNama() ?></h1>

        <p><?= $kuliner->getLokasi() ?></p>

    </div>

</div>


<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card-info">

                <h4>📌 Informasi Kuliner</h4>
                <hr>

                <div class="info-box">

                    <strong>📍 Lokasi</strong>

                    <p>
                        <?= $kuliner->getLokasi() ?>
                    </p>

                </div>

                <div class="info-box">

                    <strong>⏰ Jam Operasional</strong>

                    <p>
                        <?= $kuliner->getJamBuka() ?>
                    </p>

                </div>

                <div class="info-box">

                    <strong>🍽️ Deskripsi</strong>

                    <p>
                        <?= $kuliner->getDeskripsi() ?>
                    </p>

                </div>

                <a href="index.php" class="back-btn">
                    ← Kembali
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>

<?php
    }
}


// ==============================
// CONTROLLER
// ==============================

$repository = new KulinerRepository($conn);

$service = new KulinerService($repository);

$kuliner = $service->findKuliner(
    $_GET['id'] ?? null
);

$view = new DetailKulinerView();

$view->render($kuliner);

?>
