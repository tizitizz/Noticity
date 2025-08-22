<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';
require 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$current_user_id = (int)$_SESSION['user_id'];

/* Dati utente per header/sidebar e default comune */
$stmt_user = $pdo->prepare("
  SELECT u.*, c.nome AS nome_comune, c.provincia, c.regione
  FROM utenti u
  JOIN comuni c ON c.id = u.comune_id
  WHERE u.id = ?
");
$stmt_user->execute([$current_user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
if (!$user) { session_destroy(); header("Location: login.php"); exit; }

$foto_url        = $user['foto_profilo'] ? "assets/img/utenti/".$user['foto_profilo'] : "assets/img/utenti/default.jpg";
$defaultComuneId = (int)$user['comune_id'];

/* ---- Contatori iniziali (per badge tabs) ---- */
# Amici accettati
$sqlAmici = "
  SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
  FROM amicizie a
  JOIN utenti u ON u.id = a.utente_destinatario_id
  WHERE a.utente_richiedente_id = :meA AND a.stato = 'accepted'
  UNION ALL
  SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
  FROM amicizie a
  JOIN utenti u ON u.id = a.utente_richiedente_id
  WHERE a.utente_destinatario_id = :meB AND a.stato = 'accepted'
  ORDER BY nome, cognome
";
$stmt_amici = $pdo->prepare($sqlAmici);
$stmt_amici->execute([':meA' => (int)$current_user_id, ':meB' => (int)$current_user_id]);
$amici = $stmt_amici->fetchAll(PDO::FETCH_ASSOC);
$conteggio_amici = count($amici);

# Richieste ricevute pendenti
$st = $pdo->prepare("SELECT COUNT(*) FROM amicizie WHERE utente_destinatario_id=:u AND stato='pending'");
$st->execute([':u'=>$current_user_id]);
$conteggio_richieste = (int)$st->fetchColumn();

# Suggeriti = utenti non collegati a te (grezzo, per il badge)
$rel = $pdo->prepare("SELECT utente_richiedente_id, utente_destinatario_id FROM amicizie WHERE utente_richiedente_id=? OR utente_destinatario_id=?");
$rel->execute([$current_user_id, $current_user_id]);
$exclude = [$current_user_id];
foreach ($rel->fetchAll(PDO::FETCH_ASSOC) as $r) {
  $exclude[] = ($r['utente_richiedente_id'] == $current_user_id) ? (int)$r['utente_destinatario_id'] : (int)$r['utente_richiedente_id'];
}
// ----- CONTEGGIO SUGGERITI (robusto, senza NOT IN dinamico) -----
$st = $pdo->prepare("
  SELECT COUNT(*)
  FROM utenti u
  WHERE u.id <> :me1
    AND NOT EXISTS (
      SELECT 1
      FROM amicizie a
      WHERE (a.utente_richiedente_id = :me2 AND a.utente_destinatario_id = u.id)
         OR (a.utente_destinatario_id = :me3 AND a.utente_richiedente_id = u.id)
    )
");
$st->execute([
  ':me1' => (int)$current_user_id,
  ':me2' => (int)$current_user_id,
  ':me3' => (int)$current_user_id
]);
$conteggio_suggeriti = (int)$st->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Noticity</title>
  <meta content="Fai sentire la tua voce nella tua città." name="description" />
  <link href="assets/img/favicon.png" rel="icon" />
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />
  <link href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet" />
  <!-- Vendor CSS -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet" />
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet" />
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet" />
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet" />
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet" />
  <!-- Template CSS -->
  <link href="assets/css/main.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <link href="assets/css/color.css" rel="stylesheet" />
  <style>
    /* Stili rapidi per card amici/ricerca */
    .friends-card .friend-item{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eee}
    .friends-card .friend-info{display:flex;gap:10px;align-items:center}
    .friends-card .friend-info img{width:44px;height:44px;border-radius:50%;object-fit:cover}
    .friends-card .menu-dropdown{position:absolute;right:0;top:calc(100% + 6px);z-index:1000;display:none;min-width:280px}
    .friends-card .results-title{color:#012970}
  </style>
</head>

<body>
  <!-- ======= Header (INVARIATO) ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt=""/>
        <span class="d-none d-lg-block">Noticity</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <!-- Search e icone header (in linea con il tuo template) -->
    <div class="search-bar">
      <form class="search-form d-flex align-items-center" action="#" method="post" onsubmit="return false;">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item">
          <a class="nav-link nav-icon" href="amici.php" title="Amici">
            <i class="bi bi-people"></i>
          </a>
        </li>

        <!-- Notifiche (lasciate come nel tuo file) -->
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">3</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <!-- … (eventuali tuoi elementi già presenti) … -->
          </ul>
        </li>

        <!-- Profilo -->
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="<?php echo htmlspecialchars($foto_url); ?>" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;" alt="Profile">
            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?php echo htmlspecialchars(substr($user['nome'],0,1)).'. '.htmlspecialchars($user['cognome']); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($user['nome']).' '.htmlspecialchars($user['cognome']); ?></h6>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item d-flex align-items-center" href="profilo_utente.php"><i class="bi bi-person"></i><span>Il mio profilo</span></a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item d-flex align-items-center" href="profilo_utente.php"><i class="bi bi-gear"></i><span>Impostazioni</span></a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item d-flex align-items-center" href="pages-faq.html"><i class="bi bi-question-circle"></i><span>Hai bisogno d'aiuto?</span></a></li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <a class="dropdown-item d-flex align-items-center custom-logout" href="logout.php" onclick="return confirm('Vuoi disconnetterti?');" style="color:#444;font-size:14px;text-decoration:none;">
                <i class="bi bi-box-arrow-right me-2"></i><span>Disconnetti</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>
  <!-- ======= Sidebar (INVARIATA) ======= -->
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-grid"></i><span>Home</span></a></li>
      <!-- … (mantieni qui le tue voci di menu esistenti) … -->
    </ul>
  </aside>

  <!-- ======= Main ======= -->
  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Amici</h1>
    </div>

    <section class="section dashboard">
      <div class="row">
        <div class="col-lg-8">
          <div class="card friends-card">
            <div class="card-body">
              <h5 class="card-title">Gestisci amici</h5>

              <!-- Barra ricerca + filtri -->
              <div class="d-flex align-items-center gap-2 mb-3 position-relative">
                <form id="form-ricerca-amici" class="d-flex flex-grow-1 gap-2" onsubmit="return false;">
                  <input type="text" class="form-control" id="ricerca-q" placeholder="Cerca persone... (nome, cognome, professione)">
                  <input type="hidden" id="f-ambito" name="ambito" value="tutti">
                  <input type="hidden" id="f-comune" name="comune_id" value="<?php echo $defaultComuneId; ?>">
                  <input type="hidden" id="f-prof" name="professione" value="">
                  <button class="btn btn-outline-primary" type="submit" title="Cerca"><i class="bi bi-search"></i></button>
                </form>

                <!-- Bottone filtro + menu a tendina -->
                <div id="friends-filter" class="position-relative">
                  <button type="button" class="btn btn-outline-secondary" id="btn-open-filter" title="Filtra"><i class="bi bi-funnel"></i></button>
                  <div class="card menu-dropdown shadow-sm">
                    <div class="card-body">
                      <div class="mb-2">
                        <label class="form-label small mb-1 d-block">Ambito</label>
                        <div class="d-flex gap-3">
                          <label class="small"><input type="radio" name="ambito_r" value="tutti" checked> Tutti</label>
                          <label class="small"><input type="radio" name="ambito_r" value="amici"> Solo amici</label>
                        </div>
                      </div>

                      <div class="mb-2">
                        <label class="form-label small mb-1">Comune</label>
                        <select class="form-select form-select-sm" id="sel-comune">
                          <?php
                            $cs = $pdo->query("SELECT id, nome FROM comuni ORDER BY nome ASC");
                            while ($c = $cs->fetch(PDO::FETCH_ASSOC)) {
                              $sel = ((int)$c['id'] === $defaultComuneId) ? 'selected' : '';
                              echo '<option value="'.(int)$c['id'].'" '.$sel.'>'.htmlspecialchars($c['nome']).'</option>';
                            }
                          ?>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label small mb-1">Professione</label>
                        <input type="text" class="form-control form-control-sm" id="inp-prof" placeholder="es. elettricista, idraulico">
                        <div class="form-text small">Se scrivi nella barra di ricerca, quella parola ha priorità su questa.</div>
                      </div>

                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light btn-sm" id="btn-filter-cancel">Annulla</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-filter-apply">Applica</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Rimuovi filtri -->
                <button type="button" class="btn btn-outline-secondary d-none" id="btn-clear-filters" title="Rimuovi filtri" style="padding:6px 10px;">
                  <i class="bi bi-x-circle"></i>
                </button>
              </div>

              <!-- Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="friends-tabs">
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-amici">
                    Amici <span class="badge bg-secondary ms-1" id="badge-amici"><?php echo (int)$conteggio_amici; ?></span>
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-suggeriti">
                    Suggeriti <span class="badge bg-secondary ms-1" id="badge-suggeriti"><?php echo (int)$conteggio_suggeriti; ?></span>
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-richieste">
                    Richieste <span class="badge bg-danger ms-1" id="badge-richieste"><?php echo (int)$conteggio_richieste; ?></span>
                  </button>
                </li>
              </ul>

              <!-- Contenuti tab (riempiti via AJAX) -->
              <div class="tab-content pt-2" id="friends-tabs-content">
                <div class="tab-pane fade show active" id="tab-amici">
                  <div class="friends-list pt-3" id="amici-list"></div>
                </div>

                <div class="tab-pane fade" id="tab-suggeriti">
                  <div class="d-flex justify-content-end mt-2">
                    <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-suggested" title="Aggiorna suggeriti">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                  </div>
                  <div class="friends-list pt-3" id="suggeriti-list"></div>
                </div>

                <div class="tab-pane fade" id="tab-richieste">
                  <div class="friends-list pt-3" id="richieste-list"></div>
                </div>
              </div>

              <!-- Risultati ricerca -->
              <div id="search-results" class="pt-2 d-none">
                <div class="results-title fw-semibold mb-2">Risultati ricerca</div>
                <div class="results-list"></div>
              </div>

            </div>
          </div>
        </div>

        <!-- Colonna destra (se ti serve) -->
        <div class="col-lg-4">
          <!-- Lasciata vuota o aggiungi tuoi widget -->
        </div>
      </div>
    </section>
  </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">© Copyright <strong><span>Noticity</span></strong>. All Rights Reserved</div>
    <div class="credits"></div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<script>
(function () {
  // loader semplice
  function load(src, cb) {
    var s = document.createElement('script');
    s.src = src;
    s.async = false; // mantieni l'ordine
    s.onload = function(){ cb && cb(); };
    s.onerror = function(){ cb && cb(new Error('Errore nel caricare: ' + src)); };
    document.head.appendChild(s);
  }

  // prova jQuery da più CDN, poi carica i tuoi script nell'ordine giusto
  var cdnList = [
    'https://code.jquery.com/jquery-3.7.1.min.js',                    // (niente integrity!)
    'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js',
    'https://unpkg.com/jquery@3.7.1/dist/jquery.min.js'
  ];

  function tryLoadJQ(i) {
    if (i >= cdnList.length) {
      console.error('jQuery non caricato. Controlla firewall/CDN.');
      return;
    }
    load(cdnList[i], function (err) {
      if (err || !window.jQuery) {
        // prova il prossimo CDN
        tryLoadJQ(i + 1);
        return;
      }
      // assicura l'alias $
      if (!window.$) window.$ = window.jQuery;

      // ora carica, in quest’ordine:
      // 1) bootstrap (se ti serve)
      load('assets/vendor/bootstrap/js/bootstrap.bundle.min.js', function () {
        // 2) i tuoi script che usano jQuery
        load('assets/js/app.js', function () {
          load('assets/js/amici.js');
        });
      });
    });
  }

  tryLoadJQ(0);
})();
</script>


</body>
</html>
