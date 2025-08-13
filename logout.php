<?php
session_start();
session_unset();
session_destroy();

// Reindirizza alla pagina di login
header("Location: login.php");
exit();
?>