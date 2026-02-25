<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$idTavolo = intval($_SESSION['id_tavolo']);
$idPiatto = intval($_POST['id_alimento'] ?? 0);

if ($idPiatto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

// Trova il carrello aperto
$queryOrdine = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $idTavolo AND stato = 'in_attesa' LIMIT 1");

if ($queryOrdine->num_rows > 0) {
    $idOrdine = $queryOrdine->fetch_assoc()['id_ordine'];

    // Controlla quantità attuale del piatto nel carrello
    $queryQuantita = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");

    if ($queryQuantita->num_rows > 0) {
        $quantitaAttuale = $queryQuantita->fetch_assoc()['quantita'];

        if ($quantitaAttuale > 1) {
            $sqlAzione = "UPDATE dettaglio_ordini SET quantita = quantita - 1 WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto";
        } else {
            $sqlAzione = "DELETE FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto";
        }

        if ($conn->query($sqlAzione)) {
            echo json_encode(['success' => true, 'message' => 'Carrello aggiornato.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Piatto non trovato nel carrello.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun carrello attivo.']);
}
?>