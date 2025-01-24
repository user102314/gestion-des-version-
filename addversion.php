<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$iduser = $_SESSION['iduser'];
$nomUtilisateur = $_SESSION['email'];

$stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser");
$stmt->execute(['iduser' => $iduser]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$lastVersion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idapplication = $_POST['idapplication'];
    $version = $_POST['version'];

    $stmt = $conn->prepare("SELECT * FROM version WHERE idapplication = :idapplication AND version = :version");
    $stmt->execute(['idapplication' => $idapplication, 'version' => $version]);
    if ($stmt->fetch()) {
        $error = "Cette version existe déjà pour cette application.";
    } else {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = basename($_FILES['file']['name']);
            $filepath = $uploadDir . uniqid() . '_' . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
                $stmt = $conn->prepare("INSERT INTO folder (filename, filepath, dateupload) VALUES (:filename, :filepath, NOW())");
                $stmt->execute(['filename' => $filename, 'filepath' => $filepath]);
                $idfolderp = $conn->lastInsertId();

                $stmt = $conn->prepare("INSERT INTO version (idapplication, version, idfolderp) VALUES (:idapplication, :version, :idfolderp)");
                $stmt->execute([
                    'idapplication' => $idapplication,
                    'version' => $version,
                    'idfolderp' => $idfolderp
                ]);

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
    <style>
        /* Style pour le contenu */
        #page-wrapper {
            padding: 20px;
        }

        #page-inner {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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

        .btn {
            margin: 5px;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-primary {
            background: #8B0000;
            color: white;
        }

        .btn-primary:hover {
            background: #6B0000;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
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
    <div id="wrapper">
        <!-- Barre de navigation supérieure (inchangée) -->
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
                <a href="index.php"><i class="fa fa-tachometer fa-3x"></i> Dashboard</a>
            </li>
            <li>
                <a href="version.php"><i class="fa fa-code-fork fa-3x"></i> Version</a>
            </li>   
            <li>
                <a class="active-menu"  href="addversion.php"><i class="fa fa-plus-circle fa-3x"></i> Add Version</a>
            </li>  
            <li>
                <a href="notification.php"><i class="fa fa-bell fa-3x"></i> Notification</a>
            </li> 
            <li>
                <a href="logout.php"><i class="fa fa-sign-out fa-3x"></i> Se déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

        <!-- Contenu principal (modifié) -->
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