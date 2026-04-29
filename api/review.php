<?php
include '../config.php';
mysqli_query($conn,"INSERT INTO review VALUES(NULL,$_SESSION[user][id],$_POST[id],$_POST[rating],'$_POST[komentar]')");