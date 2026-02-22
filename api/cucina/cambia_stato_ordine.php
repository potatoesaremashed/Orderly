<?php
/**
 * =========================================
 * API: Cambia Stato Ordine
 * =========================================
 * Questo file viene chiamato (via AJAX/Fetch) ogni volta che un cuoco 
 * preme un pulsante per cambiare lo stato di un ordine (es. da "In Attesa" a "Pronto").
 * 
 * Le API non mostrano HTML, ma scambiano dati (spesso in formato JSON).
 * Qui usiamo i "Prepared Statements" per proteggere il database da attacchi SQL Injection.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json'); // Comunica al browser che la risposta sarà un oggetto JSON.

/**
 * SICUREZZA E PERMESSI
 * Solo chi lavora nel ristorante (cuoco, manager, admin) può modificare gli ordini.
 */
$ruoli_ammessi = ['cuoco', 'manager', 'admin'];
if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) {
    echo json_encode(['success' => false, 'message' => 'Non hai i permessi per questa operazione.']);
    exit;
}

/**
 * RICEZIONE DATI JSON
 * Dato che inviamo dati come JSON dal frontend, dobbiamo leggerli dal "flusso" (stream) di PHP.
 */
$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['nuovo_stato'] ?? null;

// Gli stati devono corrispondere esattamente a quelli definiti nel database (ENUM).
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];

if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Dati ordine mancanti o non validi.']);
    exit;
}

/**
 * AGGIORNAMENTO DATABASE
 * Prepariamo la query con i segnaposto (?) per la massima sicurezza.
 */
$sql = "UPDATE ordini SET stato = ? WHERE id_ordine = ?";
$stmt = $conn->prepare($sql);

/**
 * bind_param associa le variabili ai punti di domanda:
 * "s" -> stringa (per nuovo_stato)
 * "i" -> intero (per id_ordine)
 */
$stmt->bind_param("si", $nuovo_stato, $id_ordine);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio dei dati: ' . $conn->error]);
}
?>