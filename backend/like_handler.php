<?php
include_once("../db.php");

if (isset($_POST['like_actualite'])) {
  $id_actu = (int) $_POST['id_actualite_like'];
  $id_joueur = $_SESSION['user']['id'];

  // Vérifie si déjà liké
  $check = mysqli_query($conn, "SELECT * FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur");
  if (mysqli_num_rows($check) == 0) {
      mysqli_query($conn, "INSERT INTO likes_actualites (id_actualite, id_joueur) VALUES ($id_actu, $id_joueur)");
  } else {
      // Si déjà liké, on supprime (toggle like)
      mysqli_query($conn, "DELETE FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur");
  }
}
?>
