<?php
include 'config.php';

// VALIDASI ID
if(!isset($_GET['id']) || $_GET['id'] == ''){
    echo "<h2 style='color:white;text-align:center;margin-top:50px'>ID tidak ditemukan</h2>";
    exit;
}

$id = (int)$_GET['id'];

// AMBIL DATA
$query = mysqli_query($conn,"SELECT * FROM kuliner WHERE id=$id");
$data = mysqli_fetch_assoc($query);

// CEK DATA
if(!$data){
    echo "<h2 style='color:white;text-align:center;margin-top:50px'>Data tidak ditemukan</h2>";
    exit;
}

// FUNCTION SAFE
function safe($data, $key, $default='-'){
    return isset($data[$key]) && $data[$key] != '' ? $data[$key] : $default;
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?= safe($data,'nama_kuliner') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    margin:0;
    background:#020617;
    color:white;
    font-family:'Segoe UI', sans-serif;
}

/* HERO */
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

.hero-text p{
    opacity:0.8;
}

/* CONTENT */
.container{
    margin-top:-80px;
}

/* CARD */
.card-info{
    background: rgba(17,24,39,0.85);
    backdrop-filter: blur(12px);
    border-radius:20px;
    padding:30px;
    box-shadow:0 20px 60px rgba(0,0,0,0.6);
}

/* GRID */
.info-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:20px;
    margin-bottom:20px;
}

/* BOX */
.info-box{
    background:rgba(255,255,255,0.03);
    padding:15px;
    border-radius:12px;
    transition:0.3s;
}

.info-box:hover{
    background:rgba(255,255,255,0.06);
}

.label{
    font-size:13px;
    opacity:0.7;
}

.value{
    font-size:15px;
    font-weight:500;
}

/* DESKRIPSI */
.deskripsi{
    margin-top:10px;
    line-height:1.7;
    opacity:0.9;
}

/* BUTTON */
.back-btn{
    margin-top:20px;
    display:inline-block;
    padding:10px 20px;
    border-radius:10px;
    background:linear-gradient(135deg,#2563eb,#06b6d4);
    color:white;
    text-decoration:none;
    transition:0.3s;
}

.back-btn:hover{
    opacity:0.8;
}

/* BADGE */
.badge-custom{
    background:#10b981;
    padding:5px 10px;
    border-radius:8px;
    font-size:12px;
}
</style>

</head>
<body>

<!-- HERO -->
<div class="hero">
    <img src="assets/img/<?= safe($data,'gambar','default.jpg') ?>"
         onerror="this.src='assets/img/default.jpg'">

    <div class="overlay"></div>

    <div class="hero-text">
        <span class="badge-custom">Kuliner</span>
        <h1><?= safe($data,'nama_kuliner') ?></h1>
        <p><?= safe($data,'lokasi') ?></p>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card-info">

                <h4>📌 Informasi Kuliner</h4>
                <hr>

                <!-- GRID -->
                <div class="info-grid">

                    <div class="info-box">
                        <div class="label">📍 Lokasi</div>
                        <div class="value"><?= safe($data,'lokasi') ?></div>
                    </div>

                    <div class="info-box">
                        <div class="label">⏰ Jam Operasional</div>
                        <div class="value"><?= safe($data,'jam_buka','Belum tersedia') ?></div>
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="info-box">
                    <div class="label">🍽️ Deskripsi</div>
                    <div class="deskripsi">
                        <?= safe($data,'deskripsi','Kuliner khas Nganjuk yang wajib dicoba.') ?>
                    </div>
                </div>

                <a href="index.php" class="back-btn">← Kembali</a>

            </div>

        </div>
    </div>
</div>

</body>
</html>