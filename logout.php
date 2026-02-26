<?php
// Riapre la sessione corrente in modo da poterla leggere
session_start();

// Se l'utente che sta effettuando il logout è un tavolo...
if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'tavolo' && isset($_SESSION['id_tavolo'])) {
    include "include/conn.php";
    $idTavolo = intval($_SESSION['id_tavolo']);

    // ...allora liberi fisicamente il tavolo nel DB in modo che altri clienti vi si possano sedere
    // e annulliamo l'eventuale token di sicurezza collegato
    $conn->query("UPDATE tavoli SET stato='libero', device_token=NULL WHERE id_tavolo=" . $idTavolo);

    // Eliminazione del cookie che riconosceva il dispositivo
    setcookie('device_token_' . $idTavolo, '', time() - 3600, '/');
}

// Pulisce tutti i dati conservati in memoria per quest'utente e li distrugge
session_unset();
session_destroy();

// Lo rimbalza alla schermata di sblocco (login)
header("Location: index.php");
exit;
?>