<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

// Récupérer tous les utilisateurs
$stmt = $conn->query("SELECT * FROM user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des utilisateurs</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
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
                        <a href="admin.php"><i class="fa fa-table fa-3x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a class="active-menu" href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
                    </li>
                    <li>
                        <a href="application.php"><i class="fa fa-users fa-3x"></i> Application</a>
                    </li>
                </ul>
            </div>
        </nav> 

        <!-- Contenu principal -->
        <div id="page-wrapper">
            <div id="page-inner">
                <h2>Gestion des utilisateurs</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom et prénom</th>
                            <th>Email</th>
                            <th>Mot de passe</th> <!-- Nouvelle colonne pour afficher le mot de passe -->
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['iduser']) ?></td>
                                <td><?= htmlspecialchars($user['nep']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['mdp']) ?></td> <!-- Afficher le mot de passe -->
                                <td><?= $user['role'] == 1 ? 'Admin' : 'Utilisateur' ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['iduser'] ?>" class="btn btn-primary">Modifier</a>
                                    <a href="delete_user.php?id=<?= $user['iduser'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                                    <?php if ($user['role'] == 0): ?>
                                        <a href="set_admin.php?id=<?= $user['iduser'] ?>" class="btn btn-success">Définir Admin</a>
                                    <?php else: ?>
                                        <a href="set_user.php?id=<?= $user['iduser'] ?>" class="btn btn-warning">Définir Utilisateur</a>
                                    <?php endif; ?>
                                    <a href="login_as_user.php?id=<?= $user['iduser'] ?>" class="btn btn-info">Se connecter</a> <!-- Bouton "Se connecter" -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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