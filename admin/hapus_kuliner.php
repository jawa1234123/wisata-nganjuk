<?php
include '../config.php';
 global $conn;
$id = $_GET['id'];

mysqli_query($conn,"DELETE FROM kuliner WHERE id=$id");

header("Location: kuliner.php");