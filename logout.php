<?php
// Recupera la sessione attualmente in uso dal browser dell'utente
session_start();

// Svuota tutte le variabili temporanee salvate (ruolo, id tavolo, ecc.)
session_unset();

// Distruggi fisicamente la sessione dal server per impedire accessi futuri
session_destroy();

// Rispedisci l'utente immediatamente alla pagina di login (index.php)
header("Location: index.php");
exit;
?>