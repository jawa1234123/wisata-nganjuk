<?php
include '../config.php';
include 'auth.php';
include 'layout.php';
  
global $conn;
$data = mysqli_query($conn,"SELECT * FROM event");
?>

<h2>Kelola Event</h2>

<a href="tambah_event.php" class="btn btn-success mb-3">+ Tambah</a>

<table class="table table-dark">
<tr>
<th>No</th>
<th>Gambar</th>
<th>Judul</th>
<th>Tanggal</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($d=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++ ?></td>

<td>
<img src="../assets/img/<?= $d['gambar'] ?>" width="80">
</td>

<td><?= $d['judul'] ?></td>
<td><?= $d['tanggal'] ?></td>

<td>
<a href="edit_event.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="hapus_event.php?id=<?= $d['id'] ?>" class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>
<?php } ?>

</table>