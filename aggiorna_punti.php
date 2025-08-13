<?php
function assegnaPunti($pdo, $utente_id, $punti) {
    $stmt = $pdo->prepare("UPDATE utenti SET punti = punti + ?, livello = FLOOR(punti / 100) + 1 WHERE id = ?");
    $stmt->execute([$punti, $utente_id]);
}
?>