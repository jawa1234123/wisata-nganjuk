<?php include 'config.php';
$id=$_GET['id'];
$d=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM wisata WHERE id=$id"));
?>

<h2><?=$d['nama']?></h2>
<img src="assets/img/<?=$d['gambar']?>" width="300">
<p><?=$d['deskripsi']?></p>

<?php if(isset($_SESSION['user'])){ ?>
<form method="POST">
<button name="fav">❤️ Favorit</button>
</form>

<form method="POST">
<input name="rating">
<textarea name="komentar"></textarea>
<button name="kirim">Kirim</button>
</form>
<?php } ?>

<?php
if(isset($_POST['fav'])){
mysqli_query($conn,"INSERT INTO favorit VALUES(NULL,$_SESSION[user][id],$id)");
}
if(isset($_POST['kirim'])){
mysqli_query($conn,"INSERT INTO review VALUES(NULL,$_SESSION[user][id],$id,$_POST[rating],'$_POST[komentar]')");
}
?>