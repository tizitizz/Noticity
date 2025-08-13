<?php
// File: ajax/add_comment.php

session_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utente non autenticato.']);
    exit();
}
if (!isset($_POST['post_id']) || !isset($_POST['commento']) || empty(trim($_POST['commento']))) {
    echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
    exit();
}

$post_id = $_POST['post_id'];
$utente_id = $_SESSION['user_id'];
$testo_commento = trim($_POST['commento']);
$parent_id = isset($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

try {
    $pdo->beginTransaction();

    $sql_insert = "INSERT INTO commenti (post_id, utente_id, commento, parent_id) VALUES (?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$post_id, $utente_id, $testo_commento, $parent_id]);
    $new_comment_id = $pdo->lastInsertId();

    if ($parent_id === null) {
        $sql_update = "UPDATE post SET commenti = commenti + 1 WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$post_id]);
    }
    
    $pdo->commit();

    $sql_fetch = "
        SELECT 
            c.id, c.commento, c.created_at, c.parent_id, c.post_id,
            u.id as utente_id, u.nome, u.cognome, u.foto_profilo,
            0 as like_count,
            0 as user_liked
        FROM commenti c JOIN utenti u ON c.utente_id = u.id
        WHERE c.id = ?
    ";
    $stmt_fetch = $pdo->prepare($sql_fetch);
    $stmt_fetch->execute([$new_comment_id]);
    $new_comment_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

    if ($new_comment_data) {
        $new_comment_data['foto_profilo_url'] = !empty($new_comment_data['foto_profilo']) ? 'assets/img/utenti/' . $new_comment_data['foto_profilo'] : 'assets/img/utenti/default.jpg';
        $new_comment_data['time_ago'] = format_time_ago($new_comment_data['created_at']);
        $new_comment_data['user_liked'] = false;
	    $new_comment_data['commento'] = force_word_wrap($new_comment_data['commento']);
    }

    echo json_encode(['success' => true, 'comment' => $new_comment_data]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
?>