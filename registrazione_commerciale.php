<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $comune_id = $_POST['comune_id'];

    $stmt = $pdo->prepare("INSERT INTO utenti (nome, email, password, ruolo, comune_id, tipo_login)
                           VALUES (?, ?, ?, 'commerciale', ?, 'email')");
    $stmt->execute([$nome, $email, $password, $comune_id]);

    header("Location: login.php");
    exit;
}
?>
<!-- Form registrazione attività commerciale -->