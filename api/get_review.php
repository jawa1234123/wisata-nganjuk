<?php
include '../config.php';
$q=mysqli_query($conn,"SELECT * FROM review");
while($d=mysqli_fetch_assoc($q)){
echo "<p>".$d['komentar']."</p>";
}