<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'application depuis l'URL
if (!isset($_GET['id'])) {
    header('Location: application.php');
    exit();
}
$idapplication = $_GET['id'];

// Récupérer le fichier correspondant à la dernière version validée
$stmt = $conn->prepare("
    SELECT folder.filepath, folder.filename
    FROM valid
    INNER JOIN version ON valid.idversion = version.idversion
    INNER JOIN folder ON version.idfolderp = folder.idfolderp
    WHERE valid.idapplication = :idapplication AND valid.estvalid = 1
    ORDER BY valid.idvalid DESC
    LIMIT 1
");
$stmt->execute(['idapplication' => $idapplication]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    header('Location: application.php');
    exit();
}

// Chemin du fichier
$filepath = $file['filepath'];
$filename = $file['filename'];

// Vérifier si le fichier existe
if (!file_exists($filepath)) {
    die("Le fichier n'existe pas.");
}

// Télécharger le fichier
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>