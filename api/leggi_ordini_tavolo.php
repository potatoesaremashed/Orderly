<?php
session_start();
header('Content-Type: application/json');
include "../include/conn.php";

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

$sql = "SELECT o.id_ordine, o.stato, o.data_ora,
               d.quantita, a.nome_piatto, a.prezzo, d.note
        FROM ordini o
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.id_tavolo = ?
        ORDER BY o.data_ora DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_tavolo);
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];

while ($row = $res->fetch_assoc()) {
    $id = $row['id_ordine'];

    if (!isset($ordini[$id])) {
        $ordini[$id] = [
            'id_ordine' => $id,
            'stato' => $row['stato'],
            'ora' => date('H:i', strtotime($row['data_ora'])),
            'data' => date('d/m/Y', strtotime($row['data_ora'])),
            'piatti' => [],
            'totale' => 0
        ];
    }

    $subtotale = $row['quantita'] * $row['prezzo'];
    $ordini[$id]['piatti'][] = [
        'nome' => $row['nome_piatto'],
        'qta' => $row['quantita'],
        'prezzo' => number_format($row['prezzo'], 2),
        'note' => $row['note'] ?? ''
    ];
    $ordini[$id]['totale'] += $subtotale;
}

// Format totals
foreach ($ordini as &$o) {
    $o['totale'] = number_format($o['totale'], 2);
}

echo json_encode(array_values($ordini));
?>