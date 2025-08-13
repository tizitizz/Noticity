<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

$stmt = $pdo->prepare("SELECT * FROM notifiche WHERE utente_id = ? ORDER BY data DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifiche = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Le tue notifiche</h2>
  <ul>
    <?php foreach ($notifiche as $n): ?>
      <li><?php echo htmlspecialchars($n['testo']); ?> (<?php echo $n['data']; ?>)</li>
    <?php endforeach; ?>
  </ul>
</main>
<?php include 'footer.php'; ?>
