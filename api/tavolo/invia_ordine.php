<?php
/**
 * =========================================
 * API: Invia Ordine
 * =========================================
 * Trasforma il carrello in un vero ordine pronto per la cucina.
 * 
 * Per uno sviluppatore Junior:
 * L'operazione "Invia Ordine" è critica. Usiamo le "Transazioni SQL". 
 * Immagina un bonifico: non puoi togliere soldi da un conto e non metterli nell'altro. 
 * Se qualcosa fallisce a metà (es. salta la luce), il database annulla 
 * tutto (Rollback) per evitare di avere ordini "a metà".
 */

session_start();
header('Content-Type: application/json');
include "../../include/conn.php";

// Verifica che l'utente sia autenticato come tavolo.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato: effettua il login al tavolo.']);
    exit;
}

// Decodifica il JSON inviato dal frontend.
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Attenzione: il carrello è vuoto.']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

// --- 1. INIZIO TRANSAZIONE ---
$conn->begin_transaction();

try {
    // A. Crea la "testata" dell'ordine (id_ordine, stato, ora).
    $sql_ordine = "INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())";
    $stmt = $conn->prepare($sql_ordine);
    $stmt->bind_param("i", $id_tavolo);

    if (!$stmt->execute()) {
        throw new Exception("Errore durante la creazione dell'ordine principale.");
    }

    $id_ordine_creato = $conn->insert_id; // Recuperiamo l'ID appena generato.

    // B. Inserisci ogni singolo piatto nella tabella dei dettagli.
    $sql_dettaglio = "INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)";
    $stmt_det = $conn->prepare($sql_dettaglio);

    foreach ($data['prodotti'] as $p) {
        $id_alimento = $p['id'];
        $quantita = $p['qta'];

        if ($quantita > 0) {
            $note = isset($p['note']) ? $p['note'] : null;
            // "iiis" -> Intero, Intero, Intero, Stringa.
            $stmt_det->bind_param("iiis", $id_ordine_creato, $id_alimento, $quantita, $note);
            if (!$stmt_det->execute()) {
                throw new Exception("Errore nell'inserimento del piatto ID: " . $id_alimento);
            }
        }
    }

    // --- 2. CONFERMA (COMMIT) ---
    // Se siamo arrivati qui senza errori, salviamo tutto definitivamente.
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ottimo! Il tuo ordine è stato inviato in cucina.']);

}
catch (Exception $e) {
    // --- 3. ANNULLAMENTO (ROLLBACK) ---
    // Se scatta un errore in qualsiasi punto del blocco 'try', cancelliamo tutto.
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Errore critico: ' . $e->getMessage()]);
}
?>