<?php
require 'config.php';
$post_id = $_GET['post_id'] ?? 0;

$stmt = $pdo->prepare("SELECT tipo, COUNT(*) as totale FROM reazioni WHERE post_id = ? GROUP BY tipo");
$stmt->execute([$post_id]);
$reazioni = $stmt->fetchAll();

foreach ($reazioni as $r) {
    echo "<div>{$r['tipo']}: {$r['totale']}</div>";
}
?>