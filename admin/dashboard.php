<?php
include '../config.php';
include 'auth.php'; // proteksi halaman

include 'layout.php';
   global $conn;

// ambil total wisata
$query = mysqli_query($conn, "SELECT COUNT(*) as jml FROM wisata");
$data = mysqli_fetch_assoc($query);
$total = $data['jml'];
?>

<h2>Dashboard</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card p-3">
            <h3><?= $total ?></h3>
            <p>Total Wisata</p>
        </div>
    </div>
</div>

</div>
</body>
</html>