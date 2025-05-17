<?php

function checkCsrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("❌ Erreur CSRF : Token invalide.");
    }
}
