<?php
include '../config.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Login Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#0f172a;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    background:#1e293b;
    color:white;
    padding:30px;
    border-radius:12px;
    width:300px;
}
</style>
</head>

<body>

<form method="POST" action="proses_login.php" class="card">
<h4 class="text-center mb-3">Login Admin</h4>

<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button class="btn btn-primary w-100">Login</button>

</form>

</body>
</html>