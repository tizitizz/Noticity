<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';



$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'] ?? '';

    if (empty($email)) {
        $error = "L'email è obbligatoria.";
    } else {
        // Controlla i tentativi di reset per questa email
        $stmt = $pdo->prepare("SELECT * FROM password_reset_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $log = $stmt->fetch();

        if ($log) {
            $attempts = $log['attempts'];
            $last_attempt_time = strtotime($log['attempt_time']);
            $current_time = time();

            // Se sono passate meno di 24 ore dal primo tentativo, verifica i tentativi
            if ($current_time - $last_attempt_time < 86400 && $attempts >= 3) {
                $error = "Hai raggiunto il limite di tentativi per il reset della password. Riprova tra 24 ore.";
            }
        }

        // Se non ci sono errori, procedi con il recupero della password
        if (!$error) {
            $stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Genera il token di reset
                $token = bin2hex(random_bytes(32));

                // Salva il token nella tabella utenti
                $stmt = $pdo->prepare("UPDATE utenti SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
                $stmt->execute([$token, $user['id']]);

                // Invia email con il link per reimpostare la password
               $reset_link = "https://www.noticity.it/reset-password.php?token=" . urlencode($token); // Crea il link di reset
                // Inizializza PHPMailer

require 'lib/PHPMailer/src/PHPMailer.php';  // Assicurati che PHPMailer sia caricato
require 'lib/PHPMailer/src/SMTP.php';
require 'lib/PHPMailer/src/Exception.php';


            $mail = new PHPMailer\PHPMailer\PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtps.aruba.it'; // Usa il server SMTP di Aruba
            $mail->SMTPAuth = true;
            $mail->Username = 'no-reply@noticity.it'; // Inserisci il tuo username SMTP
            $mail->Password = 'Milazzo00!'; // Inserisci la tua password SMTP
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;  // Usa 465 per SSL, 587 per TLS

            // Destinatario e mittente
	    $mail->isHTML(true);  // Imposta l'email come HTML
            $mail->setFrom('no-reply@noticity.it', 'Noticity');
            $mail->addAddress($email); // Email dell'utente che sta recuperando la password
            $mail->Subject = 'Reset Password - Noticity';
            $mail->Body    =  '
<html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                padding: 20px;
                color: #333;
            }
            .container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                padding: 30px;
                text-align: center;
            }
            h1 {
                color: #007bff; /*  */
            }
            p {
                font-size: 16px;
                color: #555;
            }
            .button {
                background-color: #85bfff; /*  */
                color: #ffffff;
                padding: 12px 25px;
                font-size: 16px;
                border-radius: 30px;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
            }


.button:hover {
  color: white;
  background-color: darkblue;
}

Button:active {
  color: lightblue;
  background-color: navy;
}
            .footer {
                font-size: 12px;
                color: #888;
                margin-top: 20px;
            }
            .logo {
                width: 150px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="https://www.noticity.it/assets/img/apple-touch-icon.png" alt="Noticity" class="logo">
            <h1>Reimposta la tua password</h1>
            <p>Abbiamo ricevuto una richiesta per reimpostare la tua password. Clicca il link qui sotto per creare una nuova password:</p>
            <a href="' . $reset_link . '" class="button">Reimposta la tua password</a>
            <p class="footer">Se non hai richiesto questa modifica, ignora questa email.</p>
        </div>
    </body>
    </html>
    ';




                // Invia l'email
                if(!$mail->send()) {
                    $error = 'Errore nell\'invio dell\'email: ' . $mail->ErrorInfo;
                } else {
                    $message = "Email inviata. Riceverai le istruzioni per reimpostare la password.";
                }

                // Aggiorna o inserisci il tentativo di reset
                if ($log) {
                    // Se il record esiste, aggiorna il numero di tentativi
                    $stmt = $pdo->prepare("UPDATE password_reset_attempts SET attempts = attempts + 1, attempt_time = NOW() WHERE email = ?");
                    $stmt->execute([$email]);
                } else {
                    // Se non ci sono tentativi precedenti, inserisci un nuovo record
                    $stmt = $pdo->prepare("INSERT INTO password_reset_attempts (email, attempt_time, attempts) VALUES (?, NOW(), 1)");
                    $stmt->execute([$email]);
                }
            } else {
                $error = "Email non trovata.";
            }
        }
    }
}
?>

<!-- Form di recupero password con lo stile -->
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recupera Password - Noticity</title>
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
                  <h5 class="card-title text-center pb-0 fs-4">Recupera la tua password</h5>
                </div>
                <?php if ($error): ?>
                  <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($message): ?>
                  <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3 needs-validation" novalidate>
                  <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                    <div class="invalid-feedback">Inserisci la tua email.</div>
                  </div>
                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Invia link di reset</button>
                  </div>
                  <div class="col-12">
                    <p class="small mb-0"><a href="login.php">Torna al login</a></p>
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
