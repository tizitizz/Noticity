<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domanda = $_POST['domanda'];
    $op1 = $_POST['opzione1'];
    $op2 = $_POST['opzione2'];
    $op3 = $_POST['opzione3'];
    $op4 = $_POST['opzione4'];
    $comune_id = $_POST['comune_id'];
    $utente_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO sondaggi (domanda, opzione1, opzione2, opzione3, opzione4, comune_id, utente_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$domanda, $op1, $op2, $op3, $op4, $comune_id, $utente_id]);

    header("Location: sondaggi.php");
    exit;
}
?>