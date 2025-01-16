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
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
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
</body>
</html>