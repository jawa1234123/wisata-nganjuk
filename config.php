<?php
session_start(); // 🔥 WAJIB BANGET

$conn = mysqli_connect("localhost", "root", "", "wisata-nganjuk");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>