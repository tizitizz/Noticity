<?php
// File: ajax/like_comment.php

session_start();
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utente non autenticato.']);
    exit();
}

if (!isset($_POST['commento_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID del commento non specificato.']);
    exit();
}

$commento_id = $_POST['commento_id'];
$utente_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Controlliamo se l'utente ha già messo 'mi piace' a questo commento
    $sql_check = "SELECT id FROM commenti_reazioni WHERE commento_id = ? AND utente_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$commento_id, $utente_id]);
    
    if ($stmt_check->fetch()) {
        // Se esiste, l'utente sta togliendo il 'mi piace'
        $sql = "DELETE FROM commenti_reazioni WHERE commento_id = ? AND utente_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commento_id, $utente_id]);
        $user_liked = false;
    } else {
        // Se non esiste, l'utente sta mettendo 'mi piace'
        $sql = "INSERT INTO commenti_reazioni (commento_id, utente_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commento_id, $utente_id]);
        $user_liked = true;
    }

    // Ora contiamo il nuovo totale di 'mi piace' per quel commento
    $sql_count = "SELECT COUNT(*) FROM commenti_reazioni WHERE commento_id = ?";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([$commento_id]);
    $like_count = $stmt_count->fetchColumn();

    $pdo->commit();

    // Restituiamo una risposta completa al JavaScript
    echo json_encode([
        'success' => true, 
        'like_count' => $like_count,
        'user_liked' => $user_liked // true se ora ha il like, false se l'ha tolto
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
?>