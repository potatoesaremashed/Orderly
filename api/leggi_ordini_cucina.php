<?php
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

// --- DEBUG DI CONNESSIONE ---
if ($conn->connect_error) {
    echo json_encode(["error" => "Connessione DB fallita: " . $conn->connect_error]);
    exit;
}

// Query Modificata:
// 1. LEFT JOIN su tavoli (così se il tavolo è stato cancellato o l'ID è strano, l'ordine si vede lo stesso)
// 2. Selezioniamo anche o.id_tavolo per sicurezza
$sql = "SELECT o.id_ordine, o.id_tavolo, t.nome_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto, d.note
        FROM ordini o
        LEFT JOIN tavoli t ON o.id_tavolo = t.id_tavolo
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_attesa', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);

// --- DEBUG QUERY FALLITA ---
if (!$res) {
    echo json_encode(["error" => "Errore SQL: " . $conn->error]);
    exit;
}

$ordini = [];

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_ordine'];
        
        // LOGICA DI SALVATAGGIO:
        // Se la JOIN non trova il nome (es. tavolo cancellato), usiamo l'ID grezzo + (?)
        $nomeTavolo = !empty($row['nome_tavolo']) ? $row['nome_tavolo'] : "Tavolo " . $row['id_tavolo'] . " (?)";

        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'id_ordine' => $id,
                'tavolo' => $nomeTavolo, 
                'stato' => $row['stato'],
                'ora' => date('H:i', strtotime($row['data_ora'])),
                'piatti' => []
            ];
        }
        
        $ordini[$id]['piatti'][] = [
            'nome' => $row['nome_piatto'],
            'qta' => $row['quantita'],
            'note' => $row['note'] ?? ''
        ];
    }
}

echo json_encode(array_values($ordini));
?>