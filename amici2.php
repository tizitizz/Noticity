<?php
session_start();
require 'config.php';
require 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Recupero i dati dell'utente LOGGATO per l'header e per il suo comune
$stmt_user = $pdo->prepare("SELECT u.*, c.nome as nome_comune, c.provincia, c.regione FROM utenti u JOIN comuni c ON u.comune_id = c.id WHERE u.id = ?");
$stmt_user->execute([$current_user_id]);
$user = $stmt_user->fetch();
$foto_url = $user['foto_profilo'] ? "assets/img/utenti/" . $user['foto_profilo'] : "assets/img/utenti/default.jpg";
$user_comune_id = $user['comune_id'];

// --- LOGICA PER I CONTATORI E LE LISTE DELLE TAB ---

// 1. Amici
$stmt_amici = $pdo->prepare("SELECT u.id FROM amicizie a JOIN utenti u ON u.id = IF(a.utente_richiedente_id = ?, a.utente_destinatario_id, a.utente_richiedente_id) WHERE (a.utente_richiedente_id = ? OR a.utente_destinatario_id = ?) AND a.stato = 'accepted'");
$stmt_amici->execute([$current_user_id, $current_user_id, $current_user_id]);
$amici = $stmt_amici->fetchAll();
$conteggio_amici = count($amici);

// 2. Richieste Ricevute
$stmt_richieste = $pdo->prepare("SELECT a.id FROM amicizie a WHERE a.utente_destinatario_id = ? AND a.stato = 'pending'");
$stmt_richieste->execute([$current_user_id]);
$richieste = $stmt_richieste->fetchAll();
$conteggio_richieste = count($richieste);

// 3. Suggeriti (per ora contiamo tutti gli utenti del comune da cui escludere amici, richieste e se stesso)
$stmt_relazioni = $pdo->prepare("SELECT utente_richiedente_id, utente_destinatario_id FROM amicizie WHERE utente_richiedente_id = :current_user_id OR utente_destinatario_id = :current_user_id");
$stmt_relazioni->execute(['current_user_id' => $current_user_id]);
$relazioni_esistenti = $stmt_relazioni->fetchAll();
$ids_da_escludere = [$current_user_id];
foreach($relazioni_esistenti as $rel) {
    $ids_da_escludere[] = ($rel['utente_richiedente_id'] == $current_user_id) ? $rel['utente_destinatario_id'] : $rel['utente_richiedente_id'];
}
$placeholders = implode(',', array_fill(0, count($ids_da_escludere), '?'));
$sql_suggeriti_count = "SELECT COUNT(id) FROM utenti WHERE comune_id = ? AND id NOT IN ($placeholders)";
$params = array_merge([$user_comune_id], $ids_da_escludere);
$stmt_suggeriti_count = $pdo->prepare($sql_suggeriti_count);
$stmt_suggeriti_count->execute($params);
$conteggio_suggeriti = $stmt_suggeriti_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Amici - Noticity</title>
    
    <link href="assets/img/favicon.png" rel="icon" />
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet" />
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet" />
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet" />
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Rete Sociale</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Amici</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    
                    <div class="d-flex align-items-center justify-content-between pt-3 pb-3">
                        <div class="search-bar-friends flex-grow-1">
                            <form class="search-form d-flex align-items-center">
                                <input type="text" name="query" placeholder="Cerca persone per nome, comune o professione..." class="form-control">
                                <button type="submit" class="btn btn-primary ms-2"><i class="bi bi-search"></i></button>
                            </form>
                        </div>
                        <div class="filter-menu ms-3">
                             <div class="post-menu"> <button class="btn btn-outline-secondary menu-btn"><i class="bi bi-filter"></i></button>
                                <div class="menu-dropdown">
                                    <h6 class="dropdown-header">Filtra per</h6>
                                    <a href="#">Solo Amici</a>
                                    <a href="#">Tutti</a>
                                    <div class="dropdown-divider"></div>
                                    <div class="px-3 py-2">
                                        <label for="comuneFilter" class="form-label">Comune</label>
                                        <input type="text" class="form-control" id="comuneFilter" placeholder="Nome comune...">
                                    </div>
                                     <div class="px-3 py-2">
                                        <label for="professioneFilter" class="form-label">Professione</label>
                                        <input type="text" class="form-control" id="professioneFilter" placeholder="Es. Elettricista">
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <div class="px-3 py-2 text-end">
                                        <button class="btn btn-primary btn-sm">Applica</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-bordered">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-amici">
                                Amici <span class="badge bg-secondary ms-1"><?php echo $conteggio_amici; ?></span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-suggeriti">
                                Suggeriti <span class="badge bg-secondary ms-1"><?php echo $conteggio_suggeriti; ?></span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-richieste">
                                Richieste <span class="badge bg-danger ms-1"><?php echo $conteggio_richieste; ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2">
                        <div class="tab-pane fade show active" id="tab-amici"><p class="p-3">Qui verrà visualizzata la lista dei tuoi amici...</p></div>
                        <div class="tab-pane fade" id="tab-suggeriti"><p class="p-3">Qui verrà visualizzata la lista degli utenti suggeriti...</p></div>
                        <div class="tab-pane fade" id="tab-richieste"><p class="p-3">Qui verrà visualizzata la lista delle tue richieste di amicizia...</p></div>
                    </div></div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        const currentUserID = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>