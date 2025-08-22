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
    $cognome = $_POST['cognome'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_raw = $_POST['password'] ?? '';
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
            // --- LOGICA DI GEOCODING AUTOMATICO ---
            // 1. Controlla se il comune selezionato ha già le coordinate
            $stmt_comune = $pdo->prepare("SELECT nome, provincia, lat, lng FROM comuni WHERE id = ?");
            $stmt_comune->execute([$comune_id]);
            $comune_selezionato = $stmt_comune->fetch();

            $lat = $comune_selezionato['lat'] ?? null;
            $lng = $comune_selezionato['lng'] ?? null;

            // 2. Se le coordinate mancano (sono NULL), le recuperiamo
            if ($comune_selezionato && (is_null($lat) || is_null($lng))) {
                $indirizzo = $comune_selezionato['nome'] . ", " . $comune_selezionato['provincia'] . ", Italia";
                $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($indirizzo) . "&format=json&limit=1";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'NoticityRegisterScript/1.0');
                $response = curl_exec($ch);
                curl_close($ch);
                $data = json_decode($response, true);

                if (!empty($data)) {
                    $lat = $data[0]['lat'];
                    $lng = $data[0]['lon'];
                    // Aggiorna la tabella comuni con le nuove coordinate
                    $update_stmt = $pdo->prepare("UPDATE comuni SET lat = ?, lng = ? WHERE id = ?");
                    $update_stmt->execute([$lat, $lng, $comune_id]);
                }
            }
            // --- FINE LOGICA DI GEOCODING ---

            // Procede con la registrazione dell'utente
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            
            // Inserisce l'utente INCLUDENDO le coordinate recuperate
            $stmt = $pdo->prepare(
                "INSERT INTO utenti (nome, cognome, email, password, comune_id, lat, lng, ruolo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nome, $cognome, $email, $password, $comune_id, $lat, $lng, $ruolo]);

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
                                            <label class="form-label">Nome</label>
                                            <input type="text" name="nome" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Cognome</label>
                                            <input type="text" name="cognome" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{7,}$" title="La password deve contenere almeno 7 caratteri, una maiuscola, una minuscola, un numero e un carattere speciale.">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Conferma Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
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
