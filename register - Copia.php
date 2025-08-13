<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';

// Carica comuni dal database
$comuni = $pdo->query("SELECT id, nome FROM comuni ORDER BY nome ASC")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $ruolo = $_POST['ruolo'] ?? 'cittadino';
    $comune_id = $_POST['comune_id'] ?? null;

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
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrazione - Noticity</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<main>
  <div class="container">
    <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
            <div class="d-flex justify-content-center py-4">
              <a href="index.php" class="logo d-flex align-items-center w-auto">
                <img src="assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">Noticity</span>
              </a>
            </div>
            <div class="card mb-3">
              <div class="card-body">
                <div class="pt-4 pb-2">
                  <h5 class="card-title text-center pb-0 fs-4">Crea un account</h5>
                  <p class="text-center small">Inserisci i tuoi dati per registrarti</p>
                </div>
                <?php if ($error): ?>
                  <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3 needs-validation" novalidate>
                  <div class="col-12">
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="nome" class="form-control" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Ruolo</label>
                    <select name="ruolo" class="form-select" required>
                      <option value="cittadino">Cittadino</option>
                      <option value="pa">Pubblica Amministrazione</option>
                      <option value="commerciale">Profilo Commerciale</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Comune di residenza</label>
                    <select name="comune_id" class="form-select" required>
                      <option value="">-- Seleziona un comune --</option>
                      <?php foreach ($comuni as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" required>
                      <label class="form-check-label">Accetto i termini e condizioni</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Crea Account</button>
                  </div>
                  <div class="col-12">
                    <p class="small mb-0">Hai già un account? <a href="login.php">Accedi</a></p>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
