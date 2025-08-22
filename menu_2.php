<nav class="main-menu">
  <ul>
    <li><a href="#">Home</a></li>
    <li class="dropdown">
      <a href="#">Notizie</a>
      <ul class="submenu">
        <li><a href="#">Italia</a></li>
        <li><a href="#">Regione</a></li>
        <li><a href="#">Provincia</a></li>
        <li><a href="#">Comune</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#">Servizi</a>
      <ul class="submenu">
        <li><a href="#">Segnala</a></li>
        <li><a href="#">Proponi</a></li>
      </ul>
    </li>
    <li><a href="#">Eventi</a></li>
    <li><a href="#">Contatti</a></li>
  </ul>
</nav>

<style>
/* menu.css incluso inline per ora */
.main-menu {
  background-color: #003366;
  font-family: Arial, sans-serif;
}
.main-menu ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  display: flex;
}
.main-menu ul li {
  position: relative;
}
.main-menu ul li a {
  display: block;
  padding: 14px 20px;
  color: white;
  text-decoration: none;
}
.main-menu ul li a:hover {
  background-color: #005599;
}
.submenu {
  display: none;
  position: absolute;
  background-color: #004080;
  top: 100%;
  left: 0;
  min-width: 180px;
}
.submenu li a {
  padding: 10px 15px;
}
.dropdown:hover .submenu {
  display: block;
}
</style>
