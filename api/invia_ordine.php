<?php
/**
 * API: Invia Ordine (Conferma)
 * ----------------------------
 * Finalizza l'ordine del cliente.
 * Logica:
 * 1. Cerca l'ordine 'in_attesa' del tavolo.
 * 2. Cambia lo stato in 'in_coda', rendendolo visibile alla Dashboard Cucina.
 * 3. Aggiorna il timestamp dell'ordine.
 */
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Sessione scaduta']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

// Verifica se c'è qualcosa nel carrello (stato 'in_attesa')
$check = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa'");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $id_ordine = $row['id_ordine'];

    // Verifica che l'ordine non sia vuoto (abbia righe di dettaglio)
    $dettagli = $conn->query("SELECT count(*) as num FROM dettaglio_ordini WHERE id_ordine = $id_ordine");
    $data = $dettagli->fetch_assoc();
    
    if($data['num'] > 0) {
        // CAMBIO STATO: Da Carrello a Cucina
        $update = "UPDATE ordini SET stato = 'in_coda', data_ora = NOW() WHERE id_ordine = $id_ordine";
        
        if ($conn->query($update)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore database: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Il carrello è vuoto!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun ordine attivo.']);
}
?>