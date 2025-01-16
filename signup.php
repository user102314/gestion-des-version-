<?php
require 'db.php';

// Fonction pour générer un token alphanumérique de 16 caractères
function generateToken($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $token;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nep = $_POST['nep'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];
    $confirm_mdp = $_POST['confirm_mdp'];

    if ($mdp === $confirm_mdp) {
        $hashed_mdp = password_hash($mdp, PASSWORD_BCRYPT);

        // Générer un token et récupérer la date actuelle
        $token = generateToken(16);
        $date_token = date('Y-m-d'); // Date système au format YYYY-MM-DD

        // Insérer l'utilisateur dans la base de données avec le token et la date
        $stmt = $conn->prepare("INSERT INTO user (nep, email, mdp, role, token, date_token) VALUES (:nep, :email, :mdp, :role, :token, :date_token)");
        $stmt->execute([
            'nep' => $nep,
            'email' => $email,
            'mdp' => $hashed_mdp,
            'role' => 0,
            'token' => $token,
            'date_token' => $date_token
        ]);

        // Rediriger vers la page de connexion
        header('Location: login.php');
        exit();
    } else {
        $error = "Les mots de passe ne correspondent pas.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>
    <div class="signup-container">
        <h1>Inscription</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="signup.php" method="POST">
            <input type="text" name="nep" placeholder="Nom et prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <input type="password" name="confirm_mdp" placeholder="Confirmer le mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>