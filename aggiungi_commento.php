<?php
require 'db_connection.php';

$post_id = $_POST['post_id'];
$utente_id = $_POST['utente_id'];
$commento = $_POST['commento'];
$parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null; // Risposta a un commento

$sql = "INSERT INTO commenti (post_id, utente_id, commento, parent_id) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iisi', $post_id, $utente_id, $commento, $parent_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>
