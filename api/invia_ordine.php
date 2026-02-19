<?php
session_start();
header('Content-Type: application/json');
include "../include/conn.php";

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica che il carrello non sia vuoto
if (!$data || empty($data['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Carrello vuoto']);
    exit;
}

// Recupera l'ID del tavolo dalla sessione
$id_tavolo = $_SESSION['id_utente']; 

$conn->begin_transaction();

try {
    // Crea l'ordine nella tabella 'ordini'
    $sql_ordine = "INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())";
    $stmt = $conn->prepare($sql_ordine);
    $stmt->bind_param("i", $id_tavolo);
    
    if (!$stmt->execute()) {
        throw new Exception("Errore creazione ordine");
    }
    
    // Recupera l'ID dell'ordine appena creato
    $id_ordine_creato = $conn->insert_id;

    // Inserisci in dettaglio_ordine
    $sql_dettaglio = "INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)";
    $stmt_det = $conn->prepare($sql_dettaglio);

    foreach ($data['prodotti'] as $p) {
        $id_alimento = $p['id'];
        $quantita = $p['qta'];
        
        // Inserisci solo se quantità > 0
        if ($quantita > 0) {
            $note = isset($p['note']) ? $p['note'] : null;
            $stmt_det->bind_param("iiis", $id_ordine_creato, $id_alimento, $quantita, $note);
            if (!$stmt_det->execute()) {
                throw new Exception("Errore inserimento prodotto ID: " . $id_alimento);
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ordine inviato con successo']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>