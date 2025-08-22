<?php
require 'config.php';

$stmt = $pdo->query("SELECT e.*, c.nome AS comune_nome FROM eventi e JOIN comuni c ON e.comune_id = c.id ORDER BY data_evento ASC");
$eventi = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Eventi in programma</h2>
  <ul>
    <?php foreach ($eventi as $e): ?>
      <li>
        <h3><?php echo htmlspecialchars($e['titolo']); ?></h3>
        <p><?php echo htmlspecialchars($e['descrizione']); ?></p>
        <p><strong>Quando:</strong> <?php echo $e['data_evento']; ?></p>
        <p><strong>Dove:</strong> <?php echo htmlspecialchars($e['luogo']); ?> (<?php echo $e['comune_nome']; ?>)</p>
        <?php if ($e['immagine']): ?>
          <img src="<?php echo $e['immagine']; ?>" alt="Immagine evento" style="max-width:300px;">
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
<?php include 'footer.php'; ?>
