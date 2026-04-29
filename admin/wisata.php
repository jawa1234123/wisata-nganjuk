<?php
include '../config.php';
if(!isset($_SESSION['admin'])) header("Location: login.php");
   global $conn;

include 'layout.php';

$data = mysqli_query($conn,"SELECT * FROM wisata");
?>

<h2>Kelola Wisata</h2>

<a href="tambah_wisata.php" class="btn btn-success mb-3">+ Tambah</a>

<table class="table table-bordered table-dark">
<tr>
<th>No</th>
<th>Gambar</th>
<th>Nama</th>
<th>Lokasi</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($d=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++ ?></td>

<td>
<img src="../assets/img/<?= $d['gambar'] ?>" width="80">
</td>

<td><?= $d['nama'] ?></td>
<td><?= $d['lokasi'] ?></td>

<td>
<a href="edit_wisata.php?id=<?= $d['id'] ?>&tipe=wisata" class="btn btn-warning btn-sm">Edit</a>
<a href="hapus_wisata.php?id=<?= $d['id'] ?>" class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>
<?php } ?>

</table>

</div>
</body>
</html>