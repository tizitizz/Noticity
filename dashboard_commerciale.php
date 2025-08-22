<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

// Verifica ruolo commerciale
$stmt = $pdo->prepare("SELECT ruolo FROM utenti WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ruolo = $stmt->fetchColumn();

if ($ruolo !== 'commerciale') die("Accesso riservato alle attività commerciali.");

$stmt = $pdo->prepare("SELECT * FROM post_sponsorizzati WHERE utente_id = ? ORDER BY scadenza DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Le tue sponsorizzazioni</h2>
  <table>
    <tr><th>Contenuto</th><th>Area</th><th>Scadenza</th></tr>
    <?php foreach ($posts as $p): ?>
      <tr>
        <td><?php echo htmlspecialchars($p['contenuto']); ?></td>
        <td><?php echo htmlspecialchars($p['target_area']); ?></td>
        <td><?php echo htmlspecialchars($p['scadenza']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</main>
<?php include 'footer.php'; ?>
