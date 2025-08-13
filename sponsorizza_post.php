<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utente_id = $_SESSION['user_id'];
    $comune_id = $_POST['comune_id'];
    $contenuto = $_POST['contenuto'];
    $ambito = $_POST['ambito'];
    $scadenza = date('Y-m-d H:i:s', strtotime('+1 day'));

    if ($ambito === 'provincia') {
        $stmt = $pdo->prepare("SELECT provincia FROM comuni WHERE id = ?");
        $stmt->execute([$comune_id]);
        $target = "provincia:" . $stmt->fetchColumn();
    } elseif ($ambito === 'regione') {
        $stmt = $pdo->prepare("SELECT regione FROM comuni WHERE id = ?");
        $stmt->execute([$comune_id]);
        $target = "regione:" . $stmt->fetchColumn();
    } elseif ($ambito === 'italia') {
        $target = "italia";
    } else {
        $target = "comune:$comune_id";
    }

    $stmt = $pdo->prepare("INSERT INTO post_sponsorizzati (utente_id, comune_id, contenuto, scadenza, target_area)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$utente_id, $comune_id, $contenuto, $scadenza, $target]);

    header("Location: dashboard_commerciale.php");
    exit;
}
?>