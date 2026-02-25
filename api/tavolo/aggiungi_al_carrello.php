<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = $_SESSION['id_tavolo'];
$idPiatto = intval($_POST['id_alimento'] ?? 0);
$quantitaAggiunta = intval($_POST['quantita'] ?? 1);

if ($idPiatto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

// Recupera l'ID dell'ordine aperto (carrello) o creane uno nuovo
$queryCarrello = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $idTavolo AND stato = 'in_attesa' LIMIT 1");

if ($queryCarrello->num_rows > 0) {
    $idOrdine = $queryCarrello->fetch_assoc()['id_ordine'];
} else {
    $conn->query("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES ($idTavolo, 'in_attesa', NOW())");
    $idOrdine = $conn->insert_id;
}

// Verifica se il piatto è già nel carrello e aggiorna o inserisci
$queryPiatto = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");

if ($queryPiatto->num_rows > 0) {
    $conn->query("UPDATE dettaglio_ordini SET quantita = quantita + $quantitaAggiunta WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");
} else {
    $conn->query("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($idOrdine, $idPiatto, $quantitaAggiunta)");
}

echo json_encode(['success' => true, 'message' => 'Piatto aggiunto con successo al carrello!']);
?>