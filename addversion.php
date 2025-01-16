<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$iduser = $_SESSION['iduser'];
$nomUtilisateur = $_SESSION['email']; // Supposons que le nom de l'utilisateur est stocké dans la session

// Récupérer les applications de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser");
$stmt->execute(['iduser' => $iduser]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$lastVersion = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idapplication = $_POST['idapplication'];
    $version = $_POST['version'];

    // Vérifier si la version existe déjà
    $stmt = $conn->prepare("SELECT * FROM version WHERE idapplication = :idapplication AND version = :version");
    $stmt->execute(['idapplication' => $idapplication, 'version' => $version]);
    if ($stmt->fetch()) {
        $error = "Cette version existe déjà pour cette application.";
    } else {
        // Gestion du fichier téléversé
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = basename($_FILES['file']['name']);
            $filepath = $uploadDir . uniqid() . '_' . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
                // Insérer le fichier dans la table `folder`
                $stmt = $conn->prepare("INSERT INTO folder (filename, filepath, dateupload) VALUES (:filename, :filepath, NOW())");
                $stmt->execute(['filename' => $filename, 'filepath' => $filepath]);
                $idfolderp = $conn->lastInsertId();

                // Insérer la version dans la table `version`
                $stmt = $conn->prepare("INSERT INTO version (idapplication, version, idfolderp) VALUES (:idapplication, :version, :idfolderp)");
                $stmt->execute([
                    'idapplication' => $idapplication,
                    'version' => $version,
                    'idfolderp' => $idfolderp
                ]);

                // Insérer une entrée dans la table `misajour`
                $stmt = $conn->prepare("INSERT INTO misajour (date, nom) VALUES (NOW(), :nom)");
                $stmt->execute(['nom' => $nomUtilisateur]);

                header('Location: version.php');
                exit();
            } else {
                $error = "Erreur lors du téléversement du fichier.";
            }
        } else {
            $error = "Veuillez sélectionner un fichier valide.";
        }
    }
}

// Récupérer la dernière version de l'application sélectionnée
if (isset($_POST['idapplication'])) {
    $idapplication = $_POST['idapplication'];
    $stmt = $conn->prepare("SELECT version FROM version WHERE idapplication = :idapplication ORDER BY idversion DESC LIMIT 1");
    $stmt->execute(['idapplication' => $idapplication]);
    $lastVersion = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ajouter une version</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-cls-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">Binary User</a> 
            </div>
            
        </nav>   
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li class="text-center">
                        <img src="img/user.png" class="user-image img-responsive"/>
                    </li>
                    <li>
                        <a href="index.php"><i class="fa fa-table fa-3x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="version.php"><i class="fa fa-desktop fa-3x"></i> Version</a>
                    </li>   
                    <li>
                        <a class="active-menu" href="#"><i class="fa fa-desktop fa-3x"></i>Add Version</a>
                    </li>  
                    <li>
                        <a href="logout.php"><i class="fa fa-desktop fa-3x"></i> Se déconnecter</a>
                    </li>
                </ul>
            </div>
        </nav>  

        <!-- Page Content -->
        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Ajouter une version</h2>
                        <hr>
                    </div>
                </div>

                <!-- Formulaire d'ajout de version -->
                <div class="row">
                    <div class="col-md-6">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="addversion.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="idapplication">Application</label>
                                <select class="form-control" id="idapplication" name="idapplication" required onchange="this.form.submit()">
                                    <option value="">Sélectionnez une application</option>
                                    <?php foreach ($applications as $app): ?>
                                        <option value="<?php echo $app['idapplication']; ?>" <?php echo (isset($_POST['idapplication']) && $_POST['idapplication'] == $app['idapplication']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($app['nomapplication']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($lastVersion): ?>
                                <div class="alert alert-info">
                                    Dernière version de cette application : <strong><?php echo htmlspecialchars($lastVersion); ?></strong>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="version">Version</label>
                                <input type="text" class="form-control" id="version" name="version" required>
                            </div>
                            <div class="form-group">
                                <label for="file">Fichier</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter la version</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>