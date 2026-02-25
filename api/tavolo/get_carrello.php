<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode([]);
    exit;
}

$idTavolo = intval($_SESSION['id_tavolo']);

$queryCarrello = "SELECT d.id_alimento, d.quantita, a.nome_piatto, a.prezzo
        FROM dettaglio_ordini d
        JOIN ordini o ON d.id_ordine = o.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.id_tavolo = $idTavolo AND o.stato = 'in_attesa'";

$risultato = $conn->query($queryCarrello);
$prodottiCarrello = $risultato ? $risultato->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode($prodottiCarrello);
?>