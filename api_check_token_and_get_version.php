<?php
header('Content-Type: application/json'); 
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'] ?? null;
    $mdp = $data['mdp'] ?? null;

    if (!$email || !$mdp) {
        http_response_code(400);
        echo json_encode(['error' => 'Email et mot de passe sont requis.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404); 
        echo json_encode(['error' => 'Utilisateur non trouvé.']);
        exit();
    }

    if (!password_verify($mdp, $user['mdp'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Mot de passe incorrect.']);
        exit();
    }

    if (empty($user['token'])) {
        http_response_code(403); 
        echo json_encode(['error' => 'Aucun token trouvé pour cet utilisateur.']);
        exit();
    }

    $dateToken = new DateTime($user['date_token']); 
    $now = new DateTime(); 
    $interval = $dateToken->diff($now); 

    if ($interval->days > 30) {
        http_response_code(403); 
        echo json_encode(['error' => 'Le token a expiré.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser");
    $stmt->execute(['iduser' => $user['iduser']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        http_response_code(404); 
        echo json_encode(['error' => 'Aucune application trouvée pour cet utilisateur.']);
        exit();
    }

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

    if (!$lastValidVersion) {
        http_response_code(404);
        echo json_encode(['error' => 'Aucune version validée trouvée pour cette application.']);
        exit();
    }

    echo json_encode([
        'application' => [
            'idapplication' => $application['idapplication'],
            'nomapplication' => $application['nomapplication'],
            'description' => $application['description']
        ],
        'last_valid_version' => $lastValidVersion
    ]);
} else {
    http_response_code(405); 
    echo json_encode(['error' => 'Méthode non autorisée.']);
}