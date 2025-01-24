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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nep = $_POST['nep'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];
    $confirm_mdp = $_POST['confirm_mdp'];

    if ($mdp === $confirm_mdp) {
        $hashed_mdp = password_hash($mdp, PASSWORD_BCRYPT);
        $token = generateToken(16);
        $date_token = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO user (nep, email, mdp, role, token, date_token) VALUES (:nep, :email, :mdp, :role, :token, :date_token)");
        $stmt->execute([
            'nep' => $nep,
            'email' => $email,
            'mdp' => $hashed_mdp,
            'role' => 0,
            'token' => $token,
            'date_token' => $date_token
        ]);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #8B0000, #4B0000);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s ease-in-out;
        }

        .signup-container h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #8B0000;
        }

        .signup-container .form-control {
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #8B0000;
            padding: 0.75rem;
        }

        .signup-container .btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 5px;
            background: #8B0000;
            border: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .signup-container .btn:hover {
            background: #6B0000;
        }

        .signup-container .error {
            color: #FF0000;
            text-align: center;
            margin-bottom: 1rem;
        }

        .signup-container p {
            text-align: center;
            margin-top: 1rem;
            color: #333;
        }

        .signup-container a {
            color: #8B0000;
            text-decoration: none;
        }

        .signup-container a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Inscription</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="signup.php" method="POST">
            <div class="mb-3">
                <input type="text" name="nep" class="form-control" placeholder="Nom et prénom" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="mdp" class="form-control" placeholder="Mot de passe" required>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_mdp" class="form-control" placeholder="Confirmer le mot de passe" required>
            </div>
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>