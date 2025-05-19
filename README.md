## 📚 Sommaire (fait un Ctrl clickdroit sur le lien sur vscode)
- [⚙️ Fonctionnalités principales](#️-fonctionnalités-principales)
- [🧰 Technologies utilisées](#-technologies-utilisées)
- [📦 Prérequis](#-prérequis)
- [🚀 Installation](#-installation)
- [🛠 Fonctionnement des fichiers](#-fonctionnement-des-fichiers)
- [📁 Structure des fichiers](#-structure-des-fichiers)
- [🔒 Sécurité](#-sécurité)
- [🤝 Contributions](#-contributions)
- [📄 Licence](#-licence)


# 🎮 Esportify - Plateforme de gestion d’événements e-sport
## 🔗 Démo en ligne
Le projet est déployé ici 👉 [https://esportify.monsite.com](https://esportify.alwaysdata.net/)

**Esportify** est une plateforme web qui permet de créer, gérer et participer à des événements e-sport.  
Elle prend en charge plusieurs types d’utilisateurs (visiteurs, joueurs, organisateurs, administrateurs) et intègre :
- 🔐 Un système de sécurité basé sur les rôles (RBAC)
- 📬 Une gestion des newsletters avec commentaires et likes
- 📧 Un envoi sécurisé des emails via PHPMailer
---
## ⚙️ Fonctionnalités principales
- Authentification et gestion des rôles (RBAC)
- Création, modification et validation d’événements
- Système de newsletters avec interaction (likes et commentaires)
- Envoi d’emails (formulaire de contact, confirmation d’inscription, etc.)
- Gestion des utilisateurs par les administrateurs
---
## 🧰 Technologies utilisées
- PHP (sans framework)
- MySQL
- HTML / CSS / JavaScript
- Bootstrap (responsive design)
- PHPMailer
---
## 📦 Prérequis

- PHP ≥ 7.4
- [Composer](https://getcomposer.org/)
- Un serveur local (ex : XAMPP, Laragon...)
---
## 🚀 Installation
```bash
git clone https://github.com/ton-pseudo/esportify.git
cd esportify
composer install
cp ESPORTIFY.env.example ESPORTIFY.env
```
Puis configure le fichier `ESPORTIFY.env` :
```env
# Pour send_contact.php
SMTP_USER=ton_email_smtp@gmail.com
SMTP_PASS=ton_mot_de_passe_app
MAIL_RECEIVER=destinataire@gmail.com

# Pour change_email_request.php, reset_pass_request.php, signup.php
SMTP_USER2=deuxieme_email_smtp@gmail.com
SMTP_PASS2=mot_de_passe_app_du_2e
```
> 🔐 Utilise des [mots de passe d'application Gmail](https://support.google.com/mail/answer/185833?hl=fr) (et non ton mot de passe principal).
---
## 🛠 Fonctionnement des fichiers
| Fichier PHP                | Variables `.env` utilisées                |
| -------------------------- | ---------------------------------------- |
| `send_contact.php`         | `SMTP_USER`, `SMTP_PASS`, `MAIL_RECEIVER`|
| `change_email_request.php` | `SMTP_USER2`, `SMTP_PASS2`               |
| `reset_pass_request.php`   | `SMTP_USER2`, `SMTP_PASS2`               |
| `signup.php`               | `SMTP_USER2`, `SMTP_PASS2`               |
---

## 📁 Structure des fichiers

```
/frontend/         → Pages visibles par les utilisateurs
/backend/          → Traitement PHP (login, inscription, événements…)
/db.php            → Connexion à la base de données (dans le dossier racine)
/backend/auth_check.php → Vérification des rôles utilisateur
/backend/permission.php → Affichage conditionnel selon les rôles
/backend/send_contact.php → Envoi d’email via formulaire de contact
```
---
## 🔒 Sécurité
- Le fichier `.env` **ne doit jamais** être versionné → ajoutez-le dans `.gitignore`.
- Aucune information sensible (mot de passe réel, email) ne doit figurer en clair dans le code ou les exemples.
- Les rôles sont vérifiés en session et les redirections sont automatiques si accès non autorisé.
- Les composants sont affichés ou masqués dynamiquement selon les rôles.
- Envoi sécurisé d'emails via PHPMailer avec mots de passe d'application Gmail.
---
## 🤝 Contributions
Ce projet a été développé individuellement dans le cadre d’un ECF (Évaluation des Compétences en cours de Formation).
Les contributions extérieures ne sont pas activement recherchées, mais les retours sont bienvenus via GitHub Issues.
---
## 📄 Licence
Ce projet est réservé à un usage personnel et pédagogique uniquement.  
Toute utilisation commerciale est interdite sans autorisation.  
**© 2025 - Tous droits réservés.**
