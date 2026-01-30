<?php
session_start();
include "../include/conn.php";

header('Content-Type: application/json');

// 1. Verifica Sicurezza: Deve essere un tavolo loggato
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo' || !isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];
$id_alimento = isset($_POST['id_alimento']) ? intval($_POST['id_alimento']) : 0;

if ($id_alimento <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID alimento non valido']);
    exit;
}

// 2. Trova l'ordine "aperto" (in_attesa) per questo tavolo
$sql_ordine = "SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa' LIMIT 1";
$res_ordine = $conn->query($sql_ordine);

if ($res_ordine->num_rows > 0) {
    $row = $res_ordine->fetch_assoc();
    $id_ordine = $row['id_ordine'];

    // 3. Rimuovi la voce dalla tabella dettaglio_ordini
    // Nota: Se la quantità è > 1, potresti voler decrementare, ma "Rimuovi" spesso implica togliere la riga o decrementare.
    // Qui implementiamo la rimozione completa della riga per semplicità, o decremento se gestito.
    
    // Controlliamo la quantità attuale
    $check_qta = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento");
    
    if ($check_qta->num_rows > 0) {
        $curr = $check_qta->fetch_assoc();
        
        if ($curr['quantita'] > 1) {
            // Se ce n'è più di uno, decrementa
            $sql_delete = "UPDATE dettaglio_ordini SET quantita = quantita - 1 WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        } else {
            // Se è 1, rimuovi la riga
            $sql_delete = "DELETE FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        }

        if ($conn->query($sql_delete)) {
            echo json_encode(['success' => true, 'message' => 'Prodotto rimosso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore database: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Prodotto non trovato nell\'ordine']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Nessun ordine attivo trovato']);
}
?>