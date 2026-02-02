<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

// Leggi input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Sessione scaduta']);
    exit;
}

if (!isset($input['carrello']) || empty($input['carrello'])) {
    echo json_encode(['success' => false, 'message' => 'Carrello vuoto']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

// 1. Crea nuovo ordine
$conn->query("INSERT INTO ordini (id_tavolo, data_ora, stato) VALUES ($id_tavolo, NOW(), 'in_coda')");
$id_ordine = $conn->insert_id;

// 2. Inserisci dettagli
foreach ($input['carrello'] as $item) {
    $id_alim = intval($item['id_alimento']);
    $qta = intval($item['quantita']);
    if($qta > 0) {
        $conn->query("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($id_ordine, $id_alim, $qta)");
    }
}

echo json_encode(['success' => true]);
?>