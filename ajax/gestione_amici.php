<?php
/* ==========================================
   /ajax/gestione_amici.php  — Noticity
   Endpoint AJAX unico per: contatori, tab, azioni amicizia, ricerca
   ========================================== */
declare(strict_types=1);
session_start();

// Debug rapido (opzionale: /ajax/gestione_amici.php?debug=1)
if (isset($_GET['debug'])) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
}

try {
  // Path corretto (siamo in /ajax/)
  $cfg = __DIR__ . '/../config.php';
  if (!is_file($cfg)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Config non trovata (../config.php).']);
    exit;
  }
  require_once $cfg;

  if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Non autenticato']);
    exit;
  }

  if (!isset($pdo) || !($pdo instanceof PDO)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Connessione DB non disponibile']);
    exit;
  }

  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

  $utenteId = (int)$_SESSION['user_id'];
  $azione   = $_POST['azione'] ?? $_GET['azione'] ?? '';

  // ----------------- Helpers -----------------
  function getUser(PDO $pdo, int $uid): ?array {
    $st = $pdo->prepare("
      SELECT u.id, u.nome, u.cognome, u.professione, u.foto_profilo, u.comune_id, u.lat, u.lng
      FROM utenti u WHERE u.id = ?
    ");
    $st->execute([$uid]);
    $r = $st->fetch();
    return $r ?: null;
  }

  function getOriginCoords(PDO $pdo, int $uid): array {
    $st = $pdo->prepare("
      SELECT COALESCE(u.lat, c.lat) AS lat, COALESCE(u.lng, c.lng) AS lng, u.comune_id
      FROM utenti u
      LEFT JOIN comuni c ON c.id = u.comune_id
      WHERE u.id = ?
    ");
    $st->execute([$uid]);
    $r = $st->fetch() ?: ['lat'=>null,'lng'=>null,'comune_id'=>null];
    return [(float)($r['lat'] ?? 0), (float)($r['lng'] ?? 0), (int)($r['comune_id'] ?? 0)];
  }

  function haversineExpr(float $lat0, float $lng0, string $latCol, string $lngCol): string {
    $lat0 = (float)$lat0; $lng0 = (float)$lng0;
    // 6371 km
    return "(
      6371 * 2 * ASIN(
        SQRT(
          POWER(SIN(RADIANS(($latCol - $lat0) / 2)), 2) +
          COS(RADIANS($lat0)) * COS(RADIANS($latCol)) * POWER(SIN(RADIANS(($lngCol - $lng0) / 2)), 2)
        )
      )
    )";
  }

  function printUserRow(array $u, string $context): void {
    $foto = !empty($u['foto_profilo']) ? 'assets/img/utenti/'.$u['foto_profilo'] : 'assets/img/utenti/default.jpg';
    $nome = htmlspecialchars(($u['nome'] ?? '').' '.($u['cognome'] ?? ''));
    $prof = htmlspecialchars($u['professione'] ?? 'Cittadino');
    $id   = (int)$u['id'];
    $rid  = isset($u['richiesta_id']) ? (int)$u['richiesta_id'] : null;
    $dist = isset($u['distance_km']) ? number_format((float)$u['distance_km'], 1, ',', '') . ' km' : null;

    echo '<div class="friend-item d-flex align-items-center justify-content-between py-2">';
    echo '  <div class="friend-info d-flex align-items-center gap-2">';
    echo '    <img src="'.htmlspecialchars($foto).'" alt="Profilo" style="width:48px;height:48px;border-radius:50%;object-fit:cover">';
    echo '    <div>';
    echo '      <a href="profilo_utente.php?id='.$id.'">'.$nome.'</a>';
    echo '      <div class="text-muted small">'.$prof.($dist ? ' • '.$dist : '').'</div>';
    echo '    </div>';
    echo '  </div>';
    echo '  <div class="friend-actions">';
    if ($context === 'amici' || ($context === 'risultati' && !empty($u['is_friend']))) {
      echo '    <button class="btn btn-danger btn-sm btn-rimuovi-amico" data-amico-id="'.$id.'">Rimuovi</button>';
    } elseif ($context === 'suggeriti' || ($context === 'risultati' && empty($u['is_friend']))) {
      echo '    <button class="btn btn-primary btn-sm btn-invia-richiesta" data-destinatario-id="'.$id.'">Aggiungi</button>';
    } elseif ($context === 'richieste' && $rid) {
      echo '    <button class="btn btn-primary btn-sm btn-conferma-amicizia" data-richiesta-id="'.$rid.'">Accetta</button>';
      echo '    <button class="btn btn-secondary btn-sm btn-rifiuta-amicizia ms-1" data-richiesta-id="'.$rid.'">Rifiuta</button>';
    }
    echo '  </div>';
    echo '</div>';
  }

  function printUserList(array $rows, string $context): void {
    if (!$rows) {
      $msg = [
        'amici'     => 'Non hai ancora nessun amico.',
        'suggeriti' => 'Non ci sono nuovi suggerimenti.',
        'richieste' => 'Non hai richieste in attesa.',
        'risultati' => 'Nessun risultato.'
      ][$context] ?? 'Nessun risultato.';
      echo '<p class="text-muted">'.$msg.'</p>';
      return;
    }
    foreach ($rows as $r) { printUserRow($r, $context); }
  }

  // ----------------- Router -----------------
  switch ($azione) {

    /* ------- CONTATORI (JSON) ------- */
    case 'contatori': {
      header('Content-Type: application/json; charset=utf-8');

      // amici
      $st = $pdo->prepare("SELECT COUNT(*) FROM amicizie WHERE (utente_richiedente_id=:u OR utente_destinatario_id=:u) AND stato='accepted'");
      $st->execute([':u'=>$utenteId]);
      $cntAmici = (int)$st->fetchColumn();

      // richieste verso di me
      $st = $pdo->prepare("SELECT COUNT(*) FROM amicizie WHERE utente_destinatario_id=:u AND stato='pending'");
      $st->execute([':u'=>$utenteId]);
      $cntRich = (int)$st->fetchColumn();

      // suggeriti (NOT EXISTS: nessuna relazione con me)
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
      $st->execute([':me1'=>$utenteId, ':me2'=>$utenteId, ':me3'=>$utenteId]);
      $cntSugg = (int)$st->fetchColumn();

      echo json_encode(['success'=>true,'amici'=>$cntAmici,'richieste'=>$cntRich,'suggeriti'=>$cntSugg]);
      break;
    }

    /* ------- CARICA TAB (HTML) ------- */
    case 'carica_tab': {
      header('Content-Type: text/html; charset=utf-8');
      $tab    = $_POST['tab'] ?? '';
      $offset = max(0, (int)($_POST['offset'] ?? 0));
      $pageSize = 15;

      if ($tab === 'tab-amici') {
  $limitPlus = $pageSize + 1;

  // NON riusiamo lo stesso placeholder nominato: :me1 e :me2
  // Usiamo una subquery con UNION ALL per prendere sia le amicizie dove sono richiedente, sia dove sono destinatario.
  $sql = "
    SELECT t.id, t.nome, t.cognome, t.foto_profilo, t.professione
    FROM (
      SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
      FROM amicizie a
      JOIN utenti u ON u.id = a.utente_destinatario_id
      WHERE a.utente_richiedente_id = :me1 AND a.stato = 'accepted'
      UNION ALL
      SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
      FROM amicizie a
      JOIN utenti u ON u.id = a.utente_richiedente_id
      WHERE a.utente_destinatario_id = :me2 AND a.stato = 'accepted'
    ) AS t
    ORDER BY t.nome, t.cognome
    LIMIT :lim OFFSET :off
  ";

  $st = $pdo->prepare($sql);
  $st->bindValue(':me1', $utenteId, PDO::PARAM_INT);
  $st->bindValue(':me2', $utenteId, PDO::PARAM_INT);
  $st->bindValue(':lim', $limitPlus, PDO::PARAM_INT);
  $st->bindValue(':off', $offset, PDO::PARAM_INT);
  $st->execute();

  $rows   = $st->fetchAll();
  $hasMore = (count($rows) > $pageSize);
  $rows    = array_slice($rows, 0, $pageSize);

  printUserList($rows, 'amici');
  if ($hasMore) {
    echo '<div class="text-center mt-3"><button class="btn btn-outline-primary btn-load-more" data-next-offset="'.($offset + $pageSize).'">Carica altri</button></div>';
  }
  break;
}

      if ($tab === 'tab-suggeriti') {
        [$lat0, $lng0] = getOriginCoords($pdo, $utenteId);
        $distExpr = ($lat0 && $lng0) ? haversineExpr($lat0,$lng0,'COALESCE(u.lat,c.lat)','COALESCE(u.lng,c.lng)') : '999999';
        // Escludi me + chi ha già relazione pendente/accettata con me
        $sql = "
          SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione, $distExpr AS distance_km
          FROM utenti u
          LEFT JOIN comuni c ON c.id = u.comune_id
          WHERE u.id <> :me
            AND NOT EXISTS (
              SELECT 1 FROM amicizie a
              WHERE (a.utente_richiedente_id = :me2 AND a.utente_destinatario_id = u.id)
                 OR (a.utente_destinatario_id = :me3 AND a.utente_richiedente_id = u.id)
            )
          ORDER BY distance_km ASC
          LIMIT 5
        ";
        $st = $pdo->prepare($sql);
        $st->execute([':me'=>$utenteId, ':me2'=>$utenteId, ':me3'=>$utenteId]);
        printUserList($st->fetchAll(), 'suggeriti');
        break;
      }

      if ($tab === 'tab-richieste') {
        $sql = "
          SELECT a.id AS richiesta_id, u.id, u.nome, u.cognome, u.foto_profilo, u.professione
          FROM amicizie a
          JOIN utenti u ON u.id = a.utente_richiedente_id
          WHERE a.utente_destinatario_id = :me AND a.stato = 'pending'
          ORDER BY a.data_richiesta DESC
        ";
        $st = $pdo->prepare($sql);
        $st->execute([':me'=>$utenteId]);
        printUserList($st->fetchAll(), 'richieste');
        break;
      }

      http_response_code(400);
      echo '<div class="text-danger">Tab non valida</div>';
      break;
    }

    /* ------- INVIA RICHIESTA (JSON) ------- */
    case 'invia_richiesta': {
      header('Content-Type: application/json; charset=utf-8');
      $dest = max(1, (int)($_POST['destinatario_id'] ?? 0));
      if ($dest === $utenteId) { echo json_encode(['success'=>false,'error'=>'Non puoi aggiungerti.']); break; }

      // Se esiste richiesta inversa pendente, accetta
      $st = $pdo->prepare("SELECT id FROM amicizie WHERE utente_richiedente_id=? AND utente_destinatario_id=? AND stato='pending' LIMIT 1");
      $st->execute([$dest, $utenteId]);
      if ($rid = $st->fetchColumn()) {
        $up = $pdo->prepare("UPDATE amicizie SET stato='accepted', data_accettazione=NOW() WHERE id=?");
        $up->execute([$rid]);
        echo json_encode(['success'=>true,'autoAccepted'=>true]); break;
      }

      // Evita duplicati
      $st = $pdo->prepare("SELECT id FROM amicizie WHERE (utente_richiedente_id=? AND utente_destinatario_id=?) OR (utente_richiedente_id=? AND utente_destinatario_id=?) LIMIT 1");
      $st->execute([$utenteId, $dest, $dest, $utenteId]);
      if ($st->fetchColumn()) { echo json_encode(['success'=>false,'error'=>'Richiesta già esistente o amicizia presente.']); break; }

      $ins = $pdo->prepare("INSERT INTO amicizie (utente_richiedente_id, utente_destinatario_id, stato, data_richiesta) VALUES (?,?, 'pending', NOW())");
      $ins->execute([$utenteId, $dest]);
      echo json_encode(['success'=>true]);
      break;
    }

    /* ------- ACCETTA / RIFIUTA / RIMUOVI (JSON) ------- */
    case 'accetta_richiesta': {
      header('Content-Type: application/json; charset=utf-8');
      $rid = (int)($_POST['richiesta_id'] ?? 0);
      $up  = $pdo->prepare("UPDATE amicizie SET stato='accepted', data_accettazione=NOW() WHERE id=? AND utente_destinatario_id=? AND stato='pending'");
      $up->execute([$rid, $utenteId]);
      echo json_encode(['success'=> $up->rowCount() > 0]);
      break;
    }
    case 'rifiuta_richiesta': {
      header('Content-Type: application/json; charset=utf-8');
      $rid = (int)($_POST['richiesta_id'] ?? 0);
      $up  = $pdo->prepare("UPDATE amicizie SET stato='rejected' WHERE id=? AND utente_destinatario_id=? AND stato='pending'");
      $up->execute([$rid, $utenteId]);
      echo json_encode(['success'=> $up->rowCount() > 0]);
      break;
    }
    case 'rimuovi_amico': {
      header('Content-Type: application/json; charset=utf-8');
      $amicoId = (int)($_POST['amico_id'] ?? 0);
      $del = $pdo->prepare("DELETE FROM amicizie WHERE stato='accepted' AND ((utente_richiedente_id=? AND utente_destinatario_id=?) OR (utente_richiedente_id=? AND utente_destinatario_id=?))");
      $del->execute([$utenteId, $amicoId, $amicoId, $utenteId]);
      echo json_encode(['success'=> $del->rowCount() > 0]);
      break;
    }

    /* ------- RICERCA (HTML) ------- */
case 'ricerca': {
  header('Content-Type: text/html; charset=utf-8');

  $q        = trim((string)($_POST['q'] ?? ''));
  $ambito   = ($_POST['ambito'] ?? 'tutti') === 'amici' ? 'amici' : 'tutti';
  $comuneId = (int)($_POST['comune_id'] ?? 0);
  $profFiltro = trim((string)($_POST['professione'] ?? ''));

  [$lat0, $lng0, $myComune] = getOriginCoords($pdo, $utenteId);
  if ($comuneId <= 0) $comuneId = $myComune;

  // --- Costruzione condizioni di ricerca ---
  $params = [];
  $searchConds = [];
  $concatExpr = "CONCAT_WS(' ', u.nome, u.cognome, COALESCE(u.professione,''))";

  if ($q !== '') {
    // split per spazi -> tutti i token devono comparire (AND)
    $tokens = preg_split('/\s+/', $q);
    $i = 0;
    foreach ($tokens as $t) {
      if ($t === '') continue;
      $key = ':tk'.(++$i);
      $searchConds[] = "$concatExpr LIKE $key";
      $params[$key] = '%'.$t.'%';
    }
  }
  $where = $searchConds ? '('.implode(' AND ', $searchConds).')' : '1=1';
  if ($comuneId > 0) { $where .= " AND u.comune_id = :cid"; $params[':cid'] = $comuneId; }

  // --- Relazioni dell'utente (Amici e Bloccati) ---
  $st = $pdo->prepare("SELECT utente_richiedente_id, utente_destinatario_id, stato FROM amicizie WHERE utente_richiedente_id=:utenteId OR utente_destinatario_id=:utenteId");
  $st->execute([':utenteId' => $utenteId]);
  $friends = $blocked = [$utenteId];
  foreach ($st->fetchAll() as $r) {
    $other = ($r['utente_richiedente_id'] == $utenteId) ? (int)$r['utente_destinatario_id'] : (int)$r['utente_richiedente_id'];
    if ($r['stato'] === 'accepted') $friends[] = $other;
    $blocked[] = $other; // escludo tutti dalle ricerche "non-amici"
  }
  $friends = array_values(array_unique($friends));
  $blocked = array_values(array_unique($blocked));

  $result = [];

  // --- AMBITO: SOLO AMICI ---
  if ($ambito === 'amici') {
    if (count($friends) > 1) {
      $ph = implode(',', array_map(function($i) { return ":friend$i"; }, range(1, count($friends))));
      $sql = "SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
              FROM utenti u
              WHERE u.id IN ($ph) AND $where
              ORDER BY u.nome, u.cognome";
      $stA = $pdo->prepare($sql);
      foreach ($friends as $i => $id) {
        $stA->bindValue(":friend".($i+1), $id, PDO::PARAM_INT);
      }
      foreach ($params as $k=>$v) { $stA->bindValue($k, $v); }
      $stA->execute();
      $rowsA = $stA->fetchAll();
      foreach ($rowsA as &$r) $r['is_friend'] = 1;
      printUserList($rowsA, 'risultati');
      break;
    } else {
      printUserList([], 'risultati');
      break;
    }
  }

  // --- AMBITO: TUTTI → 1) amici che matchano, 2) non-amici per distanza ---
  if (count($friends) > 1) {
    $ph = implode(',', array_map(function($i) { return ":friend$i"; }, range(1, count($friends))));
    $sql = "SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione
            FROM utenti u
            WHERE u.id IN ($ph) AND $where
            ORDER BY u.nome, u.cognome";
    $stA = $pdo->prepare($sql);
    foreach ($friends as $i => $id) {
      $stA->bindValue(":friend".($i+1), $id, PDO::PARAM_INT);
    }
    foreach ($params as $k=>$v) { $stA->bindValue($k, $v); }
    $stA->execute();
    $rowsA = $stA->fetchAll();
    foreach ($rowsA as &$r) $r['is_friend'] = 1;
    $result = $rowsA;
  }

  // --- 2) Non-amici ordinati per distanza ---
  $distExpr = ($lat0 && $lng0) ? haversineExpr($lat0,$lng0,'COALESCE(u.lat,c.lat)','COALESCE(u.lng,c.lng)') : '999999';
  $phEx = implode(',', array_map(function($i) { return ":blocked$i"; }, range(1, count($blocked))));
  $sqlB = "SELECT u.id, u.nome, u.cognome, u.foto_profilo, u.professione, $distExpr AS distance_km
           FROM utenti u
           LEFT JOIN comuni c ON c.id = u.comune_id
           WHERE u.id NOT IN ($phEx) AND $where
           ORDER BY distance_km ASC, u.nome, u.cognome
           LIMIT 200";
  $stB = $pdo->prepare($sqlB);
  foreach ($blocked as $i => $id) {
    $stB->bindValue(":blocked".($i+1), $id, PDO::PARAM_INT);
  }
  foreach ($params as $k=>$v) { $stB->bindValue($k, $v); }
  $stB->execute();
  $rowsB = $stB->fetchAll();
  foreach ($rowsB as &$r) $r['is_friend'] = 0;

  $result = array_merge($result, $rowsB);
  printUserList($result, 'risultati');
  break;
}

    default: {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['success'=>false,'error'=>'Azione non valida']);
    }
  }

} catch (Throwable $e) {
  // Non mandiamo 500 ai client: ritorniamo JSON con errore così lo vedi in Network->Response
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success'=>false, 'error'=>'EXCEPTION: '.$e->getMessage()]);
}
