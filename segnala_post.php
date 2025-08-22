<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Inserisci segnalazione
$stmt = $pdo->prepare("INSERT INTO segnalazioni (post_id, utente_id) VALUES (?, ?)");
$stmt->execute([$post_id, $user_id]);

header("Location: index.php?msg=Post segnalato");
exit;
?>
