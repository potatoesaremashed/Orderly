<?php
/**
 * API: cambia_stato_tavolo.php
 * Cambia lo stato di un tavolo (libero/occupato/riservato).
 * Parametri POST: id_tavolo, stato
 */
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

include "../include/conn.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
$stato = trim($_POST['stato'] ?? '');

// Validazione stato
$stati_validi = ['libero', 'occupato', 'riservato'];
if ($id <= 0 || !in_array($stato, $stati_validi)) {
    echo json_encode(['success' => false, 'error' => 'Parametri non validi']);
    exit;
}

$stmt = $conn->prepare("UPDATE tavoli SET stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("si", $stato, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'stato' => $stato]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento']);
}
?>