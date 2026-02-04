<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

$ruoli_ammessi = ['cuoco', 'manager', 'admin'];
if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) {
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit;
}
// Decodifica dell'output JSON
$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['nuovo_stato'] ?? null;

// Valida lo stato dell'ordine:
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];

if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

// Aggiorna lo stato dell'ordine.
$sql = "UPDATE ordini SET stato = ? WHERE id_ordine = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuovo_stato, $id_ordine);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore SQL']);
}
?>