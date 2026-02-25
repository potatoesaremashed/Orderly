<?php
session_start();

// Blocca brutalmente l'ingresso tramite HTTP 403 in assenza di ruolo abilitato "tavolo" in sessione
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'tavolo') {
    http_response_code(403);
    die("Accesso negato. Autenticazione tavolo richiesta.");
}

// Agganancia dinamicamente il file globale delle credenziali database
require_once __DIR__ . '/../conn.php';
?>
