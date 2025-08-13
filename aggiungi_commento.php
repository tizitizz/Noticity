<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non loggato']);
    exit;
}

$utente_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
$testo = trim($_POST['commento'] ?? '');
$commento_padre_id = isset($_POST['padre_id']) ? intval($_POST['padre_id']) : null;

if (!$post_id || $testo === '') {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$query = "INSERT INTO commenti (post_id, utente_id, testo, created_at";
$values = [$post_id, $utente_id, $testo];
$placeholders = "?, ?, ?, NOW()";

if ($commento_padre_id) {
    $query .= ", commento_padre_id";
    $placeholders .= ", ?";
    $values[] = $commento_padre_id;
}

$query .= ") VALUES ($placeholders)";
$stmt = $pdo->prepare($query);
$stmt->execute($values);

// Aggiorna contatore commenti (solo commenti principali)
$conteggio = $pdo->query("SELECT COUNT(*) FROM commenti WHERE post_id=$post_id AND commento_padre_id IS NULL")->fetchColumn();
$pdo->prepare("UPDATE post SET commenti=? WHERE id=?")->execute([$conteggio, $post_id]);

echo json_encode(['success' => true]);
?>
