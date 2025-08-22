<?php
session_start();
require 'config.php';
require 'includes/functions.php';

// Requisito: l'utente deve essere loggato per vedere qualsiasi profilo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ID dell'utente attualmente loggato
$current_user_id = $_SESSION['user_id'];

// Determina quale profilo visualizzare:
// Se c'è un ?id=... nell'URL, usa quello. Altrimenti, mostra il profilo dell'utente loggato.
$profilo_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

// 1. Recupera i dati dell'UTENTE DEL PROFILO che stiamo visitando
$stmt_profile = $pdo->prepare("SELECT u.*, c.nome AS nome_comune FROM utenti u JOIN comuni c ON u.comune_id = c.id WHERE u.id = ?");
$stmt_profile->execute([$profilo_id]);
$user_profile = $stmt_profile->fetch();

// Se l'ID non corrisponde a nessun utente, la pagina si ferma
if (!$user_profile) {
    // Puoi creare una pagina di errore 404 o mostrare un messaggio
    die("Utente non trovato.");
}

// 2. Recupera i dati dell'UTENTE LOGGATO (servono per l'header e per i permessi)
$stmt_logged_in = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt_logged_in->execute([$current_user_id]);
$user = $stmt_logged_in->fetch(); // Usiamo la variabile $user per coerenza con il tuo header
$foto_url = $user_profile['foto_profilo'] ? "assets/img/utenti/" . $user_profile['foto_profilo'] : "assets/img/utenti/default.jpg";
$foto_userlog = $user['foto_profilo'] ? "assets/img/utenti/" . $user['foto_profilo'] : "assets/img/utenti/default.jpg";

