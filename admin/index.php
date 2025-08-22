<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

// Verifica ruolo admin
$stmt = $pdo->prepare("SELECT ruolo FROM utenti WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ruolo = $stmt->fetchColumn();

if ($ruolo !== 'admin') die("Accesso riservato agli amministratori.");
?>
<h2>Pannello Admin</h2>
<ul>
  <li><a href="utenti.php">Gestione Utenti</a></li>
  <li><a href="post.php">Gestione Post</a></li>
  <li><a href="comuni.php">Gestione Comuni</a></li>
  <li><a href="attivita.php">Gestione Attività Commerciali</a></li>
</ul>
