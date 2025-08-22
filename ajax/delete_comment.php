<?php
// File: ajax/delete_comment.php (Versione con Aggiornamento Contatore)
session_start();
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utente non autenticato.']);
    exit();
}
// Ora richiediamo anche l'ID del post, che ci serve per l'aggiornamento
if (!isset($_POST['commento_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Dati mancanti (ID commento o post).']);
    exit();
}

$commento_id = $_POST['commento_id'];
$post_id = $_POST['post_id'];
$current_user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Controllo di sicurezza: l'utente può cancellare solo i propri commenti
    $sql_check = "SELECT utente_id FROM commenti WHERE id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$commento_id]);
    $comment_author_id = $stmt_check->fetchColumn();

    if ($comment_author_id != $current_user_id) {
        echo json_encode(['success' => false, 'error' => 'Non hai i permessi per eliminare questo commento.']);
        exit();
    }
    
    // Cancella il commento (e a cascata le sue risposte e reazioni, grazie al DB)
    $sql_delete = "DELETE FROM commenti WHERE id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$commento_id]);
    
    // --- NUOVA LOGICA: Ricalcola e Aggiorna il Contatore ---
    // Dopo aver cancellato, contiamo quanti commenti principali sono rimasti per quel post
    $sql_count = "SELECT COUNT(*) FROM commenti WHERE post_id = ? AND parent_id IS NULL";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([$post_id]);
    $new_comment_count = $stmt_count->fetchColumn();

    // Aggiorniamo la tabella 'post' con il nuovo conteggio
    $sql_update = "UPDATE post SET commenti = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_comment_count, $post_id]);
    
    $pdo->commit();

    // Restituiamo il nuovo conteggio al JavaScript, così può aggiornare la UI
    echo json_encode(['success' => true, 'new_comment_count' => $new_comment_count]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
?>