<?php
require 'config.php';

$stmt = $pdo->query("SELECT * FROM dirette ORDER BY data_evento DESC");
$dirette = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Dirette Programmate</h2>
  <ul>
    <?php foreach ($dirette as $d): ?>
      <li>
        <strong><?php echo htmlspecialchars($d['titolo']); ?></strong> -
        <?php echo htmlspecialchars($d['data_evento']); ?>
        <a href="diretta.php?id=<?php echo $d['id']; ?>">Guarda</a>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
<?php include 'footer.php'; ?>
