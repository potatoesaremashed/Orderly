<?php
session_start();

// Verifica il pass basico e sbarra l'accesso uccidendo lo script se l'utente non è manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'manager') {
    http_response_code(403);
    die("Accesso negato. Solo i manager possono eseguire questa azione.");
}

// Richiama ed allaccia il database con il riferimento diretto al connettore SQL
require_once __DIR__ . '/../conn.php';
?>
