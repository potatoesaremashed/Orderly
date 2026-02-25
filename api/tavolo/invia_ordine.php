<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$datiRicevuti = json_decode(file_get_contents('php://input'), true);

if (empty($datiRicevuti['prodotti'])) {
    echo json_encode(['success' => false, 'message' => 'Il carrello è vuoto.']);
    exit;
}

$idTavolo = $_SESSION['id_tavolo'];
$conn->begin_transaction();

try {
    $creaOrdine = $conn->prepare("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES (?, 'in_attesa', NOW())");
    $creaOrdine->bind_param("i", $idTavolo);

    if (!$creaOrdine->execute()) throw new Exception("Errore creazione ordine principale.");
    $idNuovoOrdine = $conn->insert_id;

    $inserisciDettaglio = $conn->prepare("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita, note) VALUES (?, ?, ?, ?)");

    foreach ($datiRicevuti['prodotti'] as $piatto) {
        $idPiatto = $piatto['id'];
        $quantita = $piatto['qta'];

        if ($quantita > 0) {
            $notePiatto = $piatto['note'] ?? null;
            $inserisciDettaglio->bind_param("iiis", $idNuovoOrdine, $idPiatto, $quantita, $notePiatto);
            if (!$inserisciDettaglio->execute()) throw new Exception("Errore inserimento piatto.");
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'L\'ordine è stato inviato in cucina.']);

} catch (Exception $errore) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Errore critico: ' . $errore->getMessage()]);
}
?>