<?php
require '../config.php';
$stmt = $pdo->query("SELECT * FROM comuni ORDER BY nome ASC");
$comuni = $stmt->fetchAll();
?>
<h3>Comuni</h3>
<table border="1">
<tr><th>ID</th><th>Nome</th><th>Provincia</th><th>Regione</th><th>Abitanti</th></tr>
<?php foreach ($comuni as $c): ?>
<tr>
  <td><?= $c['id'] ?></td>
  <td><?= htmlspecialchars($c['nome']) ?></td>
  <td><?= htmlspecialchars($c['provincia']) ?></td>
  <td><?= htmlspecialchars($c['regione']) ?></td>
  <td><?= $c['abitanti'] ?></td>
</tr>
<?php endforeach; ?>
</table>
