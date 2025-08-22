<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $categoria = $_POST['categoria'];
    $comune = $_POST['comune'];
    $descrizione = $_POST['descrizione'];
    $telefono = $_POST['telefono'];
    $whatsapp = $_POST['whatsapp'];

    $stmt = $pdo->prepare("INSERT INTO attivita (nome, categoria, comune, descrizione, telefono, whatsapp)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $categoria, $comune, $descrizione, $telefono, $whatsapp]);

    header("Location: pagina_attivita.php?id=" . $pdo->lastInsertId());
    exit;
}
?>
<!-- HTML con form per inserire/modificare attività -->