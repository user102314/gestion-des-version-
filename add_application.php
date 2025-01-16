<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$iduser = $_SESSION['iduser'];

// Vérifier si l'utilisateur a déjà une application
$stmt = $conn->prepare("SELECT COUNT(*) FROM application WHERE iduser = :iduser");
$stmt->execute(['iduser' => $iduser]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    // L'utilisateur a déjà une application
    $_SESSION['error'] = "Vous ne pouvez avoir qu'une seule application.";
    header('Location: index.php');
    exit();
}

// Récupérer les données du formulaire
$nomapplication = $_POST['nomapplication'];
$description = $_POST['description'];
$nomresponsable = $_POST['nomresponsable'];

// Insérer la nouvelle application
$stmt = $conn->prepare("INSERT INTO application (nomapplication, description, nomresponsable, iduser) VALUES (:nomapplication, :description, :nomresponsable, :iduser)");
$stmt->execute([
    'nomapplication' => $nomapplication,
    'description' => $description,
    'nomresponsable' => $nomresponsable,
    'iduser' => $iduser
]);

header('Location: index.php');
exit();
?>