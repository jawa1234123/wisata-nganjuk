<?php
include 'config.php';

$id = $_GET['id'] ?? 0;

// ambil data
$query = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

// jika tidak ada data
if (!$data) {
    die("Data tidak ditemukan");
}

// handle gambar
$gambar = $data['gambar'] ?? '';
$path_gambar = "assets/img/" . $gambar;

// fallback gambar
if ($gambar == '' || !file_exists($path_gambar)) {
    $path_gambar = "assets/img/default.jpg";
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?= $data['nama'] ?></title>

<style>
body {
    margin: 0;
    background: #020617;
    color: white;
    font-family: 'Segoe UI', sans-serif;
}

.container {
    width: 85%;
    margin: auto;
    padding: 20px;
}

/* BACK BUTTON */
.btn-back {
    color: #94a3b8;
    text-decoration: none;
    font-size: 14px;
}
.btn-back:hover {
    color: white;
}

/* HERO */
.hero {
    position: relative;
    height: 500px;
    overflow: hidden;
    border-radius: 20px;
    margin-top: 10px;
}

.hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(60%);
}

.overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    padding: 40px;
    width: 100%;
    background: linear-gradient(to top, rgba(2,6,23,1), transparent);
}

.overlay h1 {
    font-size: 42px;
    margin: 0;
}

.overlay p {
    color: #cbd5f5;
}

/* SECTION CARD */
.section-box {
    background: rgba(17,24,39,0.8);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 25px;
    margin-top: 25px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.5);
    position: relative;
}

/* garis kiri */
.section-box::before {
    content: "";
    position: absolute;
    left: 0;
    top: 20px;
    width: 4px;
    height: 40px;
    background: linear-gradient(#22c55e,#06b6d4);
    border-radius: 10px;
}

.section-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

/* DESKRIPSI */
.deskripsi {
    line-height: 1.8;
    color: #d1d5db;
}

/* INFO */
.info-list div {
    margin-bottom: 10px;
}

/* BADGE */
.badge {
    background: #22c55e;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
}

/* MAP */
.map iframe {
    width: 100%;
    height: 320px;
    border-radius: 12px;
    border: none;
}

/* HOVER EFFECT */
.section-box:hover {
    transform: translateY(-3px);
    transition: 0.3s;
}
</style>

</head>

<body>

<div class="container">

    <!-- BACK -->
    <a href="index.php" class="btn-back">← Kembali</a>

    <!-- HERO -->
    <div class="hero">
        <img src="<?= $path_gambar ?>">

        <div class="overlay">
            <h1><?= $data['nama'] ?></h1>
            <p>📍 <?= $data['lokasi'] ?></p>
        </div>
    </div>

    <!-- DESKRIPSI -->
    <div class="section-box">
        <div class="section-title">📖 Deskripsi</div>
        <div class="deskripsi">
            <?= $data['deskripsi'] ?>
        </div>
    </div>

    <!-- INFORMASI -->
    <div class="section-box">
        <div class="section-title">📌 Informasi</div>

        <div class="info-list">
            <div>📍 <b>Lokasi:</b> <?= $data['lokasi'] ?></div>
            <div>🏷️ <b>Kategori:</b> 
                <span class="badge"><?= $data['kategori'] ?></span>
            </div>
        </div>
    </div>

    <!-- MAP -->
    <div class="section-box">
        <div class="section-title">🗺️ Lokasi Map</div>

        <div class="map">
            <iframe 
                src="https://maps.google.com/maps?q=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>&z=15&output=embed">
            </iframe>
        </div>
    </div>

</div>

</body>
</html>