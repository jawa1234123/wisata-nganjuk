<?php
include '../config.php';
include 'auth.php';
include 'layout.php';

$id = $_GET['id'];
  global $conn;


// ambil data lama
$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kuliner WHERE id=$id"));

if(isset($_POST['submit'])){

    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $lokasi    = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $jam_buka  = mysqli_real_escape_string($conn, $_POST['jam_buka']);

    // upload gambar
    if($_FILES['gambar']['name']){
        $file = $_FILES['gambar']['name'];
        $tmp  = $_FILES['gambar']['tmp_name'];

        $nama_file = time() . '_' . $file;

        move_uploaded_file($tmp,"../assets/img/".$nama_file);

        $gambar = $nama_file;
    }else{
        $gambar = $data['gambar'];
    }

    mysqli_query($conn,"UPDATE kuliner SET 
        nama_kuliner='$nama',
        lokasi='$lokasi',
        deskripsi='$deskripsi',
        jam_buka='$jam_buka',
        gambar='$gambar'
        WHERE id=$id");

    header("Location: kuliner.php");
    exit;
}
?>

<h2>Edit Kuliner</h2>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="nama" 
        value="<?= $data['nama_kuliner'] ?>" 
        class="form-control mb-2">

    <input type="text" name="lokasi" 
        value="<?= $data['lokasi'] ?>" 
        class="form-control mb-2">

    <!-- DESKRIPSI -->
    <textarea name="deskripsi" class="form-control mb-2"><?= $data['deskripsi'] ?></textarea>

    <!-- JAM BUKA -->
    <input type="text" name="jam_buka" 
        value="<?= $data['jam_buka'] ?>" 
        class="form-control mb-2">

    <!-- GAMBAR LAMA -->
    <img src="../assets/img/<?= $data['gambar'] ?>" width="120" style="border-radius:10px"><br><br>

    <!-- GANTI GAMBAR -->
    <input type="file" name="gambar" class="form-control mb-2">

    <button name="submit" class="btn btn-warning">Update</button>
</form>