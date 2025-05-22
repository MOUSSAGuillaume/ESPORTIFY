# 🎮 Esportify - Plateforme de gestion d’événements e-sport

Le projet est déployé ici 👉 [https://esportify.monsite.com](https://esportify.alwaysdata.net/)

**Esportify** est une plateforme web permettant de créer, gérer et participer à des événements e-sport.  
Elle prend en charge plusieurs types d’utilisateurs (visiteurs, joueurs, organisateurs, administrateurs) et intègre :

- 🔐 Un système de rôles et permissions (RBAC)
- 📰 Une gestion de newsletters avec commentaires et likes
- 📧 Un envoi sécurisé des e-mails via PHPMailer

---

- [🧰 Technologies utilisées](#-technologies-utilisées)
- [📦 Prérequis](#-prérequis)
- [🚀 Installation](#-installation)
- [💡 Dépendances supplémentaires](#-dépendances-supplémentaires)
- [🛠 Fonctionnement des fichiers](#-fonctionnement-des-fichiers)
- [📁 Structure des fichiers](#-structure-des-fichiers)
- [🔒 Sécurité](#-sécurité)
- [🤝 Contributions](#-contributions)
- [📄 Licence](#-licence)

---

## ⚙️ Fonctionnalités principales

- Système d'authentification et de gestion des rôles (RBAC)
- Création, modification, validation d'événements par les organisateurs/admins
- Gestion des newsletters avec likes et commentaires
- Formulaire de contact avec envoi d'e-mails via PHPMailer
- Interface d'administration pour la gestion des utilisateurs

---

## 🧰 Technologies utilisées

- **PHP** (sans framework)
- **MySQL**
- **HTML / CSS / JavaScript**
- **Bootstrap** (responsive design)
- **PHPMailer**

---

## 📦 Prérequis

- PHP ≥ 7.4
- [Composer](https://getcomposer.org/)
- Serveur local (XAMPP, Laragon, etc.)
- (Optionnel) Node.js + npm pour Bootstrap localement

---

## 🚀 Installation

```bash
git clone https://github.com/ton-pseudo/esportify.git
cd esportify
composer install
cp ESPORTIFY.env.example ESPORTIFY.env
```

Configurer ensuite le fichier `.env` :

```env
# Pour send_contact.php
SMTP_USER=ton_email_smtp@gmail.com
SMTP_PASS=ton_mot_de_passe_app
MAIL_RECEIVER=destinataire@gmail.com

# Pour change_email_request.php, reset_pass_request.php, signup.php
SMTP_USER2=deuxieme_email_smtp@gmail.com
SMTP_PASS2=mot_de_passe_app_du_2e
```

> ✅ Utilise des **mots de passe d'application Gmail**, pas ton mot de passe principal. 
> Pour cela, active l’**authentification à deux facteurs** dans ton compte Google puis génère un **mot de passe d’application**.

---

## 💡 Dépendances supplémentaires

### Bootstrap (optionnel)

Si tu ne veux pas utiliser le CDN :

```bash
npm init -y
npm install bootstrap
```

Puis, dans ton HTML :

```html
<link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
<script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
```

### reCAPTCHA (Google)

1. Va sur [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin)
2. Choisis reCAPTCHA v2 ("Je ne suis pas un robot")
3. Récupère la clé de **site** et la clé **secrète**
4. Dans le HTML :

```html
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="g-recaptcha" data-sitekey="VOTRE_CLÉ_SITE"></div>
```

5. Dans `send_contact.php` :

```php
$recaptchaResponse = $_POST['g-recaptcha-response'];
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=VOTRE_CLÉ_SECRÈTE&response=$recaptchaResponse");
$responseKeys = json_decode($response, true);
if(!$responseKeys["success"]) exit('Vérification reCAPTCHA échouée');
```

---

## 🛠 Fonctionnement des fichiers

| Fichier PHP                | Variables .env utilisées               |
| -------------------------- | -------------------------------------- |
| send_contact.php           | SMTP_USER, SMTP_PASS, MAIL_RECEIVER    |
| change_email_request.php   | SMTP_USER2, SMTP_PASS2                 |
| reset_pass_request.php     | SMTP_USER2, SMTP_PASS2                 |
| signup.php                 | SMTP_USER2, SMTP_PASS2                 |

---

## 📁 Structure des fichiers

```bash
/frontend/               # Pages visibles par les utilisateurs
/backend/                # Logique PHP (authentification, formulaires...)
/backend/auth_check.php  # Vérification des rôles utilisateur
/backend/permission.php  # Affichage conditionnel selon les permissions
/backend/send_contact.php# Envoi du formulaire de contact
/db.php                  # Connexion à la base de données
```

---

## 🔒 Sécurité

- Le fichier `.env` ne doit **jamais** être versionné → ajoute-le dans `.gitignore`
- Les mots de passe ne doivent **jamais apparaître en clair** dans le code
- L'accès aux pages sensibles est protégé par vérification de session/rôle
- Utilisation de Google reCAPTCHA + PHPMailer pour lutter contre le spam
- Utilisation recommandée de mots de passe d'application pour Gmail

---

## 🤝 Contributions

Ce projet a été développé dans le cadre d'un **ECF** individuel.
Les contributions externes ne sont pas recherchées activement, mais les retours sont bienvenus via **GitHub Issues**.

---

## 📄 Licence

Ce projet est réservé à un usage personnel et pédagogique uniquement.  
Toute utilisation commerciale est interdite sans autorisation.  
**© 2025 - Tous droits réservés.**
