<?php
// Avvia la sessione se non è già attiva
session_start();

// Verifica che l'utente attuale abbia il ruolo di 'manager'
// In caso contrario nega l'accesso e blocca lo script
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'manager') {
    http_response_code(403);
    die("Accesso negato. Solo i manager possono visualizzare questa pagina.");
}

// Rende automaticamente disponibile la connessione al database per tutti i file che includono questo
require_once __DIR__ . '/../conn.php';
?>