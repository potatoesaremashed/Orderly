<?php
// Visualizza tutte le comande giunte in cucina, processandole per le colonne 

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(["error" => "DB error: " . $conn->connect_error]);
    exit;
}

// Maxi Query SQL "Cucinetta" - Cerca tutti gli ordini che non sono stati marchiati "pronto"
// Facendo LEFT JOIN in modo da pescare anche se c'è un commento ("note") a latere di una pietanza singola o solo nomiclatori
$sql = "SELECT o.id_ordine, o.id_tavolo, t.nome_tavolo, o.stato, o.data_ora,
            d.quantita, a.nome_piatto, d.note
        FROM ordini o
        LEFT JOIN tavoli t ON o.id_tavolo = t.id_tavolo
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_attesa', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);
if (!$res) {
    echo json_encode(["error" => $conn->error]);
    exit;
}

$ordini = [];

// Raggruppamento per numero Ticket Unico
while ($row = $res->fetch_assoc()) {
    $id = $row['id_ordine'];
    if (!isset($ordini[$id])) {
        // Scatola Generale
        $ordini[$id] = [
            'id_ordine' => $id,
            'tavolo' => !empty($row['nome_tavolo']) ? $row['nome_tavolo'] : "Tavolo " . $row['id_tavolo'],
            'stato' => $row['stato'],
            'ora' => date('H:i', strtotime($row['data_ora'])),
            'piatti' => []
        ];
    }

    // Piatti inseriti dentro la Scatola Generale Ticket
    $ordini[$id]['piatti'][] = ['nome' => $row['nome_piatto'], 'qta' => $row['quantita'], 'note' => $row['note'] ?? ''];
}

echo json_encode(array_values($ordini));
?>