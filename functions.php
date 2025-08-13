<?php
function getUserComune($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT comune_id FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}
?>