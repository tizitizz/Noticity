<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT comune_id FROM utenti WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch();

$comune_id = $row ? $row['comune_id'] : 0;

header("Location: index.php?comune=" . intval($comune_id));
exit;
?>