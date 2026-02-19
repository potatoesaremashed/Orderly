<?php
/**
 * =========================================
 * API: Rimuovi dal Carrello
 * =========================================
 * Gestisce la rimozione di un piatto dall'ordine "in_attesa" del tavolo corrente.
 * 
 * Logica:
 * - Se la quantità del piatto è > 1, decrementa di 1
 * - Se la quantità è 1, rimuove completamente la riga dal database
 */

session_start();
include "../include/conn.php";

header('Content-Type: application/json');

// 1. Verifica Sicurezza: deve essere un tavolo autenticato
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo' || !isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Recupera dati dalla richiesta POST
$id_tavolo = $_SESSION['id_tavolo'];
$id_alimento = isset($_POST['id_alimento']) ? intval($_POST['id_alimento']) : 0;

// Validazione: l'ID del piatto deve essere positivo
if ($id_alimento <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID alimento non valido']);
    exit;
}

// 2. Cerca l'ordine "aperto" (in_attesa) per questo tavolo
$sql_ordine = "SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa' LIMIT 1";
$res_ordine = $conn->query($sql_ordine);

if ($res_ordine->num_rows > 0) {
    $row = $res_ordine->fetch_assoc();
    $id_ordine = $row['id_ordine'];

    // 3. Controlla la quantità attuale del piatto nell'ordine
    $check_qta = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento");

    if ($check_qta->num_rows > 0) {
        $curr = $check_qta->fetch_assoc();

        if ($curr['quantita'] > 1) {
            // Se ce n'è più di uno, decrementa la quantità di 1
            $sql_delete = "UPDATE dettaglio_ordini SET quantita = quantita - 1 WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        } else {
            // Se è rimasto solo 1, rimuovi completamente la riga dal database
            $sql_delete = "DELETE FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        }

        // Esegui l'operazione
        if ($conn->query($sql_delete)) {
            echo json_encode(['success' => true, 'message' => 'Prodotto rimosso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore database: ' . $conn->error]);
        }
    } else {
        // Il piatto non è stato trovato nell'ordine
        echo json_encode(['success' => false, 'message' => 'Prodotto non trovato nell\'ordine']);
    }

} else {
    // Non esiste un ordine aperto per questo tavolo
    echo json_encode(['success' => false, 'message' => 'Nessun ordine attivo trovato']);
}
?>