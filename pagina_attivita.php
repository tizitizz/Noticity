<?php
require 'config.php';

$attivita_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM attivita WHERE id = ?");
$stmt->execute([$attivita_id]);
$attivita = $stmt->fetch();

if (!$attivita) {
    die("Attività non trovata.");
}
?>
<?php include 'header.php'; ?>

<main>
  <h2><?php echo htmlspecialchars($attivita['nome']); ?></h2>
  <p><strong>Categoria:</strong> <?php echo htmlspecialchars($attivita['categoria']); ?></p>
  <p><strong>Comune:</strong> <?php echo htmlspecialchars($attivita['comune']); ?></p>
  <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($attivita['descrizione']); ?></p>
  <p><strong>Contatto:</strong> <a href="tel:<?php echo $attivita['telefono']; ?>"><?php echo $attivita['telefono']; ?></a></p>
  <p><a href="https://wa.me/<?php echo $attivita['whatsapp']; ?>" target="_blank">Scrivici su WhatsApp</a></p>
</main>

<?php include 'footer.php'; ?>
