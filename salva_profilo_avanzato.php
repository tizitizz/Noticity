<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

$bio = $_POST['bio'] ?? '';
$social = $_POST['social'] ?? '';
$copertina = null;

if (!empty($_FILES['copertina']['name'])) {
    $target_dir = "uploads/";
    $filename = time() . "_" . basename($_FILES["copertina"]["name"]);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES["copertina"]["tmp_name"], $target_file)) {
        $copertina = $target_file;
    }
}

if ($copertina) {
    $stmt = $pdo->prepare("UPDATE utenti SET bio = ?, social = ?, copertina = ? WHERE id = ?");
    $stmt->execute([$bio, $social, $copertina, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("UPDATE utenti SET bio = ?, social = ? WHERE id = ?");
    $stmt->execute([$bio, $social, $_SESSION['user_id']]);
}

header("Location: profilo_utente.php");
exit;
?>