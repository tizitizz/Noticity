<!DOCTYPE html>
<html lang="it">
<?php
$nome_comune = $provincia = $regione = '';
?>
<?php
session_start();
require 'config.php';
// --- include 'menu.php'; ---
require 'includes/functions.php';

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

// Post recenti del comune (con nome, cognome e foto profilo aggiornati)
$post_stmt = $pdo->prepare("
    SELECT p.*, u.nome AS nome_utente, u.cognome AS cognome_utente, 
           u.foto_profilo, p.image_path_post
    FROM post p
    JOIN utenti u ON p.utente_id = u.id
    WHERE p.comune_id = ?
    ORDER BY p.created_at DESC
");
$post_stmt->execute([$user['comune_id']]);
$post = $post_stmt->fetchAll();


// Recupera foto utente
$query = "SELECT foto_profilo FROM utenti WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$foto_profilo = $stmt->fetchColumn(); // Restituisce solo la colonna 'foto_profilo'

if ($foto_profilo) {
    $foto_url = "assets/img/utenti/" . $foto_profilo; // Percorso relativo alla cartella dove sono salvate le immagini
} else {
    $foto_url = "assets/img/utenti/default.jpg"; // Foto di default se l'utente non ha una foto
}

?>

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icofont/1.0.1/icofont.css">

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
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet"/>

    <style>
.profile-img-container-wrapper {
    display: flex;

align-items: center;
}

.profile-img-container {
    width: 80px;          /* Imposta la larghezza del contenitore */
    height: 80px;         /* Imposta l'altezza del contenitore */
    border-radius:50%!important;    /* Rende il contenitore rotondo */
overflow: hidden;          /* Nasconde la parte dell'immagine che esce dal contenitore */
margin-top: -15px;
margin-bottom: -15px;
 margin-right: -10px;   
margin-left: 25px;
/*     position: relative;         Permette di usare il positioning per centrare l'immagine */
    display: flex;             /* Per centrare l'immagine orizzontalmente e verticalmente */
    justify-content: center;   /* Centra l'immagine orizzontalmente */
    align-items: center;       /* Centra l'immagine verticalmente */
/*    margin: 0 auto;            /* Centra il contenitore nella pagina */

}

.profile-img {
    width: 100%;           /* L'immagine coprirà tutta la larghezza del contenitore */
    height: 100%;          /* L'immagine coprirà tutta l'altezza del contenitore */
    object-fit: cover;     /* L'immagine manterrà il rapporto senza deformarsi */
object-position: center; /* Centra l'immagine orizzontalmente e verticalmente */
}


#cropperContainer {
    display: none; /* Nascondi inizialmente */
    position: fixed; /* Posizione fissa sopra al contenuto */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Sfondo semi-trasparente */
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Assicurati che il cropper sia sopra gli altri elementi */
}

#cropperImage {
    max-width: 100%;
    max-height: 80%; /* Limita l'altezza massima dell'immagine */
}

#cropImageBtn {
    position: absolute;
    top: 80%;
    left: 50%;
    transform: translateX(-50%);
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}

</style>



</head>

<body>



 <!-- ======= Header ======= -->
 <header class="header fixed-top d-flex align-items-center" id="header">
  <div class="d-flex align-items-center justify-content-between">
   <a class="logo d-flex align-items-center" href="index.php">
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
                <li class="nav-item">
                    <a class="nav-link nav-icon" href="amici.php" title="Amici">
                        <i class="bi bi-people"></i>
                    </a>
                </li>
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
     <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
    <img src="<?php echo htmlspecialchars($foto_url); ?>" alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
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






 <main class="main" id="main">
  <div class="pagetitle">
   <h1>
    <?php echo htmlspecialchars($nome_comune); ?>
   </h1>
   <nav>
    <ol class="breadcrumb">
     <li class="breadcrumb-item">
      <a href="index.html">
       Italia
      </a>
     </li>
     <li class="breadcrumb-item">
      <a href="index.html">
       <?php echo htmlspecialchars($regione); ?>
      </a>
     </li>
     <li class="breadcrumb-item">
      <a href="index.html">
       <?php echo htmlspecialchars($provincia); ?>
      </a>
     </li>
     <li class="breadcrumb-item active">
      <?php echo htmlspecialchars($nome_comune); ?>
     </li>
    </ol>
   </nav>
  </div>
  <!-- End Page Title -->
  <section class="section dashboard">





    
      

   <div class="row">
    <!-- Left side columns -->
    <div class="col-lg-8">
     <div class="row">
      <div class="col-12">
       <!-- add post new box -->
       <div class="card" style="position:relative; margin-bottom:15px;">
         <div class="central-meta item">     
	    
	  <div class="friend-info" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
           	<div style="width:50px; height:50px; border-radius:50%; overflow:hidden; flex-shrink:0;">
            	<img src="<?php echo $foto_url; ?>" alt="Foto Profilo" class="profile-img">
           	</div>

	    <div>
                <a href="profilo_utente.php" title="" style="color:#007bff; font-weight:bold; font-size:18px; text-decoration:none;"><?php echo htmlspecialchars($user['nome']); ?> <?php echo htmlspecialchars($user['cognome']); ?></a>
                
            </div>     
          </div>

          <div class="newpst-input">
          <form method="post" action="crea_post.php" enctype="multipart/form-data" class="form-pubblica" onsubmit="prependTipoToContenuto()" style="padding: 1px;">
            <!-- Campo per scegliere tipo di post -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <select class="form-select" id="tipoPost" name="tipo" required>
                        <option value="" disabled selected>Scegli tipo di post</option>
                        <option value="proposta">Proposta</option>
                        <option value="segnalazione">Segnalazione</option>
                    </select>
                </div>
            </div>

