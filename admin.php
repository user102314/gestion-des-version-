<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Récupérer les données pour le graphique
$stmt = $conn->query("
    SELECT DATE(date) AS date, COUNT(*) AS count 
    FROM misajour 
    GROUP BY DATE(date) 
    ORDER BY date ASC
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formater les données pour Morris.js
$morrisData = [];
foreach ($data as $row) {
    $morrisData[] = [
        'date' => $row['date'],
        'count' => (int)$row['count']
    ];
}
$morrisData = json_encode($morrisData);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GDV - Dashboard</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
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
                        <a class="active-menu" href="admin.php"><i class="fa fa-table fa-3x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="users.php"><i class="fa fa-users fa-3x"></i> Utilisateurs</a>
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
                <h2>Dashboard</h2>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Statistiques des mises à jour
                            </div>
                            <div class="panel-body">
                                <div id="morris-line-chart"></div>
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
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    <script>
        // Données pour Morris.js
        const morrisData = <?= $morrisData ?>;

        // Créer le graphique en courbes
        Morris.Line({
            element: 'morris-line-chart',
            data: morrisData,
            xkey: 'date',
            ykeys: ['count'],
            labels: ['Nombre de mises à jour'],
            parseTime: false,
            xLabels: 'day',
            resize: true,
            lineColors: ['#1ab394'],
            pointFillColors: ['#1ab394'],
            pointStrokeColors: ['#1ab394'],
            gridTextColor: '#999',
            gridTextWeight: 'bold',
            hideHover: 'auto'
        });
    </script>
    <script src="assets/js/custom.js"></script>
</body>
</html>