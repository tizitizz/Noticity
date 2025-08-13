<?php
require '../config.php';
$stmt = $pdo->query("SELECT * FROM utenti ORDER BY data_registrazione DESC");
$utenti = $stmt->fetchAll();
?>
<h3>Utenti registrati</h3>
<table border="1">
<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ruolo</th><th>Comune</th></tr>
<?php foreach ($utenti as $u): ?>
<tr>
  <td><?= $u['id'] ?></td>
  <td><?= htmlspecialchars($u['nome']) ?></td>
  <td><?= htmlspecialchars($u['email']) ?></td>
  <td><?= $u['ruolo'] ?></td>
  <td><?= $u['comune_id'] ?></td>
</tr>
<?php endforeach; ?>
</table>
