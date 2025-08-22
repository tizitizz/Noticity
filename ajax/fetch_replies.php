<?php
// File: ajax/fetch_replies.php

session_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

$current_user_id = $_SESSION['user_id'] ?? 0;
$parent_id = (int)($_GET['parent_id'] ?? 0);
$limit = (int)($_GET['limit'] ?? 5);
$offset = (int)($_GET['offset'] ?? 0);

if ($parent_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID del commento non specificato.']);
    exit();
}

try {
    $sql_total = "SELECT COUNT(*) FROM commenti WHERE parent_id = ?";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([$parent_id]);
    $total_replies = $stmt_total->fetchColumn();

    $sql = "
        SELECT 
            c.id, c.commento, c.created_at, c.parent_id, c.post_id,
            u.id as utente_id, u.nome, u.cognome, u.foto_profilo,
            (SELECT COUNT(*) FROM commenti_reazioni cr WHERE cr.commento_id = c.id) as like_count,
            (SELECT COUNT(*) FROM commenti_reazioni cr_user WHERE cr_user.commento_id = c.id AND cr_user.utente_id = ?) as user_liked
        FROM commenti c 
        JOIN utenti u ON c.utente_id = u.id 
        WHERE c.parent_id = ?
        ORDER BY c.created_at ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $parent_id, PDO::PARAM_INT);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $replies_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $processed_replies = [];
    foreach ($replies_data as $row) {
        $row['foto_profilo_url'] = !empty($row['foto_profilo']) ? 'assets/img/utenti/' . $row['foto_profilo'] : 'assets/img/utenti/default.jpg';
        $row['time_ago'] = format_time_ago($row['created_at']);
        $row['user_liked'] = ($row['user_liked'] > 0);
	    $row['commento'] = force_word_wrap($row['commento']);
        $processed_replies[] = $row;
    }

    echo json_encode([
        'success' => true, 
        'replies' => $processed_replies,
        'total_replies' => $total_replies
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
?>