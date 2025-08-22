<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

// Solo PA o admin
$stmt = $pdo->prepare("SELECT ruolo FROM utenti WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ruolo = $stmt->fetchColumn();

if (!in_array($ruolo, ['pa', 'admin'])) die("Accesso riservato.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $embed_url = $_POST['embed_url'];
    $data_evento = $_POST['data_evento'];

    $stmt = $pdo->prepare("INSERT INTO dirette (titolo, descrizione, embed_url, data_evento)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$titolo, $descrizione, $embed_url, $data_evento]);

    header("Location: elenco_dirette.php");
    exit;
}
?>
<!-- Form per programmare una nuova diretta con data -->