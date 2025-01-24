const express = require('express');
const mysql = require('mysql');
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
  nom VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL
)`;
db.query(createLogsTable, (err) => {
  if (err) throw err;
  console.log('Table logs créée ou déjà existante');
});

// Route pour vérifier le token et récupérer les informations
app.post('/verify-token', (req, res) => {
  const { token, nom } = req.body;

  // Vérifier le token et l'utilisateur
  const query = 'SELECT * FROM user WHERE token = ? AND nep = ?';
  db.query(query, [token, nom], (err, results) => {
    if (err) throw err;

    if (results.length > 0) {
      const iduser = results[0].iduser;

      // Récupérer les applications de l'utilisateur
      const appQuery = 'SELECT * FROM application WHERE iduser = ?';
      db.query(appQuery, [iduser], (err, apps) => {
        if (err) throw err;

        // Récupérer la dernière version validée de chaque application
        const versionQuery = `
          SELECT v.* 
          FROM version v
          JOIN valid val ON v.idversion = val.idversion
          WHERE val.estvalid = 1 AND v.idapplication = ?
          ORDER BY v.idversion DESC
          LIMIT 1`;
        const appPromises = apps.map(app => {
          return new Promise((resolve, reject) => {
            db.query(versionQuery, [app.idapplication], (err, versions) => {
              if (err) reject(err);
              resolve({ ...app, latestVersion: versions[0] });
            });
          });
        });

        Promise.all(appPromises).then(appsWithVersions => {
          res.json({ success: true, data: appsWithVersions });
        });
      });
    } else {
      res.status(401).json({ success: false, message: 'Token ou nom invalide' });
    }
  });
});

// Route pour enregistrer les logs
app.post('/log', (req, res) => {
  const { ip, date, nom, token } = req.body;

  const query = 'INSERT INTO logs (ip, date, nom, token) VALUES (?, ?, ?, ?)';
  db.query(query, [ip, date, nom, token], (err, results) => {
    if (err) throw err;
    res.json({ success: true, message: 'Log enregistré' });
  });
});

// Démarrer le serveur
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Serveur API démarré sur http://localhost:${PORT}`);
});