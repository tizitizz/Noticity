<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<strong>DEBUG REDIRECT:</strong> user_id in sessione = " . ($_SESSION['user_id'] ?? 'null') . "<br>";

header("Refresh: 3; URL=index.php");
echo "Reindirizzamento automatico alla home in 3 secondi...";
exit;
