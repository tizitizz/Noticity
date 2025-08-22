<?php
// File: ajax/report_comment.php
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
$utente_segnalante_id = $_SESSION['user_id'];

try {
    // Inseriamo la segnalazione, ignorando i duplicati
    $sql = "INSERT IGNORE INTO segnalazioni_commenti (commento_id, utente_segnalante_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commento_id, $utente_segnalante_id]);

    echo json_encode(['success' => true, 'message' => 'Commento segnalato con successo.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
?>