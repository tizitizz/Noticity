<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Accesso negato.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $target_dir = "uploads/";
    $filename = basename($_FILES["foto"]["name"]);
    $target_file = $target_dir . time() . "_" . $filename;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("UPDATE utenti SET foto_profilo = ? WHERE id = ?");
        $stmt->execute([$target_file, $_SESSION['user_id']]);
        header("Location: profilo_utente.php");
        exit;
    } else {
        echo "Errore nel caricamento della foto.";
    }
}
?>