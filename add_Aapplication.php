<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomapplication = $_POST['nomapplication']; // Correction du nom du champ
    $description = $_POST['description'];
    $iduser = $_POST['iduser'];

    // Insérer l'application dans la base de données
    $stmt = $conn->prepare("INSERT INTO application (nomapplication, description, iduser) VALUES (:nomapplication, :description, :iduser)");
    $stmt->execute([
        'nomapplication' => $nomapplication,
        'description' => $description,
        'iduser' => $iduser
    ]);

    // Rediriger vers la page application.php après l'ajout
    header('Location: application.php');
    exit();
}
?>