<?php
include '../config.php';

if(isset($_POST['email'])){
       global $conn;
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = mysqli_query($conn,"SELECT * FROM admin WHERE email='$email' AND password='$password'");

    if(mysqli_num_rows($query) > 0){
        $_SESSION['admin'] = $email;

        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Login gagal');window.location='login.php';</script>";
    }
} else {
    echo "Form tidak terkirim!";
}
?>