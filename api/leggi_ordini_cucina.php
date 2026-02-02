<?php
/**
 * API: Polling Cucina
 * -------------------
 * Chiamata ciclicamente dalla Dashboard Cucina (ogni 5 sec).
 * Restituisce un JSON raggruppato per ordine contenente:
 * - Ordini 'in_coda' (Nuovi)
 * - Ordini 'in_preparazione' (In lavorazione)
 * Utile per visualizzare i piatti raggruppati per tavolo.
 */
session_start();
include "../include/conn.php";
header('Content-Type: application/json');

// Verifica permessi (opzionale: se hai un login 'cucina', scommenta la riga sotto)
// if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'cucina') { echo json_encode([]); exit; }

// Seleziona ordini in stato 'in_coda' o 'in_preparazione'
// Ordina per data (i più vecchi prima)
$sql = "SELECT o.id_ordine, o.id_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto
        FROM ordini o
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_coda', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);

$ordini = [];

// Raggruppa le righe piatte in una struttura a oggetti (Ordine -> Lista Piatti)
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

// Restituisce un array indicizzato (rimuove le chiavi ID dell'array associativo)
echo json_encode(array_values($ordini));
?>