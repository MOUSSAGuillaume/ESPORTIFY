from flask import Flask, session, redirect, url_for

app = Flask(__name__)
app.secret_key = 'supersecretkey'  # Utilisé pour sécuriser les sessions

@app.route('/')
def home():
    if 'user_role' not in session:
        return redirect(url_for('login'))

    user_role = session['user_role']  # Récupérer le rôle de l'utilisateur depuis la session

    if user_role == "Admin":
        return show_admin_dashboard()
    elif user_role == "Organisateur":
        return show_event_management_page()
    elif user_role == "Utilisateur":
        return show_event_list()
    else:
        return show_event_list_view_only()

def show_admin_dashboard():
    return "Tableau de bord Admin"

def show_event_management_page():
    return "Page de gestion des événements"

def show_event_list():
    return "Liste des événements"

def show_event_list_view_only():
    return "Vue des événements (lecture seule)"

@app.route('/login')
def login():
    # Logique de connexion
    # Exemple : après une connexion réussie, on stocke le rôle dans la session
    session['user_role'] = 'Admin'  # Exemple d'un utilisateur Admin
    return redirect(url_for('home'))

if __name__ == '__main__':
    app.run(debug=True)
