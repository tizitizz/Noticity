<?php
// File: crea_post.php (Versione Finale, Robusta e Sicura)

session_start();
require 'config.php';
require 'includes/functions.php';

header('Content-Type: application/json');

function inviaRisposta($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'post_data' => $data]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    inviaRisposta('error', 'Utente non autenticato.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    inviaRisposta('error', 'Metodo non consentito.');
}
if (empty($_POST['contenuto']) || empty($_POST['tipo'])) {
    inviaRisposta('error', 'Tipo e contenuto del post sono obbligatori.');
}

// Logica anti-doppio click
$tempo_limite = 5; // Secondi
if (isset($_SESSION['last_post_time']) && (time() - $_SESSION['last_post_time'] < $tempo_limite)) {
    inviaRisposta('error', 'Attendi qualche secondo prima di pubblicare di nuovo.');
}

$utente_id = $_SESSION['user_id'];
$contenuto = trim($_POST['contenuto']);
$tipo = $_POST['tipo'];

$stmt_user = $pdo->prepare("SELECT nome, cognome, comune_id, foto_profilo FROM utenti WHERE id = ?");
$stmt_user->execute([$utente_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    inviaRisposta('error', 'Utente non trovato.');
}

$nome_utente = $user['nome'];
$cognome_utente = $user['cognome'];
$comune_id = $user['comune_id'];
$foto_profilo = $user['foto_profilo'];
$image_path_post = null;

if (isset($_POST['image_post_base64']) && !empty($_POST['image_post_base64'])) {
    $data = $_POST['image_post_base64'];
    if (strpos($data, 'data:image/jpeg;base64,') === 0) {
        $data = str_replace('data:image/jpeg;base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        $imageData = base64_decode($data);
        $imageName = uniqid() . '.jpg';
        $uploadPath = 'assets/img/posts/' . $imageName;
        if (!file_put_contents($uploadPath, $imageData)) {
            inviaRisposta('error', 'Impossibile salvare l\'immagine.');
        }
        $image_path_post = $uploadPath;
    }
}

try {
    $sql = "INSERT INTO post (utente_id, comune_id, tipo, contenuto, nome_utente, cognome_utente, image_path_post) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$utente_id, $comune_id, $tipo, $contenuto, $nome_utente, $cognome_utente, $image_path_post]);
    $new_post_id = $pdo->lastInsertId();
    $_SESSION['last_post_time'] = time();

    $post_data = [
        'id' => $new_post_id,
        'utente_id' => $utente_id,
        'nome_utente' => $nome_utente,
        'cognome_utente' => $cognome_utente,
        'foto_profilo' => $foto_profilo,
        'tipo' => $tipo,
        'contenuto' => nl2br(htmlspecialchars($contenuto)),
        'image_path_post' => $image_path_post,
        'created_at' => 'adesso',
        'likes' => 0,
        'dislikes' => 0,
        'commenti' => 0
    ];
    inviaRisposta('success', 'Post pubblicato con successo!', $post_data);
} catch (PDOException $e) {
    inviaRisposta('error', 'Errore del database: ' . $e->getMessage());
}
?>