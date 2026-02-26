<?php
// Converte la bozza del carrello in "Ordine" ufficiale per la cucina, scalandolo dal controllo del cliente

require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

// Riceve JSON puro via fetch, quindi usa file_get_contents anziche $_POST
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Il carrello è vuoto.']);
    exit;
}

$idTavolo = $_SESSION['id_tavolo'];

// Si avvia una TRANSAZIONE SQL: se un insert fallisce a metà, tutto l'ordine viene annullato! (Previene ordini scatolati a metà)
$conn->begin_transaction();

try {
    // 1. Crea la "Testata" della comanda ufficiale
    $stmt = $conn->prepare("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())");
    $stmt->bind_param("i", $idTavolo);
    if (!$stmt->execute())
        throw new Exception("Errore creazione ordine.");

    // Memorizza l'ID appena generato
    $idOrdine = $conn->insert_id;

    // Prepara lo statement per le righe interne 
    $det = $conn->prepare("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)");

    // 2. Itera su ciascun piatto nel carrello JSON ed effettua l'INSERT
    foreach ($data['prodotti'] as $p) {
        if ($p['qta'] > 0) {
            $note = $p['note'] ?? null;
            $det->bind_param("iiis", $idOrdine, $p['id'], $p['qta'], $note);
            if (!$det->execute())
                throw new Exception("Errore inserimento piatto.");
        }
    }

    // Se tutto va a buon fine la transazione viene "Solidificata" (Commit)
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ordine inviato in cucina.']);
} catch (Exception $e) {
    // In caso di panico o problemi, fa "Rollback" stradando l'ordine
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>