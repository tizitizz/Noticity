<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Accesso negato");
}

$tipo = $_POST['tipo'] ?? '';
$contenuto = $_POST['contenuto'] ?? '';
$comune_id = $_POST['comune_id'] ?? 0;

$stmt = $pdo->prepare("INSERT INTO post (utente_id, comune_id, tipo, contenuto, data_creazione) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$_SESSION['user_id'], $comune_id, $tipo, $contenuto]);

header("Location: comune.php?nome=" . urlencode($_GET['nome']));
exit;
?>