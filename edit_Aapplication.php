<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: application.php');
    exit();
}
$idapplication = $_GET['id'];

$stmt = $conn->prepare("
    SELECT application.*, user.nep 
    FROM application 
    INNER JOIN user ON application.iduser = user.iduser 
    WHERE application.idapplication = :idapplication
");
$stmt->execute(['idapplication' => $idapplication]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    header('Location: application.php');
    exit();
}

$stmt = $conn->query("SELECT iduser, nep FROM user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomapplication = $_POST['nomapplication'];
    $description = $_POST['description'];
    $iduser = $_POST['iduser'];

    $stmt = $conn->prepare("
        UPDATE application 
        SET nomapplication = :nomapplication, description = :description, iduser = :iduser 
        WHERE idapplication = :idapplication
    ");
    $stmt->execute([
        'nomapplication' => $nomapplication,
        'description' => $description,
        'iduser' => $iduser,
        'idapplication' => $idapplication
    ]);

    header('Location: application.php');
    exit();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Modifier l'application</title>
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
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
                <a class="navbar-brand" href="index.html">Binary Admin</a> 
            </div>
            <div style="color: white;padding: 15px 50px 5px 50px;float: right;font-size: 16px;">
               &nbsp; <a href="logout.php" class="btn btn-danger square-btn-adjust">Logout</a>
            </div>
        </nav>   

        <!-- Barre de navigation latérale (inchangée) -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li class="text-center">
                        <img src="assets/img/find_user.png" class="user-image img-responsive"/>
                    </li>
                    <li>
                        <a href="admin.php"><i class="fa fa-table fa-3x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
                    </li>
                    <li>
                        <a class="active-menu" href="application.php"><i class="fa fa-users fa-3x"></i> Application</a>
                    </li>
                </ul>
            </div>
        </nav> 

        <!-- Contenu principal (modifié) -->
        <div id="page-wrapper">
            <div id="page-inner">
                <h2>Modifier l'application</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nom de l'application</label>
                        <input type="text" name="nomapplication" class="form-control" value="<?= htmlspecialchars($application['nomapplication']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required><?= htmlspecialchars($application['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Utilisateur responsable</label>
                        <select name="iduser" class="form-control" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['iduser'] ?>" <?= $user['iduser'] == $application['iduser'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['nep']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="application.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript (inchangés) -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>