<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM utenti WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Verifica se le password corrispondono
            if ($password !== $confirm_password) {
                $error = "Le password non corrispondono.";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{7,}$/', $password)) {
                // Verifica la complessità della password
                $error = "La password deve contenere almeno 7 caratteri, una maiuscola, una minuscola, un numero e un carattere speciale.";
            } else {
                // Se le password corrispondono e sono valide, aggiorna la password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utenti SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);

                // Effettua il login automatico
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit;
            }
        }
    } else {
        $error = "Token non valido o scaduto.";
    }
} else {
    $error = "Token mancante.";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Noticity</title>
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
                  <h5 class="card-title text-center pb-0 fs-4">Reimposta Password</h5>
                </div>
                <?php if ($error): ?>
                  <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3 needs-validation" novalidate>
                  <div class="col-12">
                    <label class="form-label">Nuova Password</label>
                    <input type="password" name="password" class="form-control" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{7,}$" title="La password deve contenere almeno 7 caratteri, una maiuscola, una minuscola, un numero e un carattere speciale.">
                    <div class="invalid-feedback">Inserisci la tua nuova password.</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Conferma Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                    <div class="invalid-feedback">Conferma la tua nuova password.</div>
                  </div>
                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Reimposta Password</button>
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
