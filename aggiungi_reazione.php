<?php
session_start();
require 'config.php';

$utente_id = $_SESSION['user_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;
$tipo = $_POST['tipo'] ?? null;

if ($utente_id && $post_id && $tipo) {
    $stmt = $pdo->prepare("REPLACE INTO reazioni (utente_id, post_id, tipo, data_reazione)
                           VALUES (?, ?, ?, NOW())");
    $stmt->execute([$utente_id, $post_id, $tipo]);
}
?>