<?php
/**
 * =========================================
 * API: Leggi Ordini Tavolo
 * =========================================
 * Mostra al cliente tutti gli ordini che ha fatto durante la sua permanenza.
 * 
 * Per uno sviluppatore Junior:
 * Quando leggiamo dal database, i piatti arrivano uno per riga. 
 * Se hai ordinato 3 piatti in un unico ordine, avrai 3 righe nel database.
 * Qui usiamo un array associativo per "raggruppare" i piatti sotto un unico 
 * ID ordine, rendendo la lista ordinata e leggibile per l'utente.
 */

session_start();
header('Content-Type: application/json');
include "../../include/conn.php";

// Verifica autorizzazione.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

/**
 * QUERY DI STORICO
 * Uniamo ORDINI, DETTAGLI e ALIMENTI.
 * Ordiniamo per tempo (DESC) così i più recenti sono in alto.
 */
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

    // Se è il primo piatto che troviamo per questo ID ordine, creiamo la struttura.
    if (!isset($ordini[$id])) {
        $ordini[$id] = [
            'id_ordine' => $id,
            'stato' => $row['stato'], // in_attesa, in_preparazione, pronto.
            'ora' => date('H:i', strtotime($row['data_ora'])),
            'data' => date('d/m/Y', strtotime($row['data_ora'])),
            'piatti' => [],
            'totale' => 0
        ];
    }

    // Calcoliamo il costo di questo singolo piatto (prezzo * quantità).
    $subtotale = $row['quantita'] * $row['prezzo'];

    // Aggiungiamo i dettagli del piatto all'ordine corrispondente.
    $ordini[$id]['piatti'][] = [
        'nome' => $row['nome_piatto'],
        'qta' => $row['quantita'],
        'prezzo' => number_format($row['prezzo'], 2),
        'note' => $row['note'] ?? ''
    ];

    // Incrementiamo il conto totale di quell'ordine.
    $ordini[$id]['totale'] += $subtotale;
}

// Formattiamo il totale finale in formato valuta (es. 12.50).
foreach ($ordini as &$o) {
    $o['totale'] = number_format($o['totale'], 2);
}

// Spediamo tutto al frontend.
echo json_encode(array_values($ordini));
?>