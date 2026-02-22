<?php
/**
 * =========================================
 * API: Invia Ordine
 * =========================================
 * Gestisce l'invio di un ordine dal tavolo alla cucina.
 * 
 * Logica:
 * 1. Riceve un JSON con la lista dei prodotti dal frontend (tavolo.js)
 * 2. Crea un nuovo record nella tabella 'ordini' con stato 'in_attesa'
 * 3. Inserisce ogni prodotto nella tabella 'dettaglio_ordini'
 * 4. Usa una transazione per garantire che tutto venga salvato correttamente
 *    (se un prodotto fallisce, l'intero ordine viene annullato)
 */

session_start();
header('Content-Type: application/json');
include "../../include/conn.php";

// Verifica che l'utente sia autenticato come tavolo
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Decodifica il JSON inviato dal frontend
// Formato atteso: { "prodotti": [{ "id": 5, "qta": 2, "note": "Senza cipolla" }, ...] }
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica che il carrello non sia vuoto
if (!$data || empty($data['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Carrello vuoto']);
    exit;
}

// Recupera l'ID del tavolo dalla sessione (impostato durante il login in index.php)
$id_tavolo = $_SESSION['id_tavolo'];

// Inizia una transazione: se qualcosa va storto, nessun dato viene salvato
$conn->begin_transaction();

try {
    // 1. Crea l'ordine nella tabella 'ordini' con stato iniziale 'in_attesa'
    $sql_ordine = "INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())";
    $stmt = $conn->prepare($sql_ordine);
    $stmt->bind_param("i", $id_tavolo);

    if (!$stmt->execute()) {
        throw new Exception("Errore creazione ordine");
    }

    // Recupera l'ID dell'ordine appena creato (auto increment)
    $id_ordine_creato = $conn->insert_id;

    // 2. Inserisci ogni prodotto nella tabella 'dettaglio_ordini'
    $sql_dettaglio = "INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)";
    $stmt_det = $conn->prepare($sql_dettaglio);

    foreach ($data['prodotti'] as $p) {
        $id_alimento = $p['id'];
        $quantita = $p['qta'];

        // Inserisci solo se la quantità è positiva
        if ($quantita > 0) {
            $note = isset($p['note']) ? $p['note'] : null;
            $stmt_det->bind_param("iiis", $id_ordine_creato, $id_alimento, $quantita, $note);
            if (!$stmt_det->execute()) {
                throw new Exception("Errore inserimento prodotto ID: " . $id_alimento);
            }
        }
    }

    // 3. Conferma la transazione: tutti i dati vengono salvati definitivamente
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ordine inviato con successo']);

} catch (Exception $e) {
    // In caso di errore, annulla tutte le operazioni (rollback)
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>