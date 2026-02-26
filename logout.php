<?php
// Recupera la sessione attualmente in uso dal browser dell'utente
session_start();

// Se l'utente che esce è un tavolo, rimettilo come libero e cancella il token dispositivo
if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'tavolo' && isset($_SESSION['id_tavolo'])) {
    include "include/conn.php";
    $idTavolo = intval($_SESSION['id_tavolo']);
    $conn->query("UPDATE tavoli SET stato='libero', device_token=NULL WHERE id_tavolo=" . $idTavolo);
    // Cancella il cookie del token
    setcookie('device_token_' . $idTavolo, '', time() - 3600, '/');
}

// Svuota tutte le variabili temporanee salvate (ruolo, id tavolo, ecc.)
session_unset();

// Distruggi fisicamente la sessione dal server per impedire accessi futuri
session_destroy();

// Rispedisci l'utente immediatamente alla pagina di login (index.php)
header("Location: index.php");
exit;
?>