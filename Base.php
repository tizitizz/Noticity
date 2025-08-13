<?php
$nome_comune = $provincia = $regione = '';
?>
<?php
session_start();
require 'config.php';
// --- include 'menu.php'; ---

// --- Controllo accesso robusto ---
$comune_id_param = isset($_GET['comune']) ? intval($_GET['comune']) : 0;
if (!isset($_SESSION['user_id']) && $comune_id_param <= 0) {
    header("Location: login.php");
    exit;
}
// --- Fine controllo ---


// --- LOGICA COMUNE FIXATA ---
// Supporta parametro GET ?comune=ID e sessione utente
$comune_id_param = isset($_GET['comune']) ? intval($_GET['comune']) : null;
$nome_comune = $provincia = $regione = '';

if ($comune_id_param) {
    $stmt = $pdo->prepare("SELECT nome AS nome_comune, provincia, regione FROM comuni WHERE id = ?");
    $stmt->execute([$comune_id_param]);
    $comune = $stmt->fetch();
    if ($comune) {
        $nome_comune = $comune['nome_comune'];
        $provincia = $comune['provincia'];
        $regione = $comune['regione'];
    }
} elseif (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT c.nome AS nome_comune, c.provincia, c.regione, u.comune_id
                           FROM utenti u JOIN comuni c ON u.comune_id = c.id
                           WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $nome_comune = $user['nome_comune'];
        $provincia = $user['provincia'];
        $regione = $user['regione'];
        $comune_id_param = $user['comune_id'];
    }
}
// --- FINE FIX ---


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Recupero utente e comune
$stmt = $pdo->prepare("SELECT u.*, c.nome AS nome_comune, c.provincia, c.regione FROM utenti u JOIN comuni c ON u.comune_id = c.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$nome_comune = $user['nome_comune'];
$provincia = $user['provincia'];
$regione = $user['regione'];

// Post recenti del comune
$post_stmt = $pdo->prepare("SELECT p.*, u.nome FROM post p JOIN utenti u ON p.utente_id = u.id WHERE p.comune_id = ? ORDER BY p.created_at DESC");
$post_stmt->execute([$user['comune_id']]);
$post = $post_stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="it">

<head>
 <meta charset="utf-8"/>
 <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
 <title>
  Noticity
 </title>
 <meta content="Fai sentire la tua voce nella tua città." name="description"/>
 <meta content="" name="keywords"/>
 <!-- Favicons -->
 <link href="assets/img/favicon.png" rel="icon"/>
 <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon"/>
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
 <!-- Google Fonts -->
 <link href="https://fonts.gstatic.com" rel="preconnect"/>
 <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet"/>
 <!-- Vendor CSS Files -->
 <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
 <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet"/>
 <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet"/>
 <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet"/>
 <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet"/>
 <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet"/>
 <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet"/>
 <link href="assets/css/main.min.css" rel="stylesheet"/>
 <link href="assets/css/style.css" rel="stylesheet"/>
 <link href="assets/css/color.css" rel="stylesheet"/>
 <!-- Template Main CSS File -->
 <link href="assets/css/style.css" rel="stylesheet"/>
</head>

<body>



 <!-- ======= Header ======= -->
 <header class="header fixed-top d-flex align-items-center" id="header">
  <div class="d-flex align-items-center justify-content-between">
   <a class="logo d-flex align-items-center" href="index2.html">
    <img alt="" src="assets/img/logo.png"/>
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
    <input name="query" placeholder="Search" title="Enter search keyword" type="text"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
      </li>
      <li class="message-item">
       <a href="#">
        <img alt="" class="rounded-circle" src="assets/img/messages-1.jpg"/>
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
       <hr class="dropdown-divider"/>
      </li>
      <li class="message-item">
       <a href="#">
        <img alt="" class="rounded-circle" src="assets/img/messages-2.jpg"/>
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
       <hr class="dropdown-divider"/>
      </li>
      <li class="message-item">
       <a href="#">
        <img alt="" class="rounded-circle" src="assets/img/messages-3.jpg"/>
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
       <hr class="dropdown-divider"/>
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
     <a class="nav-link nav-profile d-flex align-items-center pe-0" data-bs-toggle="dropdown" href="#">
      <img alt="Profile" class="rounded-circle" src="assets/img/resources/friend-avatar6.jpg"/>
      <span class="d-none d-md-block dropdown-toggle ps-2">
       T. Grasso
      </span>
     </a>
     <!-- End Profile Iamge Icon -->
     <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
      <li class="dropdown-header">
       <h6>
        Tiziano Grasso
       </h6>
       <span>
       </span>
      </li>
      <li>
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
       <hr class="dropdown-divider"/>
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
    <a class="nav-link" href="index2.html">
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











 <main class="main" id="main">
  












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