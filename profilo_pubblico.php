<?php
require 'config.php';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT u.*, c.nome AS comune_nome FROM utenti u JOIN comuni c ON u.comune_id = c.id WHERE u.id = ?");
$stmt->execute([$id]);
$utente = $stmt->fetch();

if (!$utente) {
    die("Utente non trovato.");
}
?>
<?php include 'header.php'; ?>
<main>
  <h2><?php echo htmlspecialchars($utente['nome']); ?></h2>
  <p><strong>Ruolo:</strong> <?php echo $utente['ruolo']; ?></p>
  <p><strong>Comune:</strong> <?php echo $utente['comune_nome']; ?></p>
  <?php if ($utente['foto_profilo']): ?>
    <img src="<?php echo $utente['foto_profilo']; ?>" alt="Foto profilo" style="max-width:150px;">
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
