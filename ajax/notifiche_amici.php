
<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utente non autenticato']);
    exit();
}

$utente_id = $_SESSION['user_id'];

try {
    // Ottieni le notifiche degli amici non lette
    $stmt = $pdo->prepare("
        SELECT na.*, 
               u.nome, u.cognome, u.foto_profilo
        FROM notifiche_amici na
        JOIN utenti u ON na.da_utente_id = u.id
        WHERE na.utente_id = ? AND na.letta = FALSE
        ORDER BY na.data_creazione DESC
        LIMIT 20
    ");
    $stmt->execute([$utente_id]);
    $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ottieni il conteggio delle notifiche non lette
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifiche_amici WHERE utente_id = ? AND letta = FALSE");
    $stmt->execute([$utente_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se è stata richiesta la marcatura come lette
    if ($_POST['marca_lette'] ?? false) {
        $stmt = $pdo->prepare("UPDATE notifiche_amici SET letta = TRUE WHERE utente_id = ?");
        $stmt->execute([$utente_id]);
    }
    
    echo json_encode([
        'success' => true, 
        'notifiche' => $notifiche,
        'count' => $count['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Errore del server: ' . $e->getMessage()]);
}
?>
