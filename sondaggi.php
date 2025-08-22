<?php
session_start();
require 'config.php';

$stmt = $pdo->query("SELECT * FROM sondaggi ORDER BY data_creazione DESC");
$sondaggi = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Sondaggi</h2>
  <?php foreach ($sondaggi as $s): ?>
    <form action="vota.php" method="POST">
      <h3><?php echo htmlspecialchars($s['domanda']); ?></h3>
      <input type="hidden" name="sondaggio_id" value="<?php echo $s['id']; ?>">
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <?php if (!empty($s["opzione$i"])): ?>
          <label>
            <input type="radio" name="scelta" value="<?php echo $i; ?>" required>
            <?php echo htmlspecialchars($s["opzione$i"]); ?>
          </label><br>
        <?php endif; ?>
      <?php endfor; ?>
      <button type="submit">Vota</button>
    </form>
    <hr>
  <?php endforeach; ?>
</main>
<?php include 'footer.php'; ?>
