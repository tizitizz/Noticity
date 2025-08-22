<?php
session_start();
require 'config.php';

$id_diretta = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM dirette WHERE id = ?");
$stmt->execute([$id_diretta]);
$diretta = $stmt->fetch();

if (!$diretta) {
    die("Diretta non trovata.");
}
?>
<?php include 'header.php'; ?>
<main>
  <h2><?php echo htmlspecialchars($diretta['titolo']); ?></h2>
  <p><?php echo htmlspecialchars($diretta['descrizione']); ?></p>
  <iframe width="100%" height="400" src="<?php echo htmlspecialchars($diretta['embed_url']); ?>" frameborder="0" allowfullscreen></iframe>
</main>
<?php include 'footer.php'; ?>
