<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../user/dashboard.php");
    exit();
}
?>