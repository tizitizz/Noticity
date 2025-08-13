<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) die("Accesso negato");

$stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$utente = $stmt->fetch();
?>
<?php include 'header.php'; ?>
<main>
  <h2>Gestione profilo</h2>
  <form action="salva_profilo_avanzato.php" method="POST" enctype="multipart/form-data">
    <label>Biografia:</label><br>
    <textarea name="bio"><?php echo htmlspecialchars($utente['bio']); ?></textarea><br><br>
    <label>Link social:</label><br>
    <input type="text" name="social" value="<?php echo htmlspecialchars($utente['social']); ?>"><br><br>
    <label>Immagine di copertina:</label><br>
    <input type="file" name="copertina"><br><br>
    <button type="submit">Salva</button>
  </form>
</main>
<?php include 'footer.php'; ?>
