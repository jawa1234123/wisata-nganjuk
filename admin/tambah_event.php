<?php
include '../config.php';
include 'auth.php';
include 'layout.php';

if(isset($_POST['submit'])){
       global $conn;

    $judul     = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal   = $_POST['tanggal'];
    $lokasi    = $_POST['lokasi'];

    $file   = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];

    if($file != ""){

        $nama_file = time().'_'.$file;

        if(move_uploaded_file($tmp, "../assets/img/".$nama_file)){

            mysqli_query($conn,"INSERT INTO event 
            (judul,deskripsi,tanggal,lokasi,gambar)
            VALUES ('$judul','$deskripsi','$tanggal','$lokasi','$nama_file')");

            header("Location: event.php");
            exit;

        } else {
            echo "Upload gagal";
        }
    }
}
?>

<h2>Tambah Event</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="judul" placeholder="Judul Event" class="form-control mb-2" required>

<textarea name="deskripsi" placeholder="Deskripsi" class="form-control mb-2" required></textarea>

<input type="date" name="tanggal" class="form-control mb-2" required>

<input type="text" name="lokasi" placeholder="Lokasi" class="form-control mb-2" required>

<input type="file" name="gambar" class="form-control mb-2" required>

<button name="submit" class="btn btn-success">Simpan</button>

</form>