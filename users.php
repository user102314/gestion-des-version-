<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

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
    <style>
        /* Style pour le tableau */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #8B0000;
            color: white;
            font-weight: bold;
        }

        .table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Style pour les boutons avec icônes */
        .btn-icon {
            margin: 2px;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-icon:hover {
            opacity: 0.8;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: white;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
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
                <a class="active-menu" href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
            </li>
            <li>
                <a href="application.php"><i class="fa fa-dashboard fa-3x"></i> Application</a>
            </li>
            <li>
                <a href="controle.php"><i class="fa fa-check-circle fa-3x"></i> Controle</a>
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
                <h2>Gestion des utilisateurs</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom et prénom</th>
                            <th>Email</th>
                            <th>Mot de passe</th>
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
                                <td><?= htmlspecialchars($user['mdp']) ?></td>
                                <td><?= $user['role'] == 1 ? 'Admin' : 'Utilisateur' ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['iduser'] ?>" class="btn btn-icon btn-primary" title="Modifier">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="delete_user.php?id=<?= $user['iduser'] ?>" class="btn btn-icon btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <?php if ($user['role'] == 0): ?>
                                        <a href="set_admin.php?id=<?= $user['iduser'] ?>" class="btn btn-icon btn-success" title="Définir Admin">
                                            <i class="fa fa-user-plus"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="set_user.php?id=<?= $user['iduser'] ?>" class="btn btn-icon btn-warning" title="Définir Utilisateur">
                                            <i class="fa fa-user-minus"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="login_as_user.php?id=<?= $user['iduser'] ?>" class="btn btn-icon btn-info" title="Se connecter">
                                        <i class="fa fa-sign-in"></i>
                                    </a>
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