<!-- Campo per scrivere il contenuto del post -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <textarea class="form-control" id="contenuto" name="contenuto" rows="2" placeholder="Propongo / Segnalo" required></textarea>
                </div>
            </div>
	    <div style="position: relative;">
            	<label for="image_post" class="upload-icon" style="position: absolute; bottom: 14px; right: 10px; cursor: pointer;">
            	<i class="fa fa-camera"></i>
            	</label>

<!-- Input file per caricare l'immagine -->
    	   	<input type="file" name="image_post" id="image_post" style="display: none;" accept="image/*" onchange="showPreview(event)">
	   </div>

<!-- Contenitore per il Cropper (da visualizzare dopo la selezione dell'immagine) -->
	   <div id="cropperContainer" style="display: none;">
    	      <img id="cropperImage" src="" alt="Immagine da ritagliare"/>
       	      <button id="cropImageBtn">Ritaglia Immagine</button>
           </div>

<!-- Anteprima immagine -->
	   <div id="imagePreview" style="margin-top: 10px; display: none;">
   	      <img id="previewImg" src="" alt="Anteprima immagine" style="max-width: 100%; height: auto;"/>
           </div>

<!-- Campo nascosto per immagine ritagliata -->
           <input type="hidden" id="hiddenImageData" name="image_post_base64">

	   <div class="attachments">
               <style>
                 .attachments {
 	          border: none;
 	          box-shadow: none;
	       </style>
               <button type="submit" id="publishButton" class="btn btn-primary w-100">Pubblica</button>              
           </div>
	  </form>
         </div>
	</div> 
      </div>
          
    
  
	

<!-- Post recenti  -->
   
<?php foreach ($post as $p): ?>
<div class="card" style="position:relative; margin-bottom:15px;">

	<div class="post-header-right">
	   <?php $tipoPost = !empty($p['tipo']) ? $p['tipo'] : 'proposta'; ?>
		<span class="badge-post <?php echo ($tipoPost === 'segnalazione') ? 'badge-segnalazione' : 'badge-proposta'; ?>">
		    <?php echo ucfirst($tipoPost); ?>
		</span>

   	    <div class="post-menu">
               <button class="menu-btn">⋮</button>
        	<div class="menu-dropdown">
           	    <?php if ($_SESSION['user_id'] == $p['utente_id']): ?>
       			<a href="javascript:void(0);" class="delete-post-btn" data-id="<?php echo $p['id']; ?>">Elimina</a>
  		    <?php endif; ?>
           	 <a href="javascript:void(0);" class="report-post-btn" data-id="<?php echo $p['id']; ?>">Segnala</a>
        	</div>
    	   </div>
	 </div>


   <div class="central-meta item">
        
        <!-- BLOCCO FOTO + NOME -->
        <div class="friend-info" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
            <div style="width:50px; height:50px; border-radius:50%; overflow:hidden; flex-shrink:0;">
                <img src="<?php echo !empty($p['foto_profilo']) ? 'assets/img/utenti/'.$p['foto_profilo'] : 'assets/img/utenti/default.jpg'; ?>" 
                     alt="Foto Profilo" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div>
                <a href="profilo_utente.php?id=<?php echo $p['utente_id']; ?>" 
                   style="color:#007bff; font-weight:bold; font-size:16px; text-decoration:none;">
                    <?php echo htmlspecialchars($p['nome_utente']) . ' ' . htmlspecialchars($p['cognome_utente']); ?>
                </a><br>
                <span style="font-size:12px; color:#6c757d;">Pubblicato: <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></span>
            </div>
        </div>



        <!-- CONTENUTO POST -->

        <div style="font-size:14px; margin-bottom:10px;">
            <?php echo nl2br(htmlspecialchars($p['contenuto'])); ?>
        </div>

        <!-- IMMAGINE POST (SE PRESENTE) -->
        <?php if (!empty($p['image_path_post'])): ?>
        <div class="post-image">
            <img src="<?php echo htmlspecialchars($p['image_path_post']); ?>" alt="Immagine del post" class="img-fluid">
        </div>
        <?php endif; ?>

<!-- INIZIO MODIFICA: post reactions e commenti -->
<div class="post-reactions" data-post-id="<?php echo $p['id']; ?>">
    <button class="btn-like">👍 <span class="like-count"><?php echo $p['likes'] ?? 0; ?></span></button>
    <?php if ($p['tipo'] === 'proposta'): ?>
    <button class="btn-dislike">👎 <span class="dislike-count"><?php echo $p['dislikes'] ?? 0; ?></span></button>
    <?php endif; ?>

<a href="#" class="noticity-comment-btn" data-post-id="<?php echo $p['id']; ?>">
    <i class="fa fa-comment"></i> <?php echo $p['commenti']; ?>
</a>


    <button class="btn-share">🔗</button>
</div>
<!-- FINE MODIFICA -->

<div class="noticity-comment-wrapper" id="comment-wrapper-<?php echo $p['id']; ?>" data-post-author-id="<?php echo $p['utente_id']; ?>">
</div>



    </div>
</div>
<?php endforeach; ?>
            
          

          
          
          
          
       <!-- Start post -->
       </div>    
    </div>
    </div>
    <!-- End Left side columns -->
    <!-- Right side columns -->
    <!-- Friends -->
    <div class="col-lg-4">
     <div class="card">
      <div class="sidebar-nav" id="sidebar-nav">
       <li class="nav-item">
        <a class="nav-link" href="index.html">
         <i class="bi bi-people-fill">
         </i>
         <span>
          Concittadini
         </span>
        </a>
       </li>
      </div>
      <div class="central-meta item">
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar2.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Laura Bighiani
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar3.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Andrea Visconti
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Silvano Colombo
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar4.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Federica Landi
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar7.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Chiara Maltagiati
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
       <div class="friend-info">
        <figure>
         <img alt="" src="assets/img/resources/friend-avatar8.jpg"/>
        </figure>
        <div class="friend-meta">
         <h4>
          <a href="time-line.html" title="">
           Sara Garavaglia
          </a>
         </h4>
         <a class="underline" href="#" title="">
          Aggiungi
         </a>
        </div>
       </div>
      </div>
     </div>
    </div>
    <!-- End Recent Activity -->
   </div>
   <!-- End Right side columns -->
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

 <!-- Modale menu commenti -->
<div class="modal fade" id="commentOptionsModal" tabindex="-1" aria-labelledby="commentOptionsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
     <div class="modal-content">
       <div class="modal-body" id="commentOptionsModalBody" style="padding: 0;">
         </div>
       <div class="modal-footer" style="border-top: none;">
         <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Annulla</button>
       </div>
     </div>
   </div>
 </div>

<!-- MODALE UNICA PER CONFERMA -->
<div class="modal fade" id="actionConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Conferma Azione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalMessage">
        Sei sicuro di voler procedere?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-danger" id="confirmActionBtn">Conferma</button>
      </div>
    </div>
  </div>
</div>


 <a class="back-to-top d-flex align-items-center justify-content-center" href="#">
  <i class="bi bi-arrow-up-short">
  </i>
 </a>
 <!-- Vendor JS Files -->



<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


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
<script>
    const currentUserID = <?php echo json_encode($_SESSION['user_id']); ?>;
</script>
<script src="assets/js/app.js"></script>




<!-- JS di Cropper.js -->




<script>
// Aspetta che l'intera pagina sia caricata prima di eseguire qualsiasi codice
document.addEventListener("DOMContentLoaded", function () {
    
    let cropper; // Variabile globale per il cropper

    // Funzione per mostrare l'anteprima e inizializzare Cropper.js
    window.showPreview = function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                initializeCropper(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }

    function initializeCropper(imageSrc) {
        const cropperContainer = document.getElementById('cropperContainer');
        const cropperImage = document.getElementById('cropperImage');
        if (!cropperContainer || !cropperImage) return;
        cropperContainer.style.display = 'flex';
        cropperImage.src = imageSrc;
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 4 / 3,
            viewMode: 1,
            autoCropArea: 1,
        });
    }

    // Gestisci il click su "Ritaglia Immagine"
    const cropImageBtn = document.getElementById('cropImageBtn');
    if (cropImageBtn) {
        cropImageBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ maxWidth: 1200, maxHeight: 1200 });
            const croppedImage = canvas.toDataURL('image/jpeg', 0.7);
            
            const previewImg = document.getElementById('previewImg');
            if (previewImg) {
                previewImg.src = croppedImage;
                document.getElementById('imagePreview').style.display = 'block';
            }
            
            const hiddenImageData = document.getElementById('hiddenImageData');
            if (hiddenImageData) hiddenImageData.value = croppedImage;
            
            const cropperContainer = document.getElementById('cropperContainer');
            if (cropperContainer) cropperContainer.style.display = 'none';
        });
    }

    // Gestisci il click su "Pubblica"
    const publishButton = document.getElementById('publishButton');
    if (publishButton) {
        publishButton.addEventListener('click', function(event) {
            event.preventDefault();
            const form = publishButton.closest('form');
            const formData = new FormData(form);
            const imageBase64 = document.getElementById('hiddenImageData').value;
            if (imageBase64) {
                formData.append('image_post_base64', imageBase64);
            }

            fetch('crea_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // alert(data.message); // Rimosso l'alert per un'esperienza più fluida
                    const post = data.post_data;
                    if (post) {
                        const newPost = document.createElement('div');
                        newPost.className = 'col-12';
                        const fotoProfiloUrl = post.foto_profilo ? `assets/img/utenti/${post.foto_profilo}` : 'assets/img/utenti/default.jpg';
                        const tipoBadgeClass = post.tipo === 'segnalazione' ? 'badge-segnalazione' : 'badge-proposta';
                        const tipoText = post.tipo.charAt(0).toUpperCase() + post.tipo.slice(1);
                        
                        newPost.innerHTML = `<div class="card" style="position:relative; margin-bottom:15px;"><div class="post-header-right"><span class="badge-post ${tipoBadgeClass}">${tipoText}</span><div class="post-menu"><button class="menu-btn">⋮</button><div class="menu-dropdown">${currentUserID == post.utente_id ? `<a href="javascript:void(0);" class="delete-post-btn" data-id="${post.id}">Elimina</a>` : `<a href="javascript:void(0);" class="report-post-btn" data-id="${post.id}">Segnala</a>`}</div></div></div><div class="central-meta item"><div class="friend-info" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;"><div style="width:50px; height:50px; border-radius:50%; overflow:hidden; flex-shrink:0;"><img src="${fotoProfiloUrl}" alt="Foto Profilo" style="width:100%; height:100%; object-fit:cover;"></div><div><a href="profilo_utente.php?id=${post.utente_id}" style="color:#007bff; font-weight:bold; font-size:16px; text-decoration:none;">${post.nome_utente} ${post.cognome_utente}</a><br><span style="font-size:12px; color:#6c757d;">Pubblicato: ${post.created_at}</span></div></div><div style="font-size:14px; margin-bottom:10px;">${post.contenuto}</div>${post.image_path_post ? `<div class="post-image"><img src="${post.image_path_post}" alt="Immagine del post" class="img-fluid"></div>` : ''}<div class="post-reactions" data-post-id="${post.id}"><button class="btn-like">👍 <span class="like-count">${post.likes}</span></button>${post.tipo === 'proposta' ? `<button class="btn-dislike">👎 <span class="dislike-count">${post.dislikes}</span></button>` : ''}<a href="#" class="noticity-comment-btn" data-post-id="${post.id}"><i class="fa fa-comment"></i> ${post.commenti}</a><button class="btn-share">🔗</button></div><div class="noticity-comment-wrapper" id="comment-wrapper-${post.id}" data-post-author-id="${post.utente_id}"></div></div></div>`;
                        
                        const postContainer = document.getElementById('post-list-container');
                        if(postContainer) {
                            postContainer.prepend(newPost);
                        } else {
                             window.location.reload(); // Fallback se non trova il contenitore
                        }
                        
                        // Pulisce il form
                        document.getElementById('tipoPost').selectedIndex = 0;
                        document.getElementById('contenuto').value = '';
                        const imageInput = document.getElementById('image_post');
                        if(imageInput) imageInput.value = '';
                        const imagePreview = document.getElementById('imagePreview');
                        if(imagePreview) imagePreview.style.display = 'none';
                        const hiddenImageData = document.getElementById('hiddenImageData');
                        if(hiddenImageData) hiddenImageData.value = '';

                    } else {
                       window.location.reload(); // Se non riceve i dati, ricarica la pagina
                    }
                } else {
                    alert('Errore: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                alert('Errore durante la pubblicazione del post. Controlla la console.');
            });
        });
    }

    // Gestione Like/Dislike sui post
    // È importante usare l'event delegation per i post aggiunti dinamicamente
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-like')) {
            const container = e.target.closest('.post-reactions');
            inviaReazione(container.dataset.postId, 'like', container);
        }
        if (e.target.closest('.btn-dislike')) {
            const container = e.target.closest('.post-reactions');
            inviaReazione(container.dataset.postId, 'dislike', container);
        }
    });

    function inviaReazione(postId, tipo, container) {
        fetch('azioni_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&azione=${tipo}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                container.querySelector('.like-count').textContent = data.likes;
                if (container.querySelector('.dislike-count')) {
                    container.querySelector('.dislike-count').textContent = data.dislikes;
                }
            }
        });
    }

    // --- GESTIONE MENU A TENDINA DEI POST (VERSIONE CORRETTA) ---
    let currentAction = null;
    let currentPostId = null;
    const actionConfirmModalElement = document.getElementById('actionConfirmModal');

    // Assicurati che la modale esista prima di aggiungere i listener
    if (actionConfirmModalElement) {
        const actionConfirmModal = new bootstrap.Modal(actionConfirmModalElement);

        // Aggiungi un unico gestore di eventi al documento per tutte le azioni
        document.addEventListener('click', function(e) {
            const clickedMenuBtn = e.target.closest(".menu-btn");
            
            // Caso 1: L'utente ha cliccato su un pulsante del menu (⋮)
            if (clickedMenuBtn) {
                e.preventDefault(); // Previene comportamenti strani del pulsante
                const parentMenu = clickedMenuBtn.closest('.post-menu');
                const wasActive = parentMenu.classList.contains('active');

                // Per prima cosa, chiudi SEMPRE tutti gli altri menu aperti
                document.querySelectorAll(".post-menu.active").forEach(menu => {
                    if (menu !== parentMenu) {
                        menu.classList.remove("active");
                    }
                });

                // Apri/Chiudi (toggle) solo il menu cliccato
                parentMenu.classList.toggle('active');
            } 
            // Caso 2: L'utente ha cliccato in un punto qualsiasi FUORI da un menu
            else if (!e.target.closest('.post-menu')) {
                document.querySelectorAll(".post-menu.active").forEach(menu => {
                    menu.classList.remove("active");
                });
            }

            // Caso 3: L'utente ha cliccato su un link di azione (Elimina/Segnala)
            if (e.target.classList.contains('delete-post-btn') || e.target.classList.contains('report-post-btn')) {
                 e.preventDefault();
                 currentPostId = e.target.dataset.id;
                 if(e.target.classList.contains('delete-post-btn')) {
                     currentAction = 'delete';
                     document.getElementById('modalTitle').textContent = "Conferma Eliminazione";
                     document.getElementById('modalMessage').textContent = "Sei sicuro di voler eliminare questo post?";
                 } else {
                     currentAction = 'report';
                     document.getElementById('modalTitle').textContent = "Conferma Segnalazione";
                     document.getElementById('modalMessage').textContent = "Vuoi davvero segnalare questo post?";
                 }
                 actionConfirmModal.show();
            }
        });

        // Gestore per il pulsante di conferma nella modale
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        if (confirmActionBtn) {
            confirmActionBtn.addEventListener('click', function() {
                if (!currentPostId) return;
                if (currentAction === 'delete') {
                    window.location.href = "elimina_post.php?id=" + currentPostId;
                } else if (currentAction === 'report') {
                    window.location.href = "segnala_post.php?id=" + currentPostId;
                }
            });
        }
    }


});
</script>

</body>
</html>