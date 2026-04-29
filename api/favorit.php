<?php
include '../config.php';

$where="WHERE 1";
if(isset($_GET['kategori']) && $_GET['kategori']!=""){
$where.=" AND kategori='$_GET[kategori]'";
}

$q=mysqli_query($conn,"SELECT * FROM wisata $where");

$data=[];
while($d=mysqli_fetch_assoc($q)){
if($d['latitude']){
$data[]=$d;
}
}

echo json_encode($data);