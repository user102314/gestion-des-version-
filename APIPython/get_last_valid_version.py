from db import get_db_connection
from datetime import datetime
from passlib.hash import bcrypt  # Utiliser passlib pour vérifier les mots de passe hashés

def get_last_valid_version(email, mdp):
    # Connexion à la base de données
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # Récupérer l'utilisateur depuis la base de données
        cursor.execute("SELECT * FROM user WHERE email = %s", (email,))
        user = cursor.fetchone()

        # Vérifier si l'utilisateur existe
        if not user:
            print("Utilisateur non trouvé.")
            return

        # Vérifier si le mot de passe est correct
        if not bcrypt.verify(mdp, user['mdp']):  # Utiliser bcrypt pour vérifier le mot de passe
            print("Mot de passe incorrect.")
            return

        # Vérifier si le token existe et est valide (date du token n'a pas dépassé 30 jours)
        if not user['token']:
            print("Aucun token trouvé pour cet utilisateur.")
            return

        date_token = user['date_token']  # Utiliser directement l'objet datetime.date
        now = datetime.now()  # Date actuelle
        delta = (now - date_token).days  # Différence en jours

        if delta > 30:
            print("Le token a expiré.")
            return

        # Récupérer l'application associée à l'utilisateur
        cursor.execute("SELECT * FROM application WHERE iduser = %s", (user['iduser'],))
        application = cursor.fetchone()

        # Vérifier si l'application existe
        if not application:
            print("Aucune application trouvée pour cet utilisateur.")
            return

        # Récupérer la dernière version validée de l'application
        cursor.execute("""
            SELECT v.*, f.filename, f.filepath 
            FROM version v 
            JOIN folder f ON v.idfolderp = f.idfolderp 
            JOIN valid val ON v.idversion = val.idversion 
            WHERE v.idapplication = %s AND val.estvalid = 1 
            ORDER BY v.idversion DESC 
            LIMIT 1
        """, (application['idapplication'],))
        last_valid_version = cursor.fetchone()

        # Vérifier si une version validée existe
        if not last_valid_version:
            print("Aucune version validée trouvée pour cette application.")
            return

        # Afficher la dernière version validée
        print("Dernière version validée :")
        print(f"Version: {last_valid_version['version']}")
        print(f"Fichier: {last_valid_version['filename']}")
        print(f"Chemin: {last_valid_version['filepath']}")

    except Exception as e:
        print(f"Erreur : {e}")

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    # Demander l'email et le mot de passe à l'utilisateur
    email = input("Entrez votre email : ")
    mdp = input("Entrez votre mot de passe : ")

    # Appeler la fonction pour récupérer la dernière version validée
    get_last_valid_version(email, mdp)