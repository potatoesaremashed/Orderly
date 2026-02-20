<?php
/**
 * =========================================
 * FILE: logout.php
 * =========================================
 * Questo file gestisce la disconnessione (logout) di qualsiasi utente (Manager, Cuoco, o Tavolo).
 * Ha il compito di cancellare in modo sicuro la sessione attiva e reindirizzare l'utente 
 * alla pagina di accesso iniziale (login).
 */

session_start(); // Avvia o riprende la sessione utente corrente

// Rimuove tutte le informazioni utente salvate in memoria temporanea (es. ruolo, id_tavolo)
session_unset();

// Distrugge fisicamente la sessione, chiudendo l'accesso protetto
session_destroy();

// Reindirizza l'utente alla schermata di login principale (index.php)
header("Location: index.php");
exit; // Termina l'esecuzione per ragioni di sicurezza
?>