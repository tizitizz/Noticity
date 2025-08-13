<?php
require 'config.php';

$stmt = $pdo->query("SELECT a.*, c.nome AS comune_nome FROM annunci a JOIN comuni c ON a.comune_id = c.id ORDER BY data_pubblicazione DESC");
$annunci = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Annunci Locali</h2>
  <?php foreach ($annunci as $a): ?>
    <article>
      <h3><?php echo htmlspecialchars($a['titolo']); ?></h3>
      <p><?php echo htmlspecialchars($a['descrizione']); ?></p>
      <small>Comune: <?php echo htmlspecialchars($a['comune_nome']); ?> | Data: <?php echo $a['data_pubblicazione']; ?></small>
    </article>
    <hr>
  <?php endforeach; ?>
</main>
<?php include 'footer.php'; ?>
