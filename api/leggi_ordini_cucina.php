<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');
    
$sql = "SELECT o.id_ordine, o.id_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto
        FROM ordini o
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_coda', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);
$ordini = [];

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_ordine'];
        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'id_ordine' => $id,
                'tavolo' => $row['id_tavolo'],
                'stato' => $row['stato'],
                'ora' => date('H:i', strtotime($row['data_ora'])),
                'piatti' => []
            ];
        }
        $ordini[$id]['piatti'][] = [
            'nome' => $row['nome_piatto'],
            'qta' => $row['quantita']
        ];
    }
}
echo json_encode(array_values($ordini));
?>