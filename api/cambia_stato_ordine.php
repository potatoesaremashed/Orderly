<?php
/**
 * API: Avanzamento Stato Ordine
 * -----------------------------
 * Gestisce il flusso di lavoro della cucina.
 * Permette di passare un ordine da 'in_coda' -> 'in_preparazione' -> 'pronto'.
 */
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_ordine']) || !isset($data['nuovo_stato'])) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$id_ordine = intval($data['id_ordine']);
$nuovo_stato = $conn->real_escape_string($data['nuovo_stato']);

// Lista stati validi per sicurezza
$stati_validi = ['in_preparazione', 'pronto', 'completato'];
if (!in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Stato non valido']);
    exit;
}

$sql = "UPDATE ordini SET stato = '$nuovo_stato' WHERE id_ordine = $id_ordine";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>