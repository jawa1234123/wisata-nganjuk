<?php
include 'config.php';

if(isset($_POST['daftar'])){

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // cek email sudah ada
    $cek = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Email sudah terdaftar!');</script>";
    } else {

        mysqli_query($conn,"INSERT INTO users VALUES(NULL,'$nama','$email','$password')");
        echo "<script>alert('Registrasi berhasil! Silakan login'); window.location='login_user.php';</script>";

    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<div class="container mt-5">
<div class="col-md-4 mx-auto">

<h3>Register User</h3>

<form method="POST">

<input type="text" name="nama" class="form-control mb-3" placeholder="Nama" required>

<input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button name="daftar" class="btn btn-success w-100">Daftar</button>

</form>

<p class="mt-3">
Sudah punya akun? 
<a href="login_user.php">Login</a>
</p>

</div>
</div>

</body>
</html>