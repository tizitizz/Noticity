<?php
function aggiungiNotifica($pdo, $utente_id, $testo) {
    $stmt = $pdo->prepare("INSERT INTO notifiche (utente_id, testo, data) VALUES (?, ?, NOW())");
    $stmt->execute([$utente_id, $testo]);
}
?>