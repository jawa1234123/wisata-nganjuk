<?php
include 'config.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $q = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($q);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        header("Location:index.php");
    } else {
        echo "<script>alert('Email atau password salah!');</script>";
    }
}
?>

<form method="POST">
<h2>User Login</h2>

<input name="email" class="form-control mb-2" placeholder="Email">

<input type="password" name="password" class="form-control mb-2" placeholder="Password">

<button name="login" class="btn btn-primary">Login</button>

<p class="mt-2">
Belum punya akun? 
<a href="register.php">Daftar</a>
</p>

</form>