<?php
// File: elimina_post.php (Versione Finale, Sicura e Funzionante)

session_start();
require 'config.php'; // Assicura la connessione al database

// 1. Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    // Se non è loggato, reindirizza al login
    header("Location: login.php");
    exit();
}

// 2. Controlla se è stato fornito un ID del post
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Se l'ID non è valido, reindirizza alla home
    header("Location: index.php?error=invalid_id");
    exit();
}

$post_id_da_cancellare = $_GET['id'];
$utente_loggato_id = $_SESSION['user_id'];

try {
    // 3. CONTROLLO DI SICUREZZA: Verifica che l'utente loggato sia l'autore del post
    $sql_check = "SELECT utente_id FROM post WHERE id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$post_id_da_cancellare]);
    $autore_post_id = $stmt_check->fetchColumn();

    if ($autore_post_id != $utente_loggato_id) {
        // Se non è l'autore, non ha i permessi. Reindirizza alla home con un errore.
        header("Location: index.php?error=permission_denied");
        exit();
    }

    // 4. Se tutti i controlli passano, procedi con l'eliminazione in una transazione
    $pdo->beginTransaction();

    // A) Cancella prima le reazioni associate al post
    $stmt_reazioni = $pdo->prepare("DELETE FROM reazioni_post WHERE post_id = ?");
    $stmt_reazioni->execute([$post_id_da_cancellare]);

    // B) Cancella le segnalazioni dei commenti di quel post
    $stmt_segnalazioni = $pdo->prepare("DELETE sc FROM segnalazioni_commenti sc JOIN commenti c ON sc.commento_id = c.id WHERE c.post_id = ?");
    $stmt_segnalazioni->execute([$post_id_da_cancellare]);

    // C) Cancella le reazioni ai commenti di quel post
    $stmt_reazioni_commenti = $pdo->prepare("DELETE cr FROM commenti_reazioni cr JOIN commenti c ON cr.commento_id = c.id WHERE c.post_id = ?");
    $stmt_reazioni_commenti->execute([$post_id_da_cancellare]);

    // D) Cancella tutti i commenti e le risposte associate al post
    $stmt_commenti = $pdo->prepare("DELETE FROM commenti WHERE post_id = ?");
    $stmt_commenti->execute([$post_id_da_cancellare]);

    // E) Infine, cancella il post
    $stmt_post = $pdo->prepare("DELETE FROM post WHERE id = ?");
    $stmt_post->execute([$post_id_da_cancellare]);

    // Conferma tutte le operazioni
    $pdo->commit();

    // 5. Reindirizza alla home con un messaggio di successo
    header("Location: index.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // Se qualcosa va storto, annulla tutto
    $pdo->rollBack();
    // Reindirizza alla home con un messaggio di errore generico
    // In un'applicazione reale, potresti voler loggare l'errore: error_log($e->getMessage());
    header("Location: index.php?error=db_error");
    exit();
}
?>