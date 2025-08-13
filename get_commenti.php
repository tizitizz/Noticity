<?php
session_start();
require 'config.php';

$post_id = intval($_GET['post_id'] ?? 0);
if (!$post_id) exit("Errore: ID post mancante");

// Recupera commenti principali
$stmt = $pdo->prepare("SELECT c.*, u.nome, u.cognome, u.foto_profilo 
                       FROM commenti c 
                       JOIN utenti u ON c.utente_id = u.id 
                       WHERE c.post_id=? AND c.commento_padre_id IS NULL
                       ORDER BY c.created_at ASC");
$stmt->execute([$post_id]);
$commenti = $stmt->fetchAll();

// Funzione per recuperare risposte
function getRisposte($pdo, $commento_id) {
    $stmt = $pdo->prepare("SELECT c.*, u.nome, u.cognome, u.foto_profilo 
                           FROM commenti c 
                           JOIN utenti u ON c.utente_id = u.id 
                           WHERE c.commento_padre_id=? 
                           ORDER BY c.created_at ASC");
    $stmt->execute([$commento_id]);
    return $stmt->fetchAll();
}

foreach ($commenti as $c):
?>
<li>
  <div class="comet-avatar">
    <img src="assets/img/utenti/<?php echo $c['foto_profilo'] ?: 'default.jpg'; ?>" alt="">
  </div>
  <div class="we-comment">
    <div class="coment-head">
      <h5><?php echo htmlspecialchars($c['nome'] . ' ' . $c['cognome']); ?></h5>
      <span><?php echo date("d/m/Y H:i", strtotime($c['created_at'])); ?></span>
      <a href="#" class="we-reply reply-btn" data-id="<?php echo $c['id']; ?>">Rispondi</a>
      <a href="#" class="we-reply like-btn" data-id="<?php echo $c['id']; ?>">Mi piace</a>
    </div>
    <p><?php echo nl2br(htmlspecialchars($c['testo'])); ?></p>
  </div>

  <ul class="reply-list" id="replies-<?php echo $c['id']; ?>">
    <?php
    $risposte = getRisposte($pdo, $c['id']);
    foreach ($risposte as $r):
    ?>
    <li>
      <div class="comet-avatar">
        <img src="assets/img/utenti/<?php echo $r['foto_profilo'] ?: 'default.jpg'; ?>" alt="">
      </div>
      <div class="we-comment">
        <div class="coment-head">
          <h5><?php echo htmlspecialchars($r['nome'] . ' ' . $r['cognome']); ?></h5>
          <span><?php echo date("d/m/Y H:i", strtotime($r['created_at'])); ?></span>
          <a href="#" class="we-reply like-btn" data-id="<?php echo $r['id']; ?>">Mi piace</a>
        </div>
        <p><?php echo nl2br(htmlspecialchars($r['testo'])); ?></p>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>

</li>
<?php endforeach;

if (!$commenti) echo "<p>Nessun commento</p>";
?>
