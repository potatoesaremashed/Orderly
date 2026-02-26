<?php
// Recupera tutti gli elementi in bozza non ancora confermati che formano il "Carrello"
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode([]);
    exit;
}

$idTavolo = intval($_SESSION['id_tavolo']);

// Esegue un JOIN massiccio per intrecciare i dettagli crudi dell'ordine con i nomi reali e i prezzi dei piatti dal Menu
$result = $conn->query("SELECT d.id_alimento, d.quantita, a.nome_piatto, a.prezzo
    FROM dettaglio_ordini d
    JOIN ordini o ON d.id_ordine = o.id_ordine
    JOIN alimenti a ON d.id_alimento = a.id_alimento
    WHERE o.id_tavolo = $idTavolo AND o.stato = 'in_attesa'");

echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);
?>