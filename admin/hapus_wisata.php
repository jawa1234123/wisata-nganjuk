<?php
include '../config.php';

$id=$_GET['id'];
   global $conn;
mysqli_query($conn,"DELETE FROM wisata WHERE id=$id");

header("Location: wisata.php");