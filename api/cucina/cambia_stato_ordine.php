<?php
/**
 * =========================================
 * API: Cambia Stato Ordine
 * =========================================
 * Permette alla cucina di aggiornare lo stato di un ordine.
 * Flusso degli stati: in_attesa → in_preparazione → pronto
 * 
 * Accesso consentito a: cuoco, manager, admin.
 * Chiamata dal frontend della cucina (cucina.php) tramite fetch POST in formato JSON.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Verifica permessi: solo cuochi, manager e admin possono cambiare lo stato
$ruoli_ammessi = ['cuoco', 'manager', 'admin'];
if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) {
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit;
}

// Decodifica del body JSON inviato dal frontend
// Il frontend invia: { "id_ordine": 5, "nuovo_stato": "in_preparazione" }
$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['nuovo_stato'] ?? null;

// Validazione: lo stato deve essere uno dei valori consentiti dall'ENUM nel database
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];

if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

// Aggiorna lo stato dell'ordine nel database
// Usa prepared statement per sicurezza contro SQL Injection
$sql = "UPDATE ordini SET stato = ? WHERE id_ordine = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuovo_stato, $id_ordine); // s = stringa (stato), i = intero (id)

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore SQL']);
}
?>