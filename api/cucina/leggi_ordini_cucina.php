<?php
/**
 * =========================================
 * API: Leggi Ordini Cucina
 * =========================================
 * Restituisce tutti gli ordini attivi (in_attesa e in_preparazione) per la dashboard della cucina.
 * La cucina chiama questa API ogni 3 secondi per aggiornare il kanban board in tempo reale.
 * 
 * Ritorna un array JSON con ogni ordine contenente:
 * - id_ordine, tavolo (nome del tavolo), stato, ora
 * - piatti: lista dei piatti con nome, quantità e note
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Controllo connessione al database
if ($conn->connect_error) {
    echo json_encode(["error" => "Connessione DB fallita: " . $conn->connect_error]);
    exit;
}

// Query: recupera ordini attivi con i dettagli dei piatti e il nome del tavolo
// LEFT JOIN su tavoli: se il tavolo è stato cancellato, l'ordine si vede comunque
// Filtriamo solo ordini 'in_attesa' e 'in_preparazione' (non quelli già pronti)
$sql = "SELECT o.id_ordine, o.id_tavolo, t.nome_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto, d.note
        FROM ordini o
        LEFT JOIN tavoli t ON o.id_tavolo = t.id_tavolo
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_attesa', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);

// Gestione errore query
if (!$res) {
    echo json_encode(["error" => "Errore SQL: " . $conn->error]);
    exit;
}

// Raggruppa i risultati per ordine (come in leggi_ordini_tavolo.php)
$ordini = [];

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_ordine'];

        // Se il tavolo è stato cancellato dal DB, mostra l'ID grezzo con "(?)"
        $nomeTavolo = !empty($row['nome_tavolo']) ? $row['nome_tavolo'] : "Tavolo " . $row['id_tavolo'] . " (?)";

        // Crea la struttura base dell'ordine se non esiste ancora
        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'id_ordine' => $id,
                'tavolo' => $nomeTavolo,
                'stato' => $row['stato'],
                'ora' => date('H:i', strtotime($row['data_ora'])),
                'piatti' => []
            ];
        }

        // Aggiungi il piatto alla lista dell'ordine
        $ordini[$id]['piatti'][] = [
            'nome' => $row['nome_piatto'],
            'qta' => $row['quantita'],
            'note' => $row['note'] ?? ''
        ];
    }
}

// Restituisce l'array come JSON
echo json_encode(array_values($ordini));
?>