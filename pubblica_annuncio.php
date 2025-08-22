<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $comune_id = $_POST['comune_id'];
    $utente_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO annunci (titolo, descrizione, comune_id, utente_id)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$titolo, $descrizione, $comune_id, $utente_id]);

    header("Location: annunci.php");
    exit;
}
?>