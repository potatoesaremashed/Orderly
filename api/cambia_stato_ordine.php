<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

// Verifica che sia un cuoco
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'cucina') {
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['stato'] ?? null;

// Valida lo stato
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];
if (!in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Stato non valido']);
    exit;
}

$sql = "UPDATE ordini SET stato = ? WHERE id_ordine = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuovo_stato, $id_ordine);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore aggiornamento']);
}

$stmt->close();
$conn->close();
?>
