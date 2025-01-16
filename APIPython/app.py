from flask import Flask, request, jsonify
from db import get_db_connection
from datetime import datetime

app = Flask(__name__)

@app.route('/check_token_and_get_version', methods=['POST'])
def check_token_and_get_version():
    # Récupérer les données JSON envoyées
    data = request.get_json()

    email = data.get('email')
    mdp = data.get('mdp')

    # Vérifier si l'email et le mot de passe sont fournis
    if not email or not mdp:
        return jsonify({'error': 'Email et mot de passe sont requis.'}), 400

    # Connexion à la base de données
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # Récupérer l'utilisateur depuis la base de données
        cursor.execute("SELECT * FROM user WHERE email = %s", (email,))
        user = cursor.fetchone()

        # Vérifier si l'utilisateur existe
        if not user:
            return jsonify({'error': 'Utilisateur non trouvé.'}), 404

        # Vérifier si le mot de passe est correct
        if not user['mdp'] == mdp:  # Remplacez par password_verify si vous utilisez des mots de passe hashés
            return jsonify({'error': 'Mot de passe incorrect.'}), 401

        # Vérifier si le token existe et est valide (date du token n'a pas dépassé 30 jours)
        if not user['token']:
            return jsonify({'error': 'Aucun token trouvé pour cet utilisateur.'}), 403

        date_token = datetime.strptime(user['date_token'], '%Y-%m-%d')  # Convertir la date du token
        now = datetime.now()  # Date actuelle
        delta = (now - date_token).days  # Différence en jours

        if delta > 30:
            return jsonify({'error': 'Le token a expiré.'}), 403

        # Récupérer l'application associée à l'utilisateur
        cursor.execute("SELECT * FROM application WHERE iduser = %s", (user['iduser'],))
        application = cursor.fetchone()

        # Vérifier si l'application existe
        if not application:
            return jsonify({'error': 'Aucune application trouvée pour cet utilisateur.'}), 404

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
            return jsonify({'error': 'Aucune version validée trouvée pour cette application.'}), 404

        # Retourner la dernière version validée
        return jsonify({
            'application': {
                'idapplication': application['idapplication'],
                'nomapplication': application['nomapplication'],
                'description': application['description']
            },
            'last_valid_version': last_valid_version
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    app.run(debug=True)