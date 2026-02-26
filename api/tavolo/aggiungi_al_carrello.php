<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = $_SESSION['id_tavolo'];
$idPiatto = intval($_POST['id_alimento'] ?? 0);
$qta = intval($_POST['quantita'] ?? 1);

if ($idPiatto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

// Get or create pending order (cart)
$res = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $idTavolo AND stato = 'in_attesa' LIMIT 1");
if ($res->num_rows > 0) {
    $idOrdine = $res->fetch_assoc()['id_ordine'];
} else {
    $conn->query("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES ($idTavolo, 'in_attesa', NOW())");
    $idOrdine = $conn->insert_id;
}

// Update or insert item
$check = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");
if ($check->num_rows > 0) {
    $conn->query("UPDATE dettaglio_ordini SET quantita = quantita + $qta WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");
} else {
    $conn->query("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($idOrdine, $idPiatto, $qta)");
}

echo json_encode(['success' => true, 'message' => 'Aggiunto al carrello.']);
?>