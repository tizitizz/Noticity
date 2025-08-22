<?php
// get_commenti.php
include('db.php'); // Includi il file di connessione al database

// Verifica che i dati siano inviati correttamente
if (isset($_POST['post_id']) && isset($_POST['commento'])) {
    $post_id = $_POST['post_id'];
    $commento = $_POST['commento'];
    $utente_id = $_SESSION['user_id']; // Assicurati che l'utente sia loggato

    // Prepara la query SQL per l'inserimento del commento
    $sql = "INSERT INTO commenti (post_id, utente_id, commento) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        // Associa i parametri alla query
        $stmt->bind_param("iis", $post_id, $utente_id, $commento);
        
        // Esegui la query e controlla se è riuscita
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Commento aggiunto con successo!"]);
        } else {
            // In caso di errore, restituisci il messaggio di errore
            echo json_encode(["status" => "error", "message" => "Errore nell'inserimento del commento: " . $stmt->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nella preparazione della query: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dati mancanti!"]);
}
?>
