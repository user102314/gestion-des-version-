<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE iduser = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nep = $_POST['nep'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE user SET nep = :nep, email = :email WHERE iduser = :id");
    $stmt->execute(['nep' => $nep, 'email' => $email, 'id' => $id]);

    header('Location: users.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'utilisateur</title>
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

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #8B0000;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
        }

        .form-control {
            border: 1px solid #8B0000;
            border-radius: 5px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #6B0000;
            box-shadow: 0 0 5px rgba(139, 0, 0, 0.5);
        }

        .btn-primary {
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

        .btn-primary:hover {
            background: #6B0000;
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
    <div class="container">
        <h2>Modifier l'utilisateur</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $user['iduser'] ?>">
            <div class="form-group">
                <label>Nom et prénom</label>
                <input type="text" name="nep" class="form-control" value="<?= htmlspecialchars($user['nep']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>

    <!-- Bootstrap 5 JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>