// 3. Carica i post scritti dall'utente del profilo che stiamo visitando
$post_stmt = $pdo->prepare("
    SELECT p.*, u.nome AS nome_utente, u.cognome AS cognome_utente, 
           u.foto_profilo, p.image_path_post
    FROM post p
    JOIN utenti u ON p.utente_id = u.id
    WHERE p.utente_id = ?
    ORDER BY p.created_at DESC
");
$post_stmt->execute([$profilo_id]);
$posts = $post_stmt->fetchAll();

// 4. Carica la lista amici dell'utente del profilo che stiamo visitando
$sql_amici = "
    SELECT u.id, u.nome, u.cognome, u.foto_profilo
    FROM amicizie a
    JOIN utenti u ON u.id = IF(a.utente_richiedente_id = ?, a.utente_destinatario_id, a.utente_richiedente_id)
    WHERE (a.utente_richiedente_id = ? OR a.utente_destinatario_id = ?) AND a.stato = 'accepted'
";
$stmt_amici = $pdo->prepare($sql_amici);
$stmt_amici->execute([$profilo_id, $profilo_id, $profilo_id]);
$amici = $stmt_amici->fetchAll();

// 5. Carica le richieste di amicizia in attesa SOLO SE stai guardando il TUO profilo
$richieste_ricevute = [];
if ($current_user_id == $profilo_id) {
    $sql_richieste = "
        SELECT a.id as richiesta_id, u.id as utente_id, u.nome, u.cognome, u.foto_profilo
        FROM amicizie a
        JOIN utenti u ON u.id = a.utente_richiedente_id
        WHERE a.utente_destinatario_id = ? AND a.stato = 'pending'
    ";
    $stmt_richieste = $pdo->prepare($sql_richieste);
    $stmt_richieste->execute([$current_user_id]);
    $richieste_ricevute = $stmt_richieste->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Noticity</title>

    <meta content="Fai sentire la tua voce nella tua città." name="description" />
    <meta content="" name="keywords" />
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon" />
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet" />
    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet" />
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet" />
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet" />
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet" />
    <link href="assets/css/main.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/color.css" rel="stylesheet" />
    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet" />

    <style>
        .profile-img-container-wrapper {
            display: flex;
            align-items: left;

        }



        .profile-img-container {
            width: 110px;
            /* Imposta la larghezza del contenitore */
            height: 110px;
            /* Imposta l'altezza del contenitore */
            border-radius: 50%;
            /* Rende il contenitore rotondo */
            overflow: hidden;
            /* Nasconde la parte dell'immagine che esce dal contenitore */
            margin-right: 20px;
            /* Spazio tra l'immagine e la barra di caricamento */

            /*     position: relative;         Permette di usare il positioning per centrare l'immagine */
            display: flex;
            /* Per centrare l'immagine orizzontalmente e verticalmente */
            justify-content: center;
            /* Centra l'immagine orizzontalmente */
            align-items: center;
            /* Centra l'immagine verticalmente */
            /*    margin: 0 auto;            /* Centra il contenitore nella pagina */

        }

        .profile-img {
            width: 100%;
            /* L'immagine coprirà tutta la larghezza del contenitore */
            height: 100%;
            /* L'immagine coprirà tutta l'altezza del contenitore */
            object-fit: cover;
            /* L'immagine manterrà il rapporto senza deformarsi */
            object-position: center;
            /* Centra l'immagine orizzontalmente e verticalmente */
        }

        /* Barra di caricamento e messaggio */
        .profile-upload {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Modifica la struttura dei form per evitare che vengano influenzati dal flex */
        .form-label {
            text-align: left;
            /* Assicura che le etichette siano allineate a sinistra */
        }

        /* Torna alla struttura normale per tutti gli altri input */
        .form-control {
            width: 100%;
            /* Mantieni la larghezza dell'input per gli altri campi */
        }

    </style>


</head>

<body>



    <!-- ======= Header ======= -->
    <header class="header fixed-top d-flex align-items-center" id="header">
        <div class="d-flex align-items-center justify-content-between">
            <a class="logo d-flex align-items-center" href="index.php">
                <img alt="" src="assets/img/logo.png" />
                <span class="d-none d-lg-block">
                    Noticity
                </span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn">
            </i>
        </div>
        <!-- End Logo -->
        <div class="search-bar">
            <form action="#" class="search-form d-flex align-items-center" method="POST">
                <input name="query" placeholder="Search" title="Enter search keyword" type="text" />
                <button title="Search" type="submit">
                    <i class="bi bi-search">
                    </i>
                </button>
            </form>
        </div>
        <!-- End Search Bar -->
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle" href="#">
                        <i class="bi bi-search">
                        </i>
                    </a>
                </li>
                <!-- End Search Icon-->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-bell">
                        </i>
                        <span class="badge bg-primary badge-number">
                            3
                        </span>
                    </a>
                    <!-- End Notification Icon -->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <li class="dropdown-header">
                            Hai 3 nuove notifiche
                            <a href="#">
                                <span class="badge rounded-pill bg-primary p-2 ms-2">
                                    Vedi tutte
                                </span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="notification-item">
                            <i class="bi bi-exclamation-circle text-warning">
                            </i>
                            <div>
                                <h4>
                                    Nuova segnalazione
                                </h4>
                                <p>
                                    Gaia Luini ha appena pubblicato una segnalazione
                                </p>
                                <p>
                                    30 minuti fa
                                </p>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="notification-item">
                            <i class="bi bi-lightbulb text-success">
                            </i>
                            <div>
                                <h4>
                                    Nuova proposta
                                </h4>
                                <p>
                                    Paolo Veneti ha appena pubblicato una proposta
                                </p>
                                <p>
                                    1 ora fa
                                </p>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="notification-item">
                            <i class="bi bi-person-plus text-primary">
                            </i>
                            <div>
                                <h4>
                                    Nuova richiesta d'amicizia
                                </h4>
                                <p>
                                    Matteo Fragano ti ha mandato una richiesta d'amicizia
                                </p>
                                <p>
                                    2 ore fa
                                </p>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="dropdown-footer">
                            <a href="#">
                                Visualizza tutte le notifiche
                            </a>
                        </li>
                    </ul>
                    <!-- End Notification Dropdown Items -->
                </li>
                <!-- End Notification Nav -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-chat-left-text">
                        </i>
                        <span class="badge bg-success badge-number">
                            3
                        </span>
                    </a>
                    <!-- End Messages Icon -->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                        <li class="dropdown-header">
                            Hai 3 nuovi messaggi da leggere
                            <a href="#">
                                <span class="badge rounded-pill bg-primary p-2 ms-2">
                                    Vedi tutti
                                </span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="message-item">
                            <a href="#">
                                <img alt="" class="rounded-circle" src="assets/img/messages-1.jpg" />
                                <div>
                                    <h4>
                                        Maddalena Bizzi
                                    </h4>
                                    <p>
                                        Ciao sono qui da poco, sai indicarmi dove si trova la casetta dell'acqua? Ti ringr...
                                    </p>
                                    <p>
                                        4 ore fa
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="message-item">
                            <a href="#">
                                <img alt="" class="rounded-circle" src="assets/img/messages-2.jpg" />
                                <div>
                                    <h4>
                                        Giorgia Lentini
                                    </h4>
                                    <p>
                                        Hai visto la nuova pista cilabile in via Roma?
                                    </p>
                                    <p>
                                        6 ore fa
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="message-item">
                            <a href="#">
                                <img alt="" class="rounded-circle" src="assets/img/messages-3.jpg" />
                                <div>
                                    <h4>
                                        Guido Tommasi
                                    </h4>
                                    <p>
                                        Ti lascio link così puoi vedere tutti i dettagli!
                                    </p>
                                    <p>
                                        9 ore fa
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li class="dropdown-footer">
                            <a href="#">
                                Visualizza tutti i messaggi
                            </a>
                        </li>
                    </ul>
                    <!-- End Messages Dropdown Items -->
                </li>
                <!-- End Messages Nav -->
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <img src="<?php echo htmlspecialchars($foto_userlog); ?>" alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                        <span class="d-none d-md-block dropdown-toggle ps-2">
                            <?php echo htmlspecialchars(substr($user['nome'], 0, 1)); ?>. <?php echo htmlspecialchars($user['cognome']); ?>
                        </span>
                    </a>
                    <!-- End Profile Iamge Icon -->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">

                        <li class="dropdown-header">
                            <h6>
                                <?php echo htmlspecialchars($user['nome']) . ' ' . htmlspecialchars($user['cognome']); ?>
                            </h6>
                        </li>

                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="profilo_utente.php">
                                <i class="bi bi-person">
                                </i>
                                <span>
                                    Il mio profilo
                                </span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="profilo_utente.php">
                                <i class="bi bi-gear">
                                </i>
                                <span>
                                    Impostazioni
                                </span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                                <i class="bi bi-question-circle">
                                </i>
                                <span>
                                    Hai bisogno d'aiuto?
                                </span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center custom-logout" href="logout.php" onclick="return confirm('Vuoi disconnetterti?');" style="color: #444 !important; font-size: 14px !important; text-decoration: none !important;">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <span>Disconnetti</span>
                            </a>
                        </li>
                    </ul>
                    <!-- End Profile Dropdown Items -->
                </li>
                <!-- End Profile Nav -->
            </ul>
        </nav>
        <!-- End Icons Navigation -->
    </header>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-grid">
                    </i>
                    <span>
                        Home
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="comunicazioni-comune.html">
                    <i class="bi bi-menu-button-wide">
                    </i>
                    <span>
                        Comunicazioni comune
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-menu-button-wide">
                    </i>
                    <span>
                        Notizie
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-calendar-date">
                    </i>
                    <span>
                        Eventi
                    </span>
                    <i class="bi bi-chevron-down ms-auto">
                    </i>
                </a>
                <ul class="nav-content collapse" data-bs-parent="#sidebar-nav" id="forms-nav">
                    <li>
                        <a href="forms-elements.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                Form Elements
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-layouts.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                Form Layouts
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-editors.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                Form Editors
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-validation.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                Form Validation
                            </span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-layout-text-window-reverse">
                    </i>
                    <span>
                        Attività commerciali
                    </span>
                    <i class="bi bi-chevron-down ms-auto">
                    </i>
                </a>
                <ul class="nav-content collapse" data-bs-parent="#sidebar-nav" id="tables-nav">
                    <li>
                        <a href="tables-general.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                General Tables
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="tables-data.html">
                            <i class="bi bi-circle">
                            </i>
                            <span>
                                Data Tables
                            </span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-asterisk">
                    </i>
                    <span>
                        Farmacia
                    </span>
                </a>
            </li>
            <!-- End Charts Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-type">
                    </i>
                    <span>
                        Scuola
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bicycle">
                    </i>
                    <span>
                        Sport
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-trash">
                    </i>
                    <span>
                        Rifiuti
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bus-front">
                    </i>
                    <span>
                        Trasporti
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-cloud-sun">
                    </i>
                    <span>
                        Meteo
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-folder2-open">
                    </i>
                    <span>
                        Documenti
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-houses">
                    </i>
                    <span>
                        Case in vendita
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-briefcase">
                    </i>
                    <span>
                        Offerte di lavoro
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-flower3">
                    </i>
                    <span>
                        Necrologi
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-chat-right-dots">
                    </i>
                    <span>
                        Segnalazioni
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-question-circle">
                    </i>
                    <span>
                        Come fare per...
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-telephone">
                    </i>
                    <span>
                        Numeri utili
                    </span>
                </a>
            </li>
            <!-- End Icons Nav -->
        </ul>
    </aside>
    <!-- End Sidebar-->






    <!-- Main Content -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Profilo di <?php echo htmlspecialchars($user_profile['nome']); ?> <?php echo htmlspecialchars($user_profile['cognome']); ?></h1>
        </div>

        <section class="section profile">
            <div class="row">
                <div class="col-xl-4">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                            <div class="profile-img-container">

                                <?php 
                            // Immagine del profilo con il nuovo percorso
                            $image_path = 'assets/img/utenti/' . htmlspecialchars($user_profile['foto_profilo'] ?? 'default.jpg');
                            if (file_exists($image_path)) {

                                echo '<img src="' . $image_path . '" alt="Profile" class="profile-img">';

                            } else {
                                // Se l'immagine non esiste, usa un'immagine predefinita

                                echo '<img src="assets/img/utenti/default.jpg" alt="Profile" class="profile-img">';
                            }
                        ?>
                            </div>
                            <h2><?php echo htmlspecialchars($user_profile['nome']); ?> <?php echo htmlspecialchars($user_profile['cognome']); ?></h2>

                            <?php if (!empty($user_profile['professione'])): ?>
                            <h3><?php echo htmlspecialchars($user_profile['professione']); ?></h3>
                            <?php else: ?>
                            <h3></h3> <!-- Vuoto se la professione non è specificata -->
                            <?php endif; ?>

                            <div class="social-links mt-2">
                                <a href="<?php echo htmlspecialchars($user_profile['twitter'] ?? '#'); ?>" class="twitter"><i class="bi bi-twitter"></i></a>
                                <a href="<?php echo htmlspecialchars($user_profile['facebook'] ?? '#'); ?>" class="facebook"><i class="bi bi-facebook"></i></a>
                                <a href="<?php echo htmlspecialchars($user_profile['instagram'] ?? '#'); ?>" class="instagram"><i class="bi bi-instagram"></i></a>
                                <a href="<?php echo htmlspecialchars($user_profile['linkedin'] ?? '#'); ?>" class="linkedin"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <!-- Profile Edit Form -->
                    <div class="card">
                        <div class="card-body pt-3">
                            <!-- Bordered Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bordered">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Panoramica</button>
                                </li>

                                <?php // Mostra la tab "Modifica Profilo" solo se l'utente sta guardando il proprio profilo ?>
                                <?php if ($current_user_id == $profilo_id): ?>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Modifica Profilo</button>
                                </li>
                                <?php endif; ?>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-friends">Amici</button>
                                </li>
                            </ul>

                            <div class="tab-content pt-2">
                                <!-- Profile Overview Tab -->
                                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Nome completo</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_profile['nome']); ?> <?php echo htmlspecialchars($user_profile['cognome']); ?></div>
                                    </div>
                                    <?php if ($current_user_id == $profilo_id): ?>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Email</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_profile['email']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Professione</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_profile['professione'] ?? 'Non specificato'); ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Comune</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_profile['nome_comune']); ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Data di nascita</div>
                                        <div class="col-lg-9 col-md-8">
                                            <?php
                                            // Se la data di nascita non è specificata, mostra "Non specificata"
                                            echo !empty($user_profile['data_nascita']) ? htmlspecialchars(date('d/m/Y', strtotime($user_profile['data_nascita']))) : 'Non specificata';
                                        ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Profile Tab -->
                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                                    <form method="post" action="update_profile.php" enctype="multipart/form-data">










                                        <div class="row mb-3">
                                            <label for="Name" class="col-md-4 col-lg-3 col-form-label">Nome</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input type="text" name="Name" class="form-control" id="Name" value="<?php echo htmlspecialchars($user_profile['nome']); ?>" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Cognome" class="col-md-4 col-lg-3 col-form-label">Cognome</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input type="text" name="Cognome" class="form-control" id="Cognome" value="<?php echo htmlspecialchars($user_profile['cognome']); ?>" />
                                            </div>
                                        </div>



                                        <div class="row mb-3">
                                            <label for="comune" class="col-md-4 col-lg-3 col-form-label">Città</label>
                                            <div class="col-md-8 col-lg-9">
                                                <select name="comune" class="form-select" id="comune">
                                                    <?php
           					 // Recupera tutte le città (comuni) dalla tabella comuni
     					         $stmt = $pdo->prepare("SELECT id, nome FROM comuni ORDER BY nome");
         				         $stmt->execute();
         				         $comuni = $stmt->fetchAll();
        				         foreach ($comuni as $comune) {
                    		   	        // Seleziona la città attuale dell'utente
          				         $selected = ($user_profile['comune_id'] == $comune['id']) ? 'selected' : '';
       	  		    	                echo "<option value=\"" . htmlspecialchars($comune['id']) . "\" $selected>" . htmlspecialchars($comune['nome']) . "</option>";
    					        }
 				               ?>
                                                </select>
                                            </div>
                                        </div>



                                        <div class="row mb-3">
                                            <label for="profession" class="col-md-4 col-lg-3 col-form-label">Professione</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input type="text" name="profession" class="form-control" id="profession" value="<?php echo htmlspecialchars($user_profile['professione'] ?? ''); ?>" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="birthDate" class="col-md-4 col-lg-3 col-form-label">Data di nascita</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input type="date" name="birthDate" class="form-control" id="birthDate" value="<?php echo htmlspecialchars($user_profile['data_nascita'] ?? ''); ?>" />
                                            </div>
                                        </div>





                                        <div class="row mb-3">
                                            <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Immagine Profilo</label>
                                            <div class="col-md-8 col-lg-9">


                                                <div class="profile-img-container-wrapper">
                                                    <!-- Immagine del profilo -->
                                                    <div class="profile-img-container">
                                                        <?php 
                    $profile_image = $user_profile['foto_profilo'] ?? 'default.jpg';
                    echo '<img src="assets/img/utenti/' . htmlspecialchars($profile_image) . '" alt="Profile" class="profile-img">';
                ?>
                                                    </div>

                                                    <!-- Barra di caricamento e messaggio -->
                                                    <div class="profile-upload">
                                                        <input type="file" name="profileImage" class="form-control" />
                                                        <small class="text-muted">Le dimensioni massime del file sono 2 MB.</small>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-primary">Salva modifiche</button>
                                                </div>


                                            </div>


                                        </div>


                                </div>
                                <!-- Friend tab -->
                                <div class="tab-pane fade pt-3" id="profile-friends">

                                    <?php // Mostra le richieste di amicizia solo se l'utente sta guardando il proprio profilo ?>
                                    <?php if ($current_user_id == $profilo_id && !empty($richieste_ricevute)): ?>
                                    <h5 class="card-title">Richieste di amicizia in attesa</h5>
                                    <div class="friend-requests-list">
                                        <?php foreach ($richieste_ricevute as $richiesta): ?>
                                        <div class="friend-item" id="richiesta-<?php echo $richiesta['richiesta_id']; ?>">
                                            <div class="friend-info">
                                                <img src="<?php echo !empty($richiesta['foto_profilo']) ? 'assets/img/utenti/'.$richiesta['foto_profilo'] : 'assets/img/utenti/default.jpg'; ?>" alt="Profilo">
                                                <a href="profilo_utente.php?id=<?php echo $richiesta['utente_id']; ?>">
                                                    <?php echo htmlspecialchars($richiesta['nome'] . ' ' . $richiesta['cognome']); ?>
                                                </a>
                                            </div>
                                            <div class="friend-actions">
                                                <button class="btn btn-primary btn-sm btn-conferma-amicizia" data-richiesta-id="<?php echo $richiesta['richiesta_id']; ?>">Conferma</button>
                                                <button class="btn btn-secondary btn-sm btn-rifiuta-amicizia" data-richiesta-id="<?php echo $richiesta['richiesta_id']; ?>">Rifiuta</button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <hr>
                                    <?php endif; ?>

                                    <h5 class="card-title">Elenco Amici (<?php echo count($amici); ?>)</h5>
                                    <div class="friends-list">
                                        <?php if (!empty($amici)): ?>
                                        <?php foreach ($amici as $amico): ?>
                                        <div class="friend-item" id="amico-<?php echo $amico['id']; ?>">
                                            <div class="friend-info">
                                                <img src="<?php echo !empty($amico['foto_profilo']) ? 'assets/img/utenti/'.$amico['foto_profilo'] : 'assets/img/utenti/default.jpg'; ?>" alt="Profilo">
                                                <a href="profilo_utente.php?id=<?php echo $amico['id']; ?>">
                                                    <?php echo htmlspecialchars($amico['nome'] . ' ' . $amico['cognome']); ?>
                                                </a>
                                            </div>
                                            <div class="friend-actions">
                                                <?php if ($current_user_id == $profilo_id): ?>
                                                <button class="btn btn-danger btn-sm btn-rimuovi-amico" data-amico-id="<?php echo $amico['id']; ?>">Rimuovi</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <p>Nessun amico da mostrare.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
        </section>
    </main>





    <!-- End #main -->










    <!-- ======= Footer ======= -->
    <footer class="footer" id="footer">
        <div class="copyright">
            © Copyright
            <strong>
                <span>
                    Noticity
                </span>
            </strong>
            . All Rights Reserved
        </div>
        <div class="credits">
        </div>
    </footer>
    <!-- End Footer -->
    <a class="back-to-top d-flex align-items-center justify-content-center" href="#">
        <i class="bi bi-arrow-up-short">
        </i>
    </a>
    <!-- Vendor JS Files -->

    <!-- <script src="assets/js/main.min.js">
 </script>
 <script src="assets/js/script.js">
 </script>
 <script src="assets/js/map-init.js">
 </script>  -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js">
    </script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js">
    </script>
    <script src="assets/vendor/chart.js/chart.umd.js">
    </script>
    <script src="assets/vendor/echarts/echarts.min.js">
    </script>
    <script src="assets/vendor/quill/quill.min.js">
    </script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js">
    </script>
    <script src="assets/vendor/tinymce/tinymce.min.js">
    </script>
    <script src="assets/vendor/php-email-form/validate.js">
    </script>
    <!-- Template Main JS File -->
    <script src="assets/js/main.js">
    </script>



</body>

</html>
