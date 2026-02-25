<?php
session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Manda subito in crash script restituendo l'errore se MySQL è fallato
if ($conn->connect_error) {
    echo json_encode(["error" => "Il database non risponde: " . $conn->connect_error]);
    exit;
}

// Pesca tutte le righe di ordini pendenti o in carico mixando assieme tavoli e dati alimento (JOIN compatto)
$sql = "SELECT o.id_ordine, o.id_tavolo, t.nome_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto, d.note
        FROM ordini o
        LEFT JOIN tavoli t ON o.id_tavolo = t.id_tavolo
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_attesa', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);

// Informa il frontend se la query fa un buco a livello di logica in backend
if (!$res) {
    echo json_encode(["error" => "Errore nella lettura degli ordini: " . $conn->error]);
    exit;
}

$ordini = [];

// Accorpa le migliaia di righe piatti per riassumerle in un singolo blocco scontrino "ordinato" (per id)
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_ordine'];

        // Fabbrica l'anagrafica di base se la vedi per la prima volta
        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'id_ordine' => $id,
                'tavolo' => !empty($row['nome_tavolo']) ? $row['nome_tavolo'] : "Tavolo " . $row['id_tavolo'],
                'stato' => $row['stato'],
                'ora' => date('H:i', strtotime($row['data_ora'])),
                'piatti' => []
            ];
        }

        // Sparagli dentro le info sul piatto 
        $ordini[$id]['piatti'][] = [
            'nome' => $row['nome_piatto'],
            'qta' => $row['quantita'],
            'note' => $row['note'] ?? ''
        ];
    }
}

// Converti l'array da chiave testuale (assoc) a progressivo posizionale standard e sputalo in json per JS
echo json_encode(array_values($ordini));
?>