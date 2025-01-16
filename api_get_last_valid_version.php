<?php
header('Content-Type: application/json'); // Définir le type de contenu comme JSON
require 'db.php'; // Inclure la connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? null;

    // Vérifier si le token est fourni
    if (!$token) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Token est requis.']);
        exit();
    }

    // Récupérer l'utilisateur associé au token
    $stmt = $conn->prepare("SELECT * FROM user WHERE token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if (!$user) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Utilisateur non trouvé.']);
        exit();
    }

    // Récupérer l'application associée à l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser");
    $stmt->execute(['iduser' => $user['iduser']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'application existe
    if (!$application) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Aucune application trouvée pour cet utilisateur.']);
        exit();
    }

    // Récupérer la dernière version validée de l'application
    $stmt = $conn->prepare("
        SELECT v.*, f.filename, f.filepath 
        FROM version v 
        JOIN folder f ON v.idfolderp = f.idfolderp 
        JOIN valid val ON v.idversion = val.idversion 
        WHERE v.idapplication = :idapplication AND val.estvalid = 1 
        ORDER BY v.idversion DESC 
        LIMIT 1
    ");
    $stmt->execute(['idapplication' => $application['idapplication']]);
    $lastValidVersion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si une version validée existe
    if (!$lastValidVersion) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Aucune version validée trouvée pour cette application.']);
        exit();
    }

    // Retourner la dernière version validée
    echo json_encode([
        'application' => [
            'idapplication' => $application['idapplication'],
            'nomapplication' => $application['nomapplication'],
            'description' => $application['description']
        ],
        'last_valid_version' => $lastValidVersion
    ]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée.']);
}