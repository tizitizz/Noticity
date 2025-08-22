<?php
require 'config.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT u.*, c.nome AS comune_nome FROM utenti u JOIN comuni c ON u.comune_id = c.id WHERE u.id = ? AND u.ruolo = 'pa'");
$stmt->execute([$id]);
$pa = $stmt->fetch();

if (!$pa) die("Profilo PA non trovato.");
?>
<?php include 'header.php'; ?>
<main>
  <h2>Pubblica Amministrazione: <?php echo htmlspecialchars($pa['nome']); ?></h2>
  <p><strong>Comune:</strong> <?php echo htmlspecialchars($pa['comune_nome']); ?></p>
  <?php if ($pa['foto_profilo']): ?>
    <img src="<?php echo $pa['foto_profilo']; ?>" alt="Foto profilo" style="max-width:150px;">
  <?php endif; ?>
  <p><strong>Bio:</strong><br><?php echo nl2br(htmlspecialchars($pa['bio'])); ?></p>
</main>
<?php include 'footer.php'; ?>
