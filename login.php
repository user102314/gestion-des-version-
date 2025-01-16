<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    // Récupérer l'utilisateur depuis la base de données
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // Vérifier le mot de passe
        if (password_verify($mdp, $user['mdp'])) {
            // Authentification réussie
            $_SESSION['iduser'] = $user['iduser'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // Stocker le rôle dans la session

            // Redirection en fonction du rôle
            if ($user['role'] == 1) {
                header('Location: admin.php'); // Rediriger vers la page admin
            } else {
                header('Location: index.php'); // Rediriger vers la page index
            }
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Utilisateur non trouvé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore de compte ? <a href="signup.php">S'inscrire</a></p>
    </div>
</body>
</html>