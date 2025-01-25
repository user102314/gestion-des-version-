<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier reCAPTCHA v3
    $recaptchaResponse = $_POST['recaptcha_response'];
    $secretKey = "6LcxasIqAAAAAGq4KD8fSt8BPpIDf9IH3fvh17nI"; // Votre clé secrète reCAPTCHA v3
    $url = "https://www.google.com/recaptcha/api/siteverify";

    // Envoyer une requête à l'API reCAPTCHA
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $responseKeys = json_decode($response, true);

    // Vérifier le score reCAPTCHA (par défaut, un score > 0.5 est considéré comme valide)
    if ($responseKeys["success"] && $responseKeys["score"] >= 0.5) {
        // reCAPTCHA valide, continuer avec la vérification de l'utilisateur
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
    } else {
        $error = "Échec de la vérification reCAPTCHA. Vous semblez être un robot.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Script reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LcxasIqAAAAAHUYNNs5DxCexdER2wg3q5jRfobd"></script>
    <style>
        body {
            background: linear-gradient(135deg, #8B0000, #4B0000); /* Dégradé rouge foncé */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s ease-in-out;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #8B0000; /* Titre en rouge foncé */
        }

        .login-container .form-control {
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #8B0000; /* Bordure rouge foncé */
            padding: 0.75rem;
        }

        .login-container .btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 5px;
            background: #8B0000; /* Bouton rouge foncé */
            border: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-container .btn:hover {
            background: #6B0000; /* Bouton rouge foncé plus sombre au survol */
        }

        .login-container .error {
            color: #FF0000; /* Texte d'erreur en rouge vif */
            text-align: center;
            margin-bottom: 1rem;
        }

        .login-container p {
            text-align: center;
            margin-top: 1rem;
            color: #333; /* Texte en gris foncé */
        }

        .login-container a {
            color: #8B0000; /* Lien en rouge foncé */
            text-decoration: none;
        }

        .login-container a:hover {
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
    <div class="login-container">
        <h1>Connexion</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="login.php" method="POST" id="login-form">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="mdp" class="form-control" placeholder="Mot de passe" required>
            </div>
            <!-- Champ caché pour le token reCAPTCHA -->
            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
            <button type="submit" class="btn">Se connecter</button>
        </form>
        <p>Pas encore de compte ? <a href="signup.php">S'inscrire</a></p>
        <p style="font-size:10px;">@Ce site est sucurisé par <a href="https://www.google.com/recaptcha/about/">recaptcha v3</a>  </p>

    </div><br>

    <script>
        // Exécuter reCAPTCHA lors de la soumission du formulaire
        document.getElementById('login-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher la soumission par défaut

            // Générer le token reCAPTCHA
            grecaptcha.ready(function() {
                grecaptcha.execute('6LcxasIqAAAAAHUYNNs5DxCexdER2wg3q5jRfobd', { action: 'login' }).then(function(token) {
                    // Ajouter le token au champ caché
                    document.getElementById('recaptchaResponse').value = token;

                    // Soumettre le formulaire
                    document.getElementById('login-form').submit();
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>