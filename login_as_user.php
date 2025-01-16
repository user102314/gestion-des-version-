<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur à partir de l'URL
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}
$userId = $_GET['id'];

// Récupérer les informations de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM user WHERE iduser = :iduser");
$stmt->execute(['iduser' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit();
}

// Mettre à jour la session pour se connecter en tant que cet utilisateur
$_SESSION['iduser'] = $user['iduser'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Rediriger vers la page d'accueil ou le tableau de bord de l'utilisateur
header('Location: index.php'); // Ou une autre page appropriée
exit();
?>