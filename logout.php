<?php
/**
 * =========================================
 * FILE: logout.php
 * =========================================
 * Questo file gestisce la disconnessione (logout) di qualsiasi utente (Manager, Cuoco, o Tavolo).
 * Ha il compito di cancellare in modo sicuro la sessione attiva e reindirizzare l'utente 
 * alla pagina di accesso iniziale (login).
 * 
 * Quando un utente fa il "logout", dobbiamo assicurarci che il server cancelli 
 * ogni informazione su di lui. Senza queste funzioni, chiunque usi lo stesso computer 
 * potrebbe ancora accedere alla dashboard premendo il tasto "indietro".
 */

session_start(); // Avvia o riprende la sessione utente corrente per poterla poi distruggere.

session_unset(); // Rimuove tutte le informazioni utente salvate in memoria temporanea (es. ruolo, id_tavolo).

session_destroy(); // Distrugge fisicamente la sessione sul server, chiudendo l'accesso protetto.

// Reindirizza l'utente alla schermata di login principale (index.php)
header("Location: index.php");
exit; // Termina l'esecuzione per ragioni di sicurezza.
?>