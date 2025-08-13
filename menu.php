<?php
session_start();
?>
<!-- MENU COMPLETO -->
<header class="header fixed-top d-flex align-items-center" id="header">
  <div class="d-flex align-items-center justify-content-between">
    <a href="index2.html" class="logo d-flex align-items-center">
      <img src="assets/img/logo.png" alt="">
      <span class="d-none d-lg-block">Noticity</span>
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div>

  <div class="search-bar">
    <form class="search-form d-flex align-items-center" method="POST" action="#">
      <input type="text" name="query" placeholder="Search" title="Enter search keyword">
      <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

      <li class="nav-item d-block d-lg-none">
        <a class="nav-link nav-icon search-bar-toggle " href="#">
          <i class="bi bi-search"></i>
        </a>
      </li>

      <!-- ICONA NOTIFICHE -->
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-bell"></i>
          <span class="badge bg-primary badge-number">0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
          <li class="dropdown-header">Nessuna nuova notifica</li>
        </ul>
      </li>
      <!-- FINE NOTIFICHE -->

      <!-- MENU UTENTE -->
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="assets/img/user.png" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px;">
          <span class="d-none d-md-block dropdown-toggle ps-2">
            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Profilo'); ?>
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?php echo htmlspecialchars($_SESSION['username'] ?? 'Utente'); ?></h6>
            <span>Profilo utente</span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="profilo.php">
              <i class="bi bi-person"></i>
              <span>Il mio profilo</span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
<li>
  <a class="dropdown-item d-flex align-items-center" href="logout.php" onclick="return confirm('Vuoi disconnetterti?');" style="color: #444 !important;">
    <i class="bi bi-box-arrow-right me-2"></i>
    <span>Disconnetti</span>
  </a>
</li>
        </ul>
      </li>
      <!-- FINE MENU UTENTE -->

    </ul>
  </nav>
</header>
