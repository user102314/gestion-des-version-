<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['iduser'];

// Vérifier si l'utilisateur a un avertissement dans la table punishment
$query = "SELECT * FROM punishment WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindValue(':user_id', $user_id);
$stmt->execute();
$warn = $stmt->fetch(PDO::FETCH_ASSOC);

// Gérer la suppression de l'avertissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $delete_query = "DELETE FROM punishment WHERE user_id = :user_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindValue(':user_id', $user_id);
    $delete_stmt->execute();

    // Rediriger pour éviter la soumission multiple du formulaire
    header('Location: notification.php');
    exit();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifications</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <style>
        .warn-block {
            background-color: #ffcccc;
            border: 1px solid #ff0000;
            padding: 15px;
            margin: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .warn-block button {
            margin-top: 10px;
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
                <a  href="index.php"><i class="fa fa-tachometer fa-3x"></i> Dashboard</a>
            </li>
            <li>
                <a href="version.php"><i class="fa fa-code-fork fa-3x"></i> Version</a>
            </li>   
            <li>
                <a href="addversion.php"><i class="fa fa-plus-circle fa-3x"></i> Add Version</a>
            </li>  
            <li>
                <a  class="active-menu"  href="notification.php"><i class="fa fa-bell fa-3x"></i> Notification</a>
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
                        <h2>Notifications</h2>
                        <hr />
                    </div>
                </div>

                <!-- Afficher un avertissement si l'utilisateur a un warn -->
                <?php if ($warn): ?>
                    <div class="warn-block">
                        <h3>⚠️ Vous avez un avertissement</h3>
                        <p>Le patron vous a averti le <?= htmlspecialchars($warn['date_punishment']) ?>.</p>
                        <form method="POST" action="notification.php">
                            <button type="submit" name="mark_as_read" class="btn btn-danger">
                                <i class="fa fa-check"></i> J'ai lu
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h3>✅ Aucun avertissement</h3>
                        <p>Vous n'avez aucun avertissement pour le moment.</p>
                    </div>
                <?php endif; ?>
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