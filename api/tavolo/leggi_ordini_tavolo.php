<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = $_SESSION['id_tavolo'];
// Recupera il momento in cui il tavolo ha fatto il login
$orarioLogin = $_SESSION['login_time'] ?? '1970-01-01 00:00:00'; 

$queryOrdini = "SELECT o.id_ordine, o.stato, o.data_ora, d.quantita, a.nome_piatto, a.prezzo, d.note
        FROM ordini o
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.id_tavolo = ? AND o.data_ora >= ?
        ORDER BY o.data_ora DESC";

$stmt = $conn->prepare($queryOrdini);
$stmt->bind_param("is", $idTavolo, $orarioLogin);
$stmt->execute();
$risultato = $stmt->get_result();

$storicoOrdini = [];

while ($datiPiatto = $risultato->fetch_assoc()) {
    $idOrdine = $datiPiatto['id_ordine'];

    // Inizializza l'ordine se è il primo piatto che processiamo per questo ID
    if (!isset($storicoOrdini[$idOrdine])) {
        $storicoOrdini[$idOrdine] = [
            'id_ordine' => $idOrdine,
            'stato' => $datiPiatto['stato'],
            'ora' => date('H:i', strtotime($datiPiatto['data_ora'])),
            'data' => date('d/m/Y', strtotime($datiPiatto['data_ora'])),
            'piatti' => [],
            'totale' => 0
        ];
    }

    $costoTotalePiatto = $datiPiatto['quantita'] * $datiPiatto['prezzo'];

    $storicoOrdini[$idOrdine]['piatti'][] = [
        'nome' => $datiPiatto['nome_piatto'],
        'qta' => $datiPiatto['quantita'],
        'prezzo' => number_format($datiPiatto['prezzo'], 2),
        'note' => $datiPiatto['note'] ?? ''
    ];

    $storicoOrdini[$idOrdine]['totale'] += $costoTotalePiatto;
}

// Formatta i totali di ogni ordine in euro
foreach ($storicoOrdini as &$ordine) {
    $ordine['totale'] = number_format($ordine['totale'], 2);
}

echo json_encode(array_values($storicoOrdini));
?>