<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';

$comuni = $pdo->query("SELECT id, nome FROM comuni ORDER BY nome ASC")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password_raw = $_POST['password'] ?? '';
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $nome = $_POST['nome'] ?? '';
    $comune_id = $_POST['comune_id'] ?? null;
    $ruolo = $_POST['ruolo'] ?? 'cittadino';

    if (!$comune_id) {
        $error = "Seleziona un comune valido.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email già registrata.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO utenti (nome, email, password, comune_id, ruolo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $password, $comune_id, $ruolo]);

            $_SESSION['user_id'] = $pdo->lastInsertId();

            echo "<strong>DEBUG:</strong> user_id = " . $_SESSION['user_id'];
            echo "<br><a href='redirect_to_home.php'>Vai alla home manualmente</a>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Registrazione (Debug)</title>
</head>
<body>
  <h2>Registrazione Debug</h2>
  <?php if ($error): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>
  <form method="post" action="register_debug.php">
    <label>Nome completo:<br><input type="text" name="nome" required></label><br><br>
    <label>Email:<br><input type="email" name="email" required></label><br><br>
    <label>Password:<br><input type="password" name="password" required></label><br><br>
    <label>Ruolo:<br>
      <select name="ruolo" required>
        <option value="cittadino">Cittadino</option>
        <option value="pa">Pubblica Amministrazione</option>
        <option value="commerciale">Profilo Commerciale</option>
      </select>
    </label><br><br>
    <label>Comune di residenza:<br>
      <select name="comune_id" required>
        <option value="">-- Seleziona un comune --</option>
        <?php foreach ($comuni as $comune): ?>
          <option value="<?= $comune['id'] ?>"><?= htmlspecialchars($comune['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </label><br><br>
    <button type="submit">Registrati</button>
  </form>
</body>
</html>
