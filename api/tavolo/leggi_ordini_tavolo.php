<?php
/**
 * =========================================
 * API: Leggi Ordini Tavolo
 * =========================================
 * Restituisce lo storico di tutti gli ordini inviati dal tavolo corrente.
 * Usata dal bottone "Ordini" nella dashboard del tavolo.
 * 
 * Ritorna un array JSON con ogni ordine contenente:
 * - id_ordine, stato (in_attesa/in_preparazione/pronto), ora, data
 * - piatti: lista dei piatti ordinati con nome, quantità, prezzo e note
 * - totale: somma dei prezzi di tutti i piatti nell'ordine
 */

session_start();
header('Content-Type: application/json');
include "../../../include/conn.php";

// Verifica che l'utente sia autenticato come tavolo
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Recupera l'ID del tavolo dalla sessione
$id_tavolo = $_SESSION['id_tavolo'];

// Query: recupera tutti gli ordini del tavolo con i dettagli dei piatti
// JOIN tra ordini → dettaglio_ordini → alimenti per ottenere i nomi e i prezzi
// Ordinamento per data decrescente (gli ordini più recenti appaiono per primi)
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

// Raggruppa i risultati per ordine
// Ogni riga del DB contiene un piatto, ma vogliamo raggruppare i piatti per ordine
$ordini = [];

while ($row = $res->fetch_assoc()) {
    $id = $row['id_ordine'];

    // Se è il primo piatto di questo ordine, crea la struttura base
    if (!isset($ordini[$id])) {
        $ordini[$id] = [
            'id_ordine' => $id,
            'stato' => $row['stato'],
            'ora' => date('H:i', strtotime($row['data_ora'])),     // Formato ora: 14:30
            'data' => date('d/m/Y', strtotime($row['data_ora'])),  // Formato data: 19/02/2026
            'piatti' => [],
            'totale' => 0
        ];
    }

    // Aggiungi il piatto alla lista dell'ordine
    $subtotale = $row['quantita'] * $row['prezzo'];
    $ordini[$id]['piatti'][] = [
        'nome' => $row['nome_piatto'],
        'qta' => $row['quantita'],
        'prezzo' => number_format($row['prezzo'], 2),
        'note' => $row['note'] ?? ''
    ];
    // Somma il subtotale al totale dell'ordine
    $ordini[$id]['totale'] += $subtotale;
}

// Formatta i totali con 2 decimali (es: 18.50)
foreach ($ordini as &$o) {
    $o['totale'] = number_format($o['totale'], 2);
}

// Restituisce l'array come JSON (array_values rimuove le chiavi numeriche)
echo json_encode(array_values($ordini));
?>