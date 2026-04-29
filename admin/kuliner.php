<?php
include '../config.php';
if(!isset($_SESSION['admin'])) header("Location: login.php");
  global $conn;

include 'layout.php';

$data = mysqli_query($conn,"SELECT * FROM kuliner");
?>

<h2>Kelola Kuliner</h2>

<a href="tambah_kuliner.php?tipe=kuliner" class="btn btn-success mb-3">+ Tambah</a>

<table class="table table-dark">
<tr>
<th>No</th>
<th>Nama</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($d=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $d['nama_kuliner'] ?></td>

<td>
<a href="edit_kuliner.php?id=<?= $d['id'] ?>&tipe=kuliner" class="btn btn-warning btn-sm">Edit</a>
<a href="hapus_kuliner.php?id=<?= $d['id'] ?>&tipe=kuliner" class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>
<?php } ?>

</table>

</div>
</body>
</html>