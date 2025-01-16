<?php
header('Content-Type: application/json'); // Définir le type de contenu comme JSON
require 'db.php'; // Inclure la connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'] ?? null;
    $mdp = $data['mdp'] ?? null;

    // Vérifier si l'email et le mot de passe sont fournis
    if (!$email || !$mdp) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Email et mot de passe sont requis.']);
        exit();
    }

    // Récupérer l'utilisateur depuis la base de données
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if (!$user) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Utilisateur non trouvé.']);
        exit();
    }

    // Vérifier si le mot de passe est correct
    if (!password_verify($mdp, $user['mdp'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Mot de passe incorrect.']);
        exit();
    }

    // Vérifier si la date du token n'a pas dépassé 30 jours
    $dateToken = new DateTime($user['date_token']); // Date du token
    $now = new DateTime(); // Date actuelle
    $interval = $dateToken->diff($now); // Différence entre les deux dates

    if ($interval->days > 30) {
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'Le token a expiré.']);
        exit();
    }

    // Si tout est correct, renvoyer le token
    echo json_encode([
        'token' => $user['token'],
        'message' => 'Connexion réussie.'
    ]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée.']);
}