<?php
include 'config.php';

// VALIDASI ID
if(!isset($_GET['id']) || $_GET['id'] == ''){
    echo "<h2 style='color:white;text-align:center;margin-top:50px'>ID tidak ditemukan</h2>";
    exit;
}

$id = (int)$_GET['id'];

// AMBIL DATA
$query = mysqli_query($conn,"SELECT * FROM event WHERE id=$id");
$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "<h2 style='color:white;text-align:center;margin-top:50px'>Data tidak ditemukan</h2>";
    exit;
}

// SAFE FUNCTION
function safe($data, $key, $default='-'){
    return isset($data[$key]) && $data[$key] != '' ? $data[$key] : $default;
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?= safe($data,'judul') ?></title>

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
}

.hero img{
    width:100%;
    height:100%;
    object-fit:cover;
    filter:brightness(55%);
}

.overlay{
    position:absolute;
    width:100%;
    height:100%;
    background:linear-gradient(to top, rgba(2,6,23,1), transparent);
}

/* TEXT HERO */
.hero-text{
    position:absolute;
    bottom:60px;
    left:60px;
}

.hero-text h1{
    font-size:46px;
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
.card-event{
    background:rgba(17,24,39,0.85);
    backdrop-filter:blur(12px);
    border-radius:20px;
    padding:30px;
    box-shadow:0 20px 60px rgba(0,0,0,0.6);
}

/* GRID */
.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-bottom:20px;
}

/* BOX */
.info-box{
    background:rgba(255,255,255,0.03);
    padding:15px;
    border-radius:12px;
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
    line-height:1.7;
    opacity:0.9;
}

/* BADGE */
.badge-event{
    background:#f59e0b;
    padding:5px 10px;
    border-radius:8px;
    font-size:12px;
}

/* BUTTON */
.back-btn{
    margin-top:20px;
    display:inline-block;
    padding:10px 20px;
    border-radius:10px;
    background:linear-gradient(135deg,#f59e0b,#ef4444);
    color:white;
    text-decoration:none;
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
        <span class="badge-event">Event</span>
        <h1><?= safe($data,'judul') ?></h1>
        <p><?= safe($data,'lokasi') ?></p>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card-event">

                <h4>📅 Detail Event</h4>
                <hr>

                <!-- GRID -->
                <div class="info-grid">

                    <div class="info-box">
                        <div class="label">📍 Lokasi</div>
                        <div class="value"><?= safe($data,'lokasi') ?></div>
                    </div>

                    <div class="info-box">
                        <div class="label">📆 Tanggal</div>
                        <div class="value"><?= safe($data,'tanggal') ?></div>
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="info-box">
                    <div class="label">📝 Deskripsi</div>
                    <div class="deskripsi">
                        <?= safe($data,'deskripsi','Belum ada deskripsi') ?>
                    </div>
                </div>

                <a href="index.php" class="back-btn">← Kembali</a>

            </div>

        </div>
    </div>
</div>

</body>
</html>