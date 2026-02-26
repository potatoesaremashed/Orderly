<?php
// Endpoint in uso in kanban cucia per dire "Spunta, sto preparando", "Spunta, è pronto!"

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Autorizzazione di blocco
$ruoli_ammessi = ['cuoco', 'manager', 'admin'];
if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['nuovo_stato'] ?? null;

// Pipeline standard su cui viaggia una comanda 
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];

if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi.']);
    exit;
}

// Inserisce il comando che retrocede di colore il badge sulla schermata dei tavoli in attesa fuori dalla cucina
$stmt = $conn->prepare("UPDATE ordini SET stato = ? WHERE id_ordine = ?");
$stmt->bind_param("si", $nuovo_stato, $id_ordine);

echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Errore: ' . $conn->error]);
?>