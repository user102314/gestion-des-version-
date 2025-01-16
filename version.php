<?php
session_start();
require 'db.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$iduser = $_SESSION['iduser'];

// Récupérer l'application de l'utilisateur (supposons qu'il n'en a qu'une seule)
$stmt = $conn->prepare("SELECT * FROM application WHERE iduser = :iduser LIMIT 1");
$stmt->execute(['iduser' => $iduser]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

$versions = [];
$lastValidVersion = null; // Variable pour stocker la dernière version validée

if ($application) {
    $selectedAppId = $application['idapplication'];

    // Récupérer les versions de l'application
    $stmt = $conn->prepare("
        SELECT v.*, f.filename, f.filepath, val.nom AS validateur, val.commentaire, val.estvalid 
        FROM version v 
        JOIN folder f ON v.idfolderp = f.idfolderp 
        LEFT JOIN valid val ON v.idversion = val.idversion AND v.idapplication = val.idapplication
        WHERE v.idapplication = :idapplication
    ");
    $stmt->execute(['idapplication' => $selectedAppId]);
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer la dernière version validée
    $stmt = $conn->prepare("
        SELECT v.*, f.filename, f.filepath 
        FROM version v 
        JOIN folder f ON v.idfolderp = f.idfolderp 
        JOIN valid val ON v.idversion = val.idversion AND v.idapplication = val.idapplication
        WHERE v.idapplication = :idapplication AND val.estvalid = 1
        ORDER BY v.idversion DESC
        LIMIT 1
    ");
    $stmt->execute(['idapplication' => $selectedAppId]);
    $lastValidVersion = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate'])) {
    $nom = $_POST['nom'];
    $commentaire = $_POST['commentaire'];
    $idversion = $_POST['idversion'];
    $idapplication = $_POST['idapplication'];
    $estvalid = isset($_POST['estvalid']) ? 1 : 0; // 1 pour true, 0 pour false

    // Si le champ "nom" est vide, utiliser l'email de l'utilisateur
    if (empty($nom)) {
        $nom = explode('@', $email)[0]; // Prendre la partie avant le @ de l'email
    }

    // Si le champ "commentaire" est vide, utiliser "Validé" par défaut
    if (empty($commentaire)) {
        $commentaire = "Validé";
    }

    // Insérer la validation dans la base de données
    $stmt = $conn->prepare("INSERT INTO valid (nom, commentaire, idversion, idapplication, estvalid) VALUES (:nom, :commentaire, :idversion, :idapplication, :estvalid)");
    $stmt->execute([
        'nom' => $nom,
        'commentaire' => $commentaire,
        'idversion' => $idversion,
        'idapplication' => $idapplication,
        'estvalid' => $estvalid
    ]);

    // Rediriger pour éviter la soumission multiple du formulaire
    header('Location: version.php');
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
    <title>GDV - Versions</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
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
                <a class="navbar-brand" href="index.php">Binary User</a> 
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
                        <a href="index.php"><i class="fa fa-table fa-3x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a class="active-menu" href="version.php"><i class="fa fa-desktop fa-3x"></i> Version</a>
                    </li>  
                    <li>
                        <a href="addversion.php"><i class="fa fa-desktop fa-3x"></i>Add Version</a>
                    </li>  
                    <li>
                        <a href="logout.php"><i class="fa fa-desktop fa-3x"></i> Se déconnecter</a>
                    </li>
                </ul>
            </div>
        </nav>  

        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Versions de l'application</h2>
                        <hr>
                    </div>
                </div>

                <!-- Bloc 1 : Détails de l'application -->
                <?php if ($application): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Application : <?php echo htmlspecialchars($application['nomapplication']); ?></h3>
                        </div>
                    </div>

                    <!-- Bloc 2 : Dernière version validée -->
                    <div>
                        <h3>Dernière version validée</h3>
                        <?php if ($lastValidVersion): ?>
                            <p>
                                La dernière version validée est : <strong><?php echo htmlspecialchars($lastValidVersion['version']); ?></strong>
                            </p>
                            <a href="<?php echo htmlspecialchars($lastValidVersion['filepath']); ?>" class="btn btn-primary" download>
                                <i class="fa fa-download"></i> Télécharger la dernière version validée
                            </a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Aucune version validée disponible pour cette application.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bloc 3 : Liste des versions -->
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Versions</h3>
                            <?php if (count($versions) > 0): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Version</th>
                                            <th>Fichier</th>
                                            <th>Télécharger</th>
                                            <th>Validé ou non</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($versions as $version): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($version['version']); ?></td>
                                                <td><?php echo htmlspecialchars($version['filename']); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($version['filepath']); ?>" class="btn btn-primary" download>
                                                        <i class="fa fa-download"></i> Télécharger
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if (isset($version['estvalid'])): ?>
                                                        <?php if ($version['estvalid'] == 1): ?>
                                                            <span style="color: green;">
                                                                Validé par <?php echo htmlspecialchars($version['validateur']); ?> : <?php echo htmlspecialchars($version['commentaire']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="color: red;">
                                                                Non validé par <?php echo htmlspecialchars($version['validateur']); ?> : <?php echo htmlspecialchars($version['commentaire']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span style="color: gray;">Aucun superviseur</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-success validate-btn" data-idversion="<?php echo $version['idversion']; ?>" data-idapplication="<?php echo $version['idapplication']; ?>">
                                                        Valider
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    Aucune version disponible pour cette application.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Aucune application trouvée pour cet utilisateur.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour la validation -->
    <div class="modal fade" id="validateModal" tabindex="-1" role="dialog" aria-labelledby="validateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="validateModalLabel">Valider la version</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="validateForm" method="POST" action="version.php">
                        <input type="hidden" name="idversion" id="idversion">
                        <input type="hidden" name="idapplication" id="idapplication">
                        <div class="form-group">
                            <label for="nom">C'est qui vous ?</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="commentaire">Tu veux laisser un commentaire ?</label>
                            <textarea class="form-control" id="commentaire" name="commentaire"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="estvalid">Est validé ?</label>
                            <input type="checkbox" id="estvalid" name="estvalid">
                        </div>
                        <button type="submit" name="validate" class="btn btn-primary">Valider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery et Bootstrap JS -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Détecter le clic sur le bouton "Valider"
        $('.validate-btn').on('click', function() {
            const idversion = $(this).data('idversion');
            const idapplication = $(this).data('idapplication');
            $('#idversion').val(idversion);
            $('#idapplication').val(idapplication);

            // Remplir les champs par défaut
            const email = "<?php echo $_SESSION['email']; ?>"; // Récupérer l'email de l'utilisateur depuis la session
            const nomParDefaut = email.split('@')[0]; // Extraire la partie avant le @ de l'email
            const commentaireParDefaut = "Validé"; // Commentaire par défaut

            // Remplir le champ "nom" si vide
            if ($('#nom').val().trim() === '') {
                $('#nom').val(nomParDefaut);
            }

            // Remplir le champ "commentaire" si vide
            if ($('#commentaire').val().trim() === '') {
                $('#commentaire').val(commentaireParDefaut);
            }

            // Afficher la modal
            $('#validateModal').modal('show');
        });
    });
</script>
   
    
</body>
</html>