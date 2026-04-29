<?php
include '../config.php';

if(isset($_POST['submit'])){
      global $conn;

    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $lokasi = $_POST['lokasi'];
    $kategori = $_POST['kategori'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];

    move_uploaded_file($tmp, "../assets/img/".$gambar);

    mysqli_query($conn, "INSERT INTO wisata 
    (nama, deskripsi, lokasi, kategori, gambar, latitude, longitude)
    VALUES 
    ('$nama','$deskripsi','$lokasi','$kategori','$gambar','$latitude','$longitude')");

    header("Location: wisata.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Wisata</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#0f172a;
    color:white;
}
.card{
    background:#1e293b;
    border:none;
}
</style>
</head>

<body>

<div class="container mt-5">
<div class="card p-4">

<h3>Tambah Wisata</h3>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="mb-3">
<label>Deskripsi</label>
<textarea name="deskripsi" class="form-control" required></textarea>
</div>

<div class="mb-3">
<label>Lokasi</label>
<input type="text" name="lokasi" class="form-control" required>
</div>

<div class="mb-3">
<label>Kategori</label>
<select name="kategori" class="form-control">
    <option value="alam">Alam</option>
    <option value="buatan">Buatan</option>
    <option value="religi">Religi</option>
</select>
</div>

<div class="mb-3">
<label>Gambar</label>
<input type="file" name="gambar" class="form-control" required>
</div>

<div class="mb-3">
<label>Latitude</label>
<input type="text" name="latitude" class="form-control" placeholder="-7.12345">
</div>

<div class="mb-3">
<label>Longitude</label>
<input type="text" name="longitude" class="form-control" placeholder="111.12345">
</div>

<button type="submit" name="submit" class="btn btn-success">Simpan</button>
<a href="wisata.php" class="btn btn-secondary">Kembali</a>

</form>

</div>
</div>

</body>
</html>