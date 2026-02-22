<?php
/**
 * =========================================
 * API: Cambia Stato Tavolo
 * =========================================
 * Permette al gestore di segnare un tavolo come Libero, Occupato o Riservato.
 * Viene usata nella dashboard per avere una visione d'insieme della sala.
 * 
 * Per uno sviluppatore Junior:
 * Questo è un classico esempio di "Update" dove validiamo i dati prima 
 * di inviarli al database per evitare che vengano inseriti stati a caso.
 */

session_start();

// Solo il Manager può gestire la sala.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato: non sei un manager.']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

// --- 1. RECUPERO DATI ---
$id = intval($_POST['id_tavolo'] ?? 0);
$stato = trim($_POST['stato'] ?? '');

// --- 2. VALIDAZIONE ---
/**
 * Lista di sicurezza: l'utente non può inventarsi stati nuovi.
 * Devono corrispondere a quelli previsti nel database (campo ENUM).
 */
$stati_validi = ['libero', 'occupato', 'riservato'];

if ($id <= 0 || !in_array($stato, $stati_validi)) {
    echo json_encode(['success' => false, 'error' => 'Dati non validi (ID o Stato errato).']);
    exit;
}

// --- 3. AGGIORNAMENTO ---
$stmt = $conn->prepare("UPDATE tavoli SET stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("si", $stato, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'nuovo_stato' => $stato]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Impossibile aggiornare il database.']);
}
?>