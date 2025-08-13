<?php
require '../config.php';
$stmt = $pdo->query("SELECT * FROM attivita ORDER BY data_creazione DESC");
$att = $stmt->fetchAll();
?>
<h3>Attività Commerciali</h3>
<table border="1">
<tr><th>ID</th><th>Nome</th><th>Comune</th><th>Telefono</th><th>WhatsApp</th></tr>
<?php foreach ($att as $a): ?>
<tr>
  <td><?= $a['id'] ?></td>
  <td><?= htmlspecialchars($a['nome']) ?></td>
  <td><?= htmlspecialchars($a['comune']) ?></td>
  <td><?= htmlspecialchars($a['telefono']) ?></td>
  <td><?= htmlspecialchars($a['whatsapp']) ?></td>
</tr>
<?php endforeach; ?>
</table>
