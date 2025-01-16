<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE user SET role = 1 WHERE iduser = :id");
    $stmt->execute(['id' => $id]);

    header('Location: users.php');
    exit();
}
?>