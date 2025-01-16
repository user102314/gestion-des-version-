<?php
require 'db.php'; 

function generateToken($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $token;
}

try {
    $conn = get_db_connection();

    $stmt = $conn->query("SELECT iduser, token, date_token FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $now = new DateTime();
    foreach ($users as $user) {
        if (!empty($user['token'])) {
            $dateToken = new DateTime($user['date_token']);

            $interval = $dateToken->diff($now);
            $daysDifference = $interval->days;

            if ($daysDifference > 30) {
                $newToken = generateToken();

                $updateStmt = $conn->prepare("UPDATE user SET token = :token, date_token = :date_token WHERE iduser = :iduser");
                $updateStmt->execute([
                    'token' => $newToken,
                    'date_token' => $now->format('Y-m-d'), // Date actuelle
                    'iduser' => $user['iduser']
                ]);

                echo "Token régénéré pour l'utilisateur ID {$user['iduser']}. Nouveau token : $newToken\n";
            }
        }
    }
    echo "Vérification des tokens terminée.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} finally {
    $conn = null;
}