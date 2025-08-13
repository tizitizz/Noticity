<?php
session_start();
require 'config.php'; // Assicurati di includere la connessione al database

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Recupera i dati inviati dal modulo
$Name = isset($_POST['Name']) ? trim($_POST['Name']) : '';
$Cognome = isset($_POST['Cognome']) ? trim($_POST['Cognome']) : '';
$profession = isset($_POST['profession']) ? trim($_POST['profession']) : '';
$birthDate = isset($_POST['birthDate']) ? $_POST['birthDate'] : ''; // Data di nascita
$comuneId = isset($_POST['comune']) ? $_POST['comune'] : ''; // Città selezionata

// Se la data di nascita è vuota, impostala su NULL
if (empty($birthDate)) {
    $birthDate = NULL;
}

// Variabili per il caricamento dell'immagine
$profileImage = '';
$maxFileSize = 2 * 1024 * 1024; // 2 MB in byte

if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $profileImageFile = $_FILES['profileImage'];  // L'array del file
    $uploadDirectory = 'assets/img/utenti/';
    $uploadedImage = $uploadDirectory . basename($profileImageFile['name']);
    
    // Verifica la dimensione del file
    if ($profileImageFile['size'] > $maxFileSize) {
        echo "Errore: L'immagine è troppo grande. La dimensione massima consentita è 2 MB.";
        exit;
    }
    
    // Muovi il file caricato nella cartella di destinazione
    if (move_uploaded_file($profileImageFile['tmp_name'], $uploadedImage)) {
        $profileImage = basename($profileImageFile['name']);  // Usa solo il nome del file
    }
}

// Prepara la query di aggiornamento
$query = "UPDATE utenti SET nome = :Name, Cognome = :Cognome, professione = :profession, data_nascita = :birthDate, comune_id = :comuneId";

if (!empty($profileImage)) {
    $query .= ", foto_profilo = :profileImage";  // Cambia immagine_profilo con foto_profilo
}

$query .= " WHERE id = :userId";

// Esegui la query
$stmt = $pdo->prepare($query);
$stmt->bindParam(':Name', $Name);
$stmt->bindParam(':Cognome', $Cognome);
$stmt->bindParam(':profession', $profession);
$stmt->bindParam(':birthDate', $birthDate, PDO::PARAM_STR);  // Passa come stringa per gestire NULL
$stmt->bindParam(':comuneId', $comuneId);
$stmt->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);

if (!empty($profileImage)) {
    $stmt->bindParam(':profileImage', $profileImage); // Associa il nome del file immagine
}

if ($stmt->execute()) {
    // Successo
    header("Location: profilo_utente.php");
    exit;
} else {
    // Errore durante l'aggiornamento
    echo "Si è verificato un errore durante l'aggiornamento del profilo.";
}
?>
