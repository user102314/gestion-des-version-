<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Récupérer toutes les applications avec les détails de l'utilisateur et la dernière version validée
$stmt = $conn->query("
    SELECT 
        application.idapplication, 
        application.nomapplication, 
        application.description, 
        user.nep,
        valid.version AS last_valid_version
    FROM application 
    INNER JOIN user ON application.iduser = user.iduser
    LEFT JOIN (
        SELECT 
            valid.idapplication, 
            version.version
        FROM valid
        INNER JOIN version ON valid.idversion = version.idversion
        WHERE valid.estvalid = 1
        ORDER BY valid.idvalid DESC
    ) AS valid ON application.idapplication = valid.idapplication
");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les utilisateurs pour la sélection dans la modale
$stmt = $conn->query("SELECT iduser, nep FROM user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des applications</title>
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
                        <a href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
                    </li>
                    <li>
                        <a class="active-menu" href="application.php"><i class="fa fa-users fa-3x"></i> Application</a>
                    </li>
                </ul>
            </div>
        </nav> 

        <!-- Contenu principal -->
        <div id="page-wrapper">
            <div id="page-inner">
                <h2>Gestion des applications</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addApplicationModal">
                    Ajouter une application
                </button>
                <br><br>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom de l'application</th>
                            <th>Description</th>
                            <th>Utilisateur responsable</th>
                            <th>Dernière version validée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <!-- ... (code précédent inchangé) ... -->
<tbody>
    <?php foreach ($applications as $app): ?>
        <tr>
            <td><?= htmlspecialchars($app['idapplication']) ?></td>
            <td><?= htmlspecialchars($app['nomapplication']) ?></td>
            <td><?= htmlspecialchars($app['description']) ?></td>
            <td><?= htmlspecialchars($app['nep']) ?></td>
            <td><?= htmlspecialchars($app['last_valid_version']) ?></td>
            <td>
                <a href="edit_Aapplication.php?id=<?= $app['idapplication'] ?>" class="btn btn-primary">Modifier</a>
                <a href="delete_application.php?id=<?= $app['idapplication'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette application ?');">Supprimer</a>
                <?php if (!empty($app['last_valid_version'])): ?>
                    <a href="download_version.php?id=<?= $app['idapplication'] ?>" class="btn btn-success">Télécharger</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
<!-- ... (code suivant inchangé) ... -->
                </table>
            </div>
        </div>
    </div>

    <!-- Modale pour ajouter une application -->
    <div class="modal fade" id="addApplicationModal" tabindex="-1" role="dialog" aria-labelledby="addApplicationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addApplicationModalLabel">Ajouter une application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addApplicationForm" action="add_Aapplication.php" method="POST">
                        <div class="form-group">
                            <label>Nom de l'application</label>
                            <input type="text" name="nomapplication" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Utilisateur responsable</label>
                            <select name="iduser" class="form-control" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['iduser'] ?>"><?= htmlspecialchars($user['nep']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" form="addApplicationForm" class="btn btn-primary">Enregistrer</button>
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