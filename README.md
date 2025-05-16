# ecf# 📬 Esportify - Gestion des Emails avec PHPMailer

Ce projet utilise **PHPMailer** pour gérer l'envoi d'emails (formulaire de contact, confirmation d'inscription, changement d'email, réinitialisation de mot de passe...).

## 📦 Prérequis

* PHP ≥ 7.4
* [Composer](https://getcomposer.org/)
* Un serveur local (ex : XAMPP, Laragon...)

---

## 🚀 Installation

1. Clone ce repo :

   ```bash
   git clone https://github.com/ton-pseudo/esportify.git
   cd esportify
   ```

2. Installe les dépendances PHP :

   ```bash
   composer install
   ```

3. Copie le fichier `.env.example` pour créer ton fichier `.env` personnel :

   ```bash
   cp ESPORTIFY.env.example ESPORTIFY.env
   ```

4. Ouvre `ESPORTIFY.env` et configure les variables :

   ```env
   # Pour send_contact.php
   SMTP_USER=ton_email_smtp@gmail.com
   SMTP_PASS=ton_mot_de_passe_app
   MAIL_RECEIVER=destinataire@gmail.com

   # Pour change_email_request.php, reset_pass_request.php, signup.php
   SMTP_USER2=deuxieme_email_smtp@gmail.com
   SMTP_PASS2=mot_de_passe_app_du_2e
   ```

> 💡 Utilise des [mots de passe d'application Gmail](https://support.google.com/mail/answer/185833?hl=fr) et non ton mot de passe classique Gmail.

---

## 🛠 Fonctionnement des fichiers

| Fichier PHP                | Variables utilisées dans `.env`           |
| -------------------------- | ----------------------------------------- |
| `send_contact.php`         | `SMTP_USER`, `SMTP_PASS`, `MAIL_RECEIVER` |
| `change_email_request.php` | `SMTP_USER2`, `SMTP_PASS2`                |
| `reset_pass_request.php`   | `SMTP_USER2`, `SMTP_PASS2`                |
| `signup.php`               | `SMTP_USER2`, `SMTP_PASS2`                |

---

## 🔒 Sécurité

* Ne versionnez **jamais** le fichier `.env`. Il doit être dans le `.gitignore`.
* Ne mettez **jamais** de mot de passe ou d'adresse email réelle dans le code ou le `.env.example`.

---

## 🤝 Contributions

Les contributions sont les bienvenues ! Crée une PR ou ouvre une issue.

---

## 📄 Licence

MIT - Utilise librement ce code dans ton propre projet.
