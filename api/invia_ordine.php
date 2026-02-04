<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

// Identifichiamo il tavolo
$id_tavolo = $_SESSION['id_tavolo'] ?? $_SESSION['id_utente'] ?? null;

if (!$id_tavolo) {
    echo json_encode(['success' => false, 'message' => 'Sessione scaduta o tavolo non identificato.']);
    exit;
}

// 1. Cerchiamo l'ordine 'in_attesa' per questo tavolo e lo attiviamo per la cucina ('in_coda')
$sql = "UPDATE ordini 
        SET stato = 'in_coda', data_ora = NOW() 
        WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        // Successo: l'ordine è ora visibile a leggi_ordini_cucina.php
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nessun piatto nel carrello da inviare.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel database: ' . $conn->error]);
}
?>