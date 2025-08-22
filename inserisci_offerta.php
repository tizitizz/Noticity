<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comune_id = $_POST['comune_id'];
    $contenuto = $_POST['contenuto'];
    $scadenza = date('Y-m-d H:i:s', strtotime('+1 day'));

    $stmt = $pdo->prepare("INSERT INTO post_sponsorizzati (comune_id, contenuto, scadenza)
                           VALUES (?, ?, ?)");
    $stmt->execute([$comune_id, $contenuto, $scadenza]);

    header("Location: comune.php?nome=" . urlencode($_POST['comune_nome']));
    exit;
}
?>