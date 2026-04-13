<?php
session_start();
// Distrugge tutte le variabili di sessione (scollega l'utente)
session_unset();
session_destroy();

// Rimanda l'utente alla homepage
header("Location: index.php");
exit;
?>