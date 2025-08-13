<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non sei loggato']);
    exit;
}

$utente_id = $_SESSION['user_id'];
$azione = $_POST['azione'] ?? '';
$post_id = intval($_POST['post_id'] ?? 0);

if (!$post_id || !in_array($azione, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

// Verifica se l'utente ha già reagito
$stmt = $pdo->prepare("SELECT tipo FROM reazioni_post WHERE post_id=? AND utente_id=?");
$stmt->execute([$post_id, $utente_id]);
$esiste = $stmt->fetchColumn();

if ($esiste) {
    // Se ha già reagito con lo stesso tipo, rimuove la reazione
    if ($esiste === $azione) {
        $pdo->prepare("DELETE FROM reazioni_post WHERE post_id=? AND utente_id=?")->execute([$post_id, $utente_id]);
    } else {
        // Altrimenti aggiorna
        $pdo->prepare("UPDATE reazioni_post SET tipo=? WHERE post_id=? AND utente_id=?")->execute([$azione, $post_id, $utente_id]);
    }
} else {
    // Nuova reazione
    $pdo->prepare("INSERT INTO reazioni_post (post_id, utente_id, tipo) VALUES (?, ?, ?)")->execute([$post_id, $utente_id, $azione]);
}

// Aggiorna conteggi
$likes = $pdo->query("SELECT COUNT(*) FROM reazioni_post WHERE post_id=$post_id AND tipo='like'")->fetchColumn();
$dislikes = $pdo->query("SELECT COUNT(*) FROM reazioni_post WHERE post_id=$post_id AND tipo='dislike'")->fetchColumn();

$pdo->prepare("UPDATE post SET likes=?, dislikes=? WHERE id=?")->execute([$likes, $dislikes, $post_id]);

echo json_encode(['success' => true, 'likes' => $likes, 'dislikes' => $dislikes]);
?>
