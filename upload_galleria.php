<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

$attivita_id = $_POST['attivita_id'];

if (!empty($_FILES['immagine']['name'])) {
    $target_dir = "uploads/";
    $filename = time() . "_" . basename($_FILES["immagine"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO galleria (attivita_id, immagine) VALUES (?, ?)");
        $stmt->execute([$attivita_id, $target_file]);
        header("Location: pagina_attivita.php?id=" . $attivita_id);
        exit;
    }
}
?>