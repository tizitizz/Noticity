<?php
session_start();
require 'config.php';

$sondaggio_id = $_POST['sondaggio_id'];
$scelta = $_POST['scelta'];
$utente_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("REPLACE INTO voti_sondaggi (sondaggio_id, utente_id, scelta, data_voto)
                       VALUES (?, ?, ?, NOW())");
$stmt->execute([$sondaggio_id, $utente_id, $scelta]);

header("Location: sondaggi.php");
exit;
?>