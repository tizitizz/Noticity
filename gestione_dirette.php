<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $embed_url = $_POST['embed_url'];

    $stmt = $pdo->prepare("INSERT INTO dirette (titolo, descrizione, embed_url, data_creazione)
                           VALUES (?, ?, ?, NOW())");
    $stmt->execute([$titolo, $descrizione, $embed_url]);

    header("Location: diretta.php?id=" . $pdo->lastInsertId());
    exit;
}
?>
<!-- Form HTML per inserire nuova diretta -->