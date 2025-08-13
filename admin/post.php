<?php
require '../config.php';
$stmt = $pdo->query("SELECT * FROM post ORDER BY data_creazione DESC");
$post = $stmt->fetchAll();
?>
<h3>Post pubblicati</h3>
<table border="1">
<tr><th>ID</th><th>Tipo</th><th>Contenuto</th><th>Utente</th></tr>
<?php foreach ($post as $p): ?>
<tr>
  <td><?= $p['id'] ?></td>
  <td><?= $p['tipo'] ?></td>
  <td><?= htmlspecialchars($p['contenuto']) ?></td>
  <td><?= $p['utente_id'] ?></td>
</tr>
<?php endforeach; ?>
</table>
