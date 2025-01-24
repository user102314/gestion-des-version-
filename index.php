<?php
session_start();
require 'db.php'; // Assurez-vous d'inclure votre fichier de connexion à la base de données

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$iduser = $_SESSION['iduser'];

// Récupérer les applications de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser");
$stmt->execute(['iduser' => $iduser]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la suppression d'une application
if (isset($_GET['delete'])) {
    $idapplication = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM application WHERE idapplication = :idapplication AND iduser = :iduser");
    $stmt->execute(['idapplication' => $idapplication, 'iduser' => $iduser]);
    header('Location: index.php'); // Recharger la page après suppression
    exit();
}


$iduser = $_SESSION['iduser'];
$emailUtilisateur = $_SESSION['email']; // Supposons que l'e-mail de l'utilisateur est stocké dans la session

// Récupérer la dernière date de la table `misajour` pour l'utilisateur actuel
$stmt = $conn->prepare("SELECT date FROM misajour WHERE nom = :email ORDER BY date DESC LIMIT 1");
$stmt->execute(['email' => $emailUtilisateur]);
$derniereDate = $stmt->fetchColumn();

// Formater la date pour l'affichage (si nécessaire)
if ($derniereDate) {
    $derniereDateFormatee = date('d M Y', strtotime($derniereDate)); // Exemple : "15 Oct 2023"
} else {
    $derniereDateFormatee = "Aucune date trouvée"; // Message par défaut si aucune date n'est trouvée
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GDV</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
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
                <a class="navbar-brand" href="index.html">Binary User</a> 
            </div>
            <div style="color: white;padding: 15px 50px 5px 50px;float: right;font-size: 16px;">
                Last access : <?php echo htmlspecialchars($derniereDateFormatee); ?> &nbsp; <a href="logout.php" class="btn btn-danger square-btn-adjust">Logout</a>
            </div>
        </nav>   
        <nav class="navbar-default navbar-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="main-menu">
            <li class="text-center">
                <img src="img/user.png" class="user-image img-responsive"/>
            </li>
            <li>
                <a class="active-menu" href="index.php"><i class="fa fa-tachometer fa-3x"></i> Dashboard</a>
            </li>
            <li>
                <a href="version.php"><i class="fa fa-code-fork fa-3x"></i> Version</a>
            </li>   
            <li>
                <a href="addversion.php"><i class="fa fa-plus-circle fa-3x"></i> Add Version</a>
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
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Developer Dashboard</h2>   
                        <h1>Bienvenue, <?php echo htmlspecialchars($email); ?> !</h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addAppModal">
                            Ajouter une application
                        </button>
                        <hr>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom de l'application</th>
                                    <th>Description</th>
                                    <th>Responsable</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['nomapplication']); ?></td>
                                        <td><?php echo htmlspecialchars($app['description']); ?></td>
                                        <td><?php echo htmlspecialchars($app['nomresponsable']); ?></td>
                                        <td>
                                            <a href="edit_application.php?id=<?php echo $app['idapplication']; ?>" class="btn btn-warning">Éditer</a>
                                            <a href="index.php?delete=<?php echo $app['idapplication']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette application ?');">Supprimer</a>
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

    <!-- Modal pour ajouter une application -->
    <div class="modal fade" id="addAppModal" tabindex="-1" role="dialog" aria-labelledby="addAppModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAppModalLabel">Ajouter une application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="add_application.php" method="POST">
                        <div class="form-group">
                            <label for="nomapplication">Nom de l'application</label>
                            <input type="text" class="form-control" id="nomapplication" name="nomapplication" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="nomresponsable">Responsable</label>
                            <input type="text" class="form-control" id="nomresponsable" name="nomresponsable" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>