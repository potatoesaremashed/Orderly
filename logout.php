<?php
session_start(); // Recupera la sessione corrente
session_unset(); // Rimuove tutte le variabili di sessione
session_destroy(); // Distrugge la sessione

// Reindirizza alla pagina di login
header("Location: index.php");
exit;
?>
