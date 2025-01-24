<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

// Récupérer les filtres depuis l'URL
$filter_date = $_GET['date'] ?? '';
$filter_name = $_GET['name'] ?? '';

// Construire la requête SQL en fonction des filtres
$query = "
    SELECT l.*, u.nep, u.iduser 
    FROM logs l
    LEFT JOIN user u ON l.token = u.token
    WHERE 1
";

if (!empty($filter_date)) {
    $query .= " AND DATE(l.date) = :filter_date";
}

if (!empty($filter_name)) {
    $query .= " AND u.nep LIKE :filter_name";
}

$query .= " ORDER BY l.date DESC";

// Préparer et exécuter la requête
$stmt = $conn->prepare($query);

if (!empty($filter_date)) {
    $stmt->bindValue(':filter_date', $filter_date);
}

if (!empty($filter_name)) {
    $stmt->bindValue(':filter_name', "%$filter_name%");
}

$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer l'ajout d'un avertissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['warn_user'])) {
    $user_id = $_POST['user_id'];
    $nom_user = $_POST['nom_user'];
    $date_punishment = date('Y-m-d H:i:s');

    // Insérer l'avertissement dans la table punishment
    $insert_query = "INSERT INTO punishment (user_id, nom_user, date_punishment) VALUES (:user_id, :nom_user, :date_punishment)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bindValue(':user_id', $user_id);
    $insert_stmt->bindValue(':nom_user', $nom_user);
    $insert_stmt->bindValue(':date_punishment', $date_punishment);
    $insert_stmt->execute();

    // Rediriger pour éviter la soumission multiple du formulaire
    header('Location: controle.php');
    exit();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des utilisateurs qui ont téléchargé la dernière version</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <style>
        .warn-btn {
            background-color: #ffcc00;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
        }
        .warn-btn:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Barre de navigation supérieure -->
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

        <!-- Barre de navigation latérale -->
        <nav class="navbar-default navbar-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="main-menu">
            <li class="text-center">
                <img src="img/admin (2).png" class="user-image img-responsive"/>
            </li>
            <li>
                <a href="admin.php"><i class="fa fa-dashboard fa-3x"></i> Dashboard</a>
            </li>
            <li>
                <a  href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
            </li>
            <li>
                <a href="application.php"><i class="fa fa-dashboard fa-3x"></i> Application</a>
            </li>
            <li>
                <a class="active-menu" href="controle.php"><i class="fa fa-check-circle fa-3x"></i> Controle</a>
            </li>
            <li>
                <a href="logout.php"><i class="fa fa-sign-out fa-3x"></i> Se déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

        <!-- Contenu principal -->
        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Gestion des utilisateurs qui ont téléchargé la dernière version</h2>
                        <hr />
                    </div>
                </div>

                <!-- Formulaire de filtre -->
                <div class="row">
                    <div class="col-md-12">
                        <form method="GET" action="controle.php" class="form-inline">
                            <div class="form-group">
                                <label for="date">Filtrer par date :</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>" />
                            </div>
                            <div class="form-group" style="margin-left: 20px;">
                                <label for="name">Filtrer par nom :</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($filter_name) ?>" placeholder="Nom et prénom" />
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-left: 20px;">Filtrer</button>
                            <a href="controle.php" class="btn btn-default" style="margin-left: 10px;">Réinitialiser</a>
                        </form>
                        <hr />
                    </div>
                </div>

                <!-- Tableau des logs -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Liste des logs
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>IP</th>
                                                <th>Date</th>
                                                <th>Adresse MAC</th>
                                                <th>Nom et prénom</th>
                                                <th>Vu</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log['ip']) ?></td>
                                                    <td><?= htmlspecialchars($log['date']) ?></td>
                                                    <td><?= htmlspecialchars($log['adresse'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($log['nep'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($log['vu']) ?></td>
                                                    <td>
                                                        <form method="POST" action="controle.php" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($log['iduser']) ?>" />
                                                            <input type="hidden" name="nom_user" value="<?= htmlspecialchars($log['nep']) ?>" />
                                                            <button type="submit" name="warn_user" class="warn-btn">
                                                                <i class="fa fa-warning"></i> Warn
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>