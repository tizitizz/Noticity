<?php
session_start();
require 'config.php';

$nome_comune = $_GET['nome'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM comuni WHERE nome = ?");
$stmt->execute([$nome_comune]);
$comune = $stmt->fetch();

if (!$comune) {
    die("Comune non trovato");
}

// Dati per breadcrumb
$regione = $comune['regione'];
$provincia = $comune['provincia'];
?>
<?php include 'header.php'; ?>

<main>
  <h2>Post recenti di <?php echo htmlspecialchars($nome_comune); ?></h2>

  <!-- Form per nuova proposta o segnalazione -->
  <form action="nuovo_post.php" method="POST">
    <input type="hidden" name="comune_id" value="<?php echo $comune['id']; ?>">
    <label>Tipo:</label>
    <select name="tipo">
      <option value="proposta">Proposta</option>
      <option value="segnalazione">Segnalazione</option>
    </select>
    <textarea name="contenuto" placeholder="Scrivi qui..."></textarea>
    <button type="submit">Pubblica</button>
  </form>

  <!-- Visualizzazione post: ogni 2 post normali, 1 sponsorizzato -->
  <?php
  $stmtPost = $pdo->prepare("SELECT * FROM post WHERE comune_id = ? ORDER BY data_creazione DESC");
  $stmtPost->execute([$comune['id']]);
  $posts = $stmtPost->fetchAll();

  $sponsorIndex = 0;
  foreach ($posts as $index => $post) {
      if ($index % 2 == 0) {
          $stmtSponsor = $pdo->prepare("SELECT * FROM post_sponsorizzati WHERE comune_id = ? AND NOW() <= scadenza ORDER BY RAND() LIMIT 1");
          $stmtSponsor->execute([$comune['id']]);
          $sponsor = $stmtSponsor->fetch();
          if ($sponsor) {
              echo "<div class='sponsor'><strong>Offerta:</strong> " . htmlspecialchars($sponsor['contenuto']) . "</div>";
          }
      }
      echo "<div class='post'><strong>{$post['tipo']}:</strong> " . htmlspecialchars($post['contenuto']) . "</div>";
  }
  ?>
</main>

<?php include 'footer.php'; ?>
