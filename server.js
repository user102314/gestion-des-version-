const express = require('express');
const mysql = require('mysql');
const fs = require('fs');
const path = require('path');
const mime = require('mime-types');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
app.use(bodyParser.json());
app.use(cors());

// Configuration de la connexion MySQL
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root', // Remplacez par votre utilisateur MySQL
  password: '', // Remplacez par votre mot de passe MySQL
  database: 'gdv1' // Remplacez par le nom de votre base de données
});

db.connect((err) => {
  if (err) throw err;
  console.log('Connecté à la base de données MySQL');
});

// Créer la table pour les logs si elle n'existe pas
const createLogsTable = `
CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(255) NOT NULL,
  date DATETIME NOT NULL,
  token VARCHAR(255) NOT NULL,
  vu ENUM('oui', 'faux') DEFAULT 'faux'
)`;
db.query(createLogsTable, (err) => {
  if (err) throw err;
  console.log('Table logs créée ou déjà existante');
});

// Route pour vérifier le token et récupérer les informations
app.post('/verify-token', (req, res) => {
  const { token } = req.body;

  // Vérifier le token et l'utilisateur
  const query = 'SELECT * FROM user WHERE token = ?';
  db.query(query, [token], (err, results) => {
    if (err) {
      console.error('Erreur lors de la vérification du token :', err);
      return res.status(500).json({ success: false, message: 'Erreur interne du serveur' });
    }

    if (!results || results.length === 0) {
      return res.status(401).json({ success: false, message: 'Token invalide' });
    }

    const iduser = results[0].iduser;

    // Récupérer les applications de l'utilisateur
    const appQuery = 'SELECT * FROM application WHERE iduser = ?';
    db.query(appQuery, [iduser], (err, apps) => {
      if (err) {
        console.error('Erreur lors de la récupération des applications :', err);
        return res.status(500).json({ success: false, message: 'Erreur interne du serveur' });
      }

      if (!apps || apps.length === 0) {
        return res.json({ success: true, data: [] }); // Aucune application trouvée
      }

      // Récupérer la dernière version validée de chaque application
      const versionQuery = `
        SELECT v.*, f.filepath
        FROM version v
        JOIN valid val ON v.idversion = val.idversion
        JOIN folder f ON v.idfolderp = f.idfolderp
        WHERE val.estvalid = 1 AND v.idapplication = ?
        ORDER BY v.idversion DESC
        LIMIT 1`;
      const appPromises = apps.map(app => {
        return new Promise((resolve, reject) => {
          db.query(versionQuery, [app.idapplication], (err, versions) => {
            if (err) {
              console.error('Erreur lors de la récupération des versions :', err);
              return reject(err);
            }

            if (!versions || versions.length === 0) {
              return resolve({ ...app, latestVersion: null, downloadLink: null });
            }

            const latestVersion = versions[0];
            const filePath = path.join(__dirname, latestVersion.filepath);

            console.log('Chemin du fichier :', filePath); // Log pour déboguer

            // Obtenir la taille et le type du fichier
            try {
              const stats = fs.statSync(filePath);
              const fileSize = stats.size;
              const fileType = mime.lookup(filePath) || 'application/octet-stream';

              const downloadLink = `http://localhost:3000/download/${encodeURIComponent(latestVersion.filepath)}?token=${token}`;
              resolve({
                ...app,
                latestVersion: {
                  ...latestVersion,
                  fileSize,
                  fileType
                },
                downloadLink
              });
            } catch (error) {
              console.error('Erreur lors de la lecture du fichier :', error);
              resolve({
                ...app,
                latestVersion: null,
                downloadLink: null,
                error: 'Fichier non trouvé sur le serveur'
              });
            }
          });
        });
      });

      Promise.all(appPromises)
        .then(appsWithVersions => {
          // Enregistrer les logs
          const ip = req.ip; // Récupérer l'adresse IP de la requête
          const date = new Date().toISOString().slice(0, 19).replace('T', ' ');

          const logQuery = 'INSERT INTO logs (ip, date, token) VALUES (?, ?, ?)';
          db.query(logQuery, [ip, date, token], (err) => {
            if (err) {
              console.error('Erreur lors de l\'enregistrement du log :', err);
            } else {
              console.log('Log enregistré');
            }
          });

          res.json({ success: true, data: appsWithVersions });
        })
        .catch(error => {
          console.error('Erreur lors de la récupération des versions :', error);
          res.status(500).json({ success: false, message: 'Erreur interne du serveur' });
        });
    });
  });
});

// Route pour télécharger un fichier
app.get('/download/:filename', (req, res) => {
  const filename = decodeURIComponent(req.params.filename); // Décoder l'URL
  const token = req.query.token; // Récupérer le token depuis les paramètres de la requête
  const filePath = path.join(__dirname, filename);

  console.log('Chemin du fichier pour téléchargement :', filePath); // Log pour déboguer

  // Vérifier si le fichier existe
  if (!fs.existsSync(filePath)) {
    console.error('Fichier non trouvé :', filePath);
    return res.status(404).json({ success: false, message: 'Fichier non trouvé' });
  }

  // Obtenir la taille et le type du fichier
  const stats = fs.statSync(filePath);
  const fileSize = stats.size;
  const fileType = mime.lookup(filePath) || 'application/octet-stream';

  // Définir les en-têtes pour le téléchargement
  res.setHeader('Content-Type', fileType);
  res.setHeader('Content-Length', fileSize);
  res.setHeader('Content-Disposition', `attachment; filename="${path.basename(filename)}"`);

  // Servir le fichier
  const fileStream = fs.createReadStream(filePath);
  fileStream.pipe(res);

  // Mettre à jour la colonne "vu" après avoir servi le fichier
  if (token) {
    const updateQuery = 'UPDATE logs SET vu = "oui" WHERE token = ?';
    db.query(updateQuery, [token], (err, results) => {
      if (err) {
        console.error('Erreur lors de la mise à jour de la colonne "vu" :', err);
      } else {
        console.log('Colonne "vu" mise à jour pour le token :', token);
      }
    });
  }
});

// Démarrer le serveur
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Serveur API démarré sur http://localhost:${PORT}`);
});