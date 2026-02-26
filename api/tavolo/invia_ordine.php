<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Il carrello è vuoto.']);
    exit;
}

$idTavolo = $_SESSION['id_tavolo'];
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())");
    $stmt->bind_param("i", $idTavolo);
    if (!$stmt->execute())
        throw new Exception("Errore creazione ordine.");
    $idOrdine = $conn->insert_id;

    $det = $conn->prepare("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)");
    foreach ($data['prodotti'] as $p) {
        if ($p['qta'] > 0) {
            $note = $p['note'] ?? null;
            $det->bind_param("iiis", $idOrdine, $p['id'], $p['qta'], $note);
            if (!$det->execute())
                throw new Exception("Errore inserimento piatto.");
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ordine inviato in cucina.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>