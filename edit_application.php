<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idapplication = $_POST['idapplication'];
    $nomapplication = $_POST['nomapplication'];
    $description = $_POST['description'];
    $nomresponsable = $_POST['nomresponsable'];

    $stmt = $conn->prepare("UPDATE application SET nomapplication = :nomapplication, description = :description, nomresponsable = :nomresponsable WHERE idapplication = :idapplication AND iduser = :iduser");
    $stmt->execute([
        'idapplication' => $idapplication,
        'nomapplication' => $nomapplication,
        'description' => $description,
        'nomresponsable' => $nomresponsable,
        'iduser' => $_SESSION['iduser']
    ]);

    header('Location: index.php'); 
    exit();
}
if (isset($_GET['id'])) {
    $idapplication = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM application WHERE idapplication = :idapplication AND iduser = :iduser");
    $stmt->execute(['idapplication' => $idapplication, 'iduser' => $_SESSION['iduser']]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        header('Location: index.php'); 
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer une application</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h1>Éditer une application</h1>
        <form method="POST">
            <input type="hidden" name="idapplication" value="<?php echo $app['idapplication']; ?>">
            <div class="form-group">
                <label for="nomapplication">Nom de l'application</label>
                <input type="text" class="form-control" id="nomapplication" name="nomapplication" value="<?php echo htmlspecialchars($app['nomapplication']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($app['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="nomresponsable">Responsable</label>
                <input type="text" class="form-control" id="nomresponsable" name="nomresponsable" value="<?php echo htmlspecialchars($app['nomresponsable']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>
</body>
</html>