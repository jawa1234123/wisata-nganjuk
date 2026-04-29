<?php
$page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    margin:0;
    background:#0f172a;
    color:white;
}

/* SIDEBAR */
.sidebar{
    width:220px;
    height:100vh;
    position:fixed;
    background:#020617;
    padding:20px;
}

.sidebar a{
    display:block;
    color:white;
    padding:10px;
    border-radius:8px;
    margin-bottom:5px;
    text-decoration:none;
}

.sidebar a:hover{
    background:#1e293b;
}

/* ACTIVE MENU */
.active{
    background:#2563eb;
}

/* CONTENT */
.content{
    margin-left:220px;
    padding:30px;
    width:calc(100% - 220px);
}

/* CARD */
.card{
    background:#1e293b;
    border:none;
    color:white;
}

/* TABLE */
.table{
    background:#1e293b;
    color:white;
}

</style>
</head>

<body>

<div class="sidebar">
    <h4>Admin</h4>

    <a class="<?= ($page=='dashboard.php')?'active':'' ?>" href="dashboard.php">Dashboard</a>
    <a class="<?= ($page=='wisata.php')?'active':'' ?>" href="wisata.php">Wisata</a>
    <a class="<?= ($page=='kuliner.php')?'active':'' ?>" href="kuliner.php">Kuliner</a>
    <a class="<?= ($page=='event.php')?'active':'' ?>" href="event.php">Event</a>

 <a href="logout.php">Logout</a>
</div>

<div class="content">