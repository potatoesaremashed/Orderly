<?php
// Sottrae una quantità di un piatto dal carrello oppure elimina totalmente il piatto 

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

// 1. Identifica il carrello (ordine in bozza)
$res = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $idTavolo AND stato = 'in_attesa' LIMIT 1");

if ($res->num_rows > 0) {
    $idOrdine = $res->fetch_assoc()['id_ordine'];

    // 2. Controlla quante volte abbiamo inserito questo piatto specifico
    $qRes = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");

    if ($qRes->num_rows > 0) {
        $qta = $qRes->fetch_assoc()['quantita'];

        // 3. Se ne abbiamo 2 o più, riduciamo di 1 (UPDATE)
        // Se è l'ultimo rimasto, non lo mettiamo a 0, ma distruggiamo direttamente la riga SQL per pulizia (DELETE)
        $sql = ($qta > 1)
            ? "UPDATE dettaglio_ordini SET quantita = quantita - 1 WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto"
            : "DELETE FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto";

        echo json_encode($conn->query($sql) ? ['success' => true] : ['success' => false, 'message' => $conn->error]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Piatto non nel carrello.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun carrello attivo.']);
}
?>