<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $data_evento = $_POST['data_evento'];
    $luogo = $_POST['luogo'];
    $comune_id = $_POST['comune_id'];
    $utente_id = $_SESSION['user_id'];
    $immagine = null;

    if (!empty($_FILES['immagine']['name'])) {
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["immagine"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
            $immagine = $target_file;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO eventi (titolo, descrizione, data_evento, luogo, immagine, comune_id, utente_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titolo, $descrizione, $data_evento, $luogo, $immagine, $comune_id, $utente_id]);

    header("Location: eventi.php");
    exit;
}
?>