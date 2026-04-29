<?php
include '../config.php';
include 'auth.php';
include 'layout.php';

$id = $_GET['id'];
$d = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM event WHERE id='$id'"));

if(isset($_POST['update'])){

    $judul     = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal   = $_POST['tanggal'];
    $lokasi    = $_POST['lokasi'];

    $file = $_FILES['gambar']['name'];

    if($file != ""){

        $tmp = $_FILES['gambar']['tmp_name'];
        $nama_file = time().'_'.$file;
        move_uploaded_file($tmp,"../assets/img/".$nama_file);

        mysqli_query($conn,"UPDATE event SET
        judul='$judul',
        deskripsi='$deskripsi',
        tanggal='$tanggal',
        lokasi='$lokasi',
        gambar='$nama_file'
        WHERE id='$id'");

    } else {

        mysqli_query($conn,"UPDATE event SET
        judul='$judul',
        deskripsi='$deskripsi',
        tanggal='$tanggal',
        lokasi='$lokasi'
        WHERE id='$id'");
    }

    header("Location: event.php");
}
?>

<h2>Edit Event</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="judul" value="<?= $d['judul'] ?>" class="form-control mb-2">

<textarea name="deskripsi" class="form-control mb-2"><?= $d['deskripsi'] ?></textarea>

<input type="date" name="tanggal" value="<?= $d['tanggal'] ?>" class="form-control mb-2">

<input type="text" name="lokasi" value="<?= $d['lokasi'] ?>" class="form-control mb-2">

<input type="file" name="gambar" class="form-control mb-2">

<button name="update" class="btn btn-warning">Update</button>

</form>