<?php
require 'config.php';

$nome_comune = $_GET['nome'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM comuni WHERE nome = ?");
$stmt->execute([$nome_comune]);
$comune = $stmt->fetch();

if (!$comune) {
    die("Comune non trovato");
}

// Esempio meteo dettagliato (mock)
$meteo = [
  "Lunedì" => "Soleggiato, 30°C",
  "Martedì" => "Pioggia, 24°C",
  "Mercoledì" => "Nuvoloso, 27°C"
];
?>
<?php include 'header.php'; ?>

<main>
  <h2>Comune di <?php echo htmlspecialchars($comune['nome']); ?></h2>
  <p><strong>Provincia:</strong> <?php echo $comune['provincia']; ?></p>
  <p><strong>Regione:</strong> <?php echo $comune['regione']; ?></p>
  <p><strong>CAP:</strong> <?php echo $comune['cap']; ?></p>
  <p><strong>Abitanti:</strong> <?php echo $comune['abitanti']; ?></p>
  <h3>Meteo dettagliato:</h3>
  <ul>
    <?php foreach ($meteo as $giorno => $descrizione): ?>
      <li><?php echo $giorno . ': ' . $descrizione; ?></li>
    <?php endforeach; ?>
  </ul>
</main>

<?php include 'footer.php'; ?>
