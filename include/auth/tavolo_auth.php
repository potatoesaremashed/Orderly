<?php
// Avvia la lettura della sessione corrente
session_start();

// Controlla se l'utente che effettua la richiesta è loggato come 'tavolo'
// Utile per proteggere le chiamate API del carrello o ordinazioni
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'tavolo') {
    http_response_code(403);
    die("Accesso negato. Usa un account tavolo valido.");
}

// Importa il setup del database per chi lo include
require_once __DIR__ . '/../conn.php';
?>