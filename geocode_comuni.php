<?php
// File: geocode_comuni.php (Versione aggiornata con cURL)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

require 'config.php';

echo "<h1>Processo di Geocoding dei Comuni (con cURL)</h1>";

$stmt = $pdo->query("SELECT id, nome, provincia FROM comuni WHERE lat IS NULL OR lng IS NULL");
$comuni_da_aggiornare = $stmt->fetchAll();

if (empty($comuni_da_aggiornare)) {
    echo "<p>Tutti i comuni hanno già le coordinate.</p>";
    exit;
}

echo "<p>Trovati " . count($comuni_da_aggiornare) . " comuni da aggiornare...</p><ul>";

$aggiornati = 0;
$falliti = 0;

foreach ($comuni_da_aggiornare as $comune) {
    $indirizzo = $comune['nome'] . ", " . $comune['provincia'] . ", Italia";
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($indirizzo) . "&format=json&limit=1";

    // --- NUOVA LOGICA CON cURL ---
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'NoticityGeocodingScript/1.0'); // Obbligatorio per l'API di Nominatim
    $response = curl_exec($ch);
    curl_close($ch);
    // --- FINE LOGICA cURL ---

    $data = json_decode($response, true);

    if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];

        $update_stmt = $pdo->prepare("UPDATE comuni SET lat = ?, lng = ? WHERE id = ?");
        $update_stmt->execute([$lat, $lng, $comune['id']]);
        
        echo "<li style='color: green;'><strong>SUCCESS:</strong> " . htmlspecialchars($comune['nome']) . " aggiornato.</li>";
        $aggiornati++;
    } else {
        echo "<li style='color: red;'><strong>FALLITO:</strong> Impossibile trovare coordinate per " . htmlspecialchars($comune['nome']) . ".</li>";
        $falliti++;
    }
    sleep(1); 
}

echo "</ul><h3>Processo completato!</h3><p>Comuni aggiornati: $aggiornati</p><p>Comuni falliti: $falliti</p>";
?>