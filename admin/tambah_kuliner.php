<?php
include '../config.php';
include 'auth.php';
include 'layout.php';

if(isset($_POST['submit'])){

    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $lokasi    = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $jam_buka  = mysqli_real_escape_string($conn, $_POST['jam_buka']);

    // file upload
    $file = $_FILES['gambar']['name'];
    $tmp  = $_FILES['gambar']['tmp_name'];

    if($file != ""){

        $nama_file = time() . '_' . $file;

        if(move_uploaded_file($tmp, "../assets/img/" . $nama_file)){

            mysqli_query($conn, "INSERT INTO kuliner 
                (nama_kuliner, lokasi, deskripsi, jam_buka, gambar) 
                VALUES ('$nama','$lokasi','$deskripsi','$jam_buka','$nama_file')");

            header("Location: kuliner.php");
            exit;

        } else {
            echo "<script>alert('Upload gambar gagal');</script>";
        }

    } else {
        echo "<script>alert('Pilih gambar dulu');</script>";
    }
}
?>

<h2>Tambah Kuliner</h2>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="nama" placeholder="Nama Kuliner" class="form-control mb-2" required>

    <input type="text" name="lokasi" placeholder="Lokasi" class="form-control mb-2" required>

    <!-- DESKRIPSI -->
    <textarea name="deskripsi" placeholder="Deskripsi kuliner" class="form-control mb-2" required></textarea>

    <!-- JAM BUKA -->
    <input type="text" name="jam_buka" placeholder="Jam buka (contoh: 08.00 - 21.00)" class="form-control mb-2" required>

    <input type="file" name="gambar" class="form-control mb-2" required>

    <button name="submit" class="btn btn-success">Simpan</button>
</form>