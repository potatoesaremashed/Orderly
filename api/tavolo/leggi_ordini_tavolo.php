<?php
// Fornisce il tracciamento in tempo reale degli scontrini virtuali (Lo "Storico")

require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = $_SESSION['id_tavolo'];

// 1. Cerca l'ora in cui il tavolo è stato "Loggato" (abilitato dal manager).
// Serve per evitare che un cliente veda gli ordini dei clienti di STAMATTINA che mangiavano allo stesso tavolo!
$stmtSess = $conn->prepare("SELECT sessione_inizio FROM tavoli WHERE id_tavolo = ?");
$stmtSess->bind_param("i", $idTavolo);
$stmtSess->execute();
$resSess = $stmtSess->get_result()->fetch_assoc();
$orarioLogin = $resSess['sessione_inizio'] ?? '1970-01-01 00:00:00';

// 2. Tira fuori tutta la storia di questo tavolo ma SOLO se l'ordine è POSTERIORE al login attuale di oggi
$stmt = $conn->prepare("SELECT o.id_ordine, o.stato, o.data_ora, d.quantita, a.nome_piatto, a.prezzo, d.note
    FROM ordini o
    JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
    JOIN alimenti a ON d.id_alimento = a.id_alimento
    WHERE o.id_tavolo = ? AND o.data_ora >= ?
    ORDER BY o.data_ora DESC");

$stmt->bind_param("is", $idTavolo, $orarioLogin);
$stmt->execute();
$result = $stmt->get_result();

$ordini = [];

// 3. Raggruppa i risultati piatti per appartenenza di Testata (Stesso Scontrino)
while ($row = $result->fetch_assoc()) {
    $id = $row['id_ordine'];
    if (!isset($ordini[$id])) {
        // Genera array padre per l'ordine intero
        $ordini[$id] = [
            'id_ordine' => $id,
            'stato' => $row['stato'],
            'ora' => date('H:i', strtotime($row['data_ora'])),
            'data' => date('d/m/Y', strtotime($row['data_ora'])),
            'piatti' => [],
            'totale' => 0
        ];
    }
    // E poi "appende" i figli piatti al suo interno
    $ordini[$id]['piatti'][] = [
        'nome' => $row['nome_piatto'],
        'qta' => $row['quantita'],
        'prezzo' => number_format($row['prezzo'], 2),
        'note' => $row['note'] ?? ''
    ];
    // Aggiunge sub-totale finanziario  
    $ordini[$id]['totale'] += $row['quantita'] * $row['prezzo'];
}

// Format finale delle currency (€)
foreach ($ordini as &$o)
    $o['totale'] = number_format($o['totale'], 2);

echo json_encode(array_values($ordini));
?>