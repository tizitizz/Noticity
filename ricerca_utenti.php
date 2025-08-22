<?php
require 'config.php';
$nome = $_GET['nome'] ?? '';

$stmt = $pdo->prepare("SELECT id, nome FROM utenti WHERE nome LIKE ?");
$stmt->execute(["%$nome%"]);
$utenti = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Risultati per "<?php echo htmlspecialchars($nome); ?>"</h2>
  <ul>
    <?php foreach ($utenti as $u): ?>
      <li><a href="profilo_pubblico.php?id=<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></a></li>
    <?php endforeach; ?>
  </ul>
</main>
<?php include 'footer.php'; ?>
