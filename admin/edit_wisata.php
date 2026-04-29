<?php
include '../config.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'"));

if(isset($_POST['submit'])){

    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $lokasi = $_POST['lokasi'];
    $kategori = $_POST['kategori'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];

    // kalau upload gambar baru
    if($gambar != ""){
        $gambar_baru = time()."_".$gambar;
        move_uploaded_file($tmp, "../assets/img/".$gambar_baru);
    }else{
        $gambar_baru = $data['gambar'];
    }

    mysqli_query($conn, "UPDATE wisata SET 
        nama='$nama',
        deskripsi='$deskripsi',
        lokasi='$lokasi',
        kategori='$kategori',
        gambar='$gambar_baru',
        latitude='$latitude',
        longitude='$longitude'
        WHERE id='$id'
    ");

    header("Location: wisata.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Wisata</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<div class="container mt-5">
<div class="card p-4 bg-secondary">

<h3>Edit Wisata</h3>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nama" value="<?= $data['nama'] ?>" class="form-control mb-2">
<textarea name="deskripsi" class="form-control mb-2"><?= $data['deskripsi'] ?></textarea>
<input type="text" name="lokasi" value="<?= $data['lokasi'] ?>" class="form-control mb-2">

<select name="kategori" class="form-control mb-2">
    <option <?= ($data['kategori']=='alam')?'selected':'' ?>>alam</option>
    <option <?= ($data['kategori']=='buatan')?'selected':'' ?>>buatan</option>
    <option <?= ($data['kategori']=='religi')?'selected':'' ?>>religi</option>
</select>

<img src="../assets/img/<?= $data['gambar'] ?>" width="100"><br><br>

<input type="file" name="gambar" class="form-control mb-2">

<input type="text" name="latitude" value="<?= $data['latitude'] ?>" class="form-control mb-2">
<input type="text" name="longitude" value="<?= $data['longitude'] ?>" class="form-control mb-2">

<button class="btn btn-warning" name="submit">Update</button>

</form>

</div>
</div>

</body>
</html>