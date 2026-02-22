<?php
/**
 * =========================================
 * API: Leggi Ordini Cucina
 * =========================================
 * Fornisce alla dashboard della cucina la lista di tutti gli ordini da preparare.
 * La cucina "interroga" (polling) questa API ogni pochi secondi per vedere se ci sono nuovi ordini.
 * 
 * Questo è un esempio di query complessa (JOIN) che unisce tre tabelle diverse 
 * per ottenere tutti i dati necessari (nome piatto, quantità, note) in un colpo solo.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Controllo connessione al database.
if ($conn->connect_error) {
    echo json_encode(["error" => "Il database non risponde: " . $conn->connect_error]);
    exit;
}

/**
 * QUERY CON JOIN
 * Stiamo unendo le tabelle:
 * - ordini (o): i dati generali (stato, ora)
 * - tavoli (t): per sapere chi ha ordinato
 * - dettaglio_ordini (d): per sapere quali piatti ci sono nell'ordine
 * - alimenti (a): per sapere come si chiamano quei piatti
 */
$sql = "SELECT o.id_ordine, o.id_tavolo, t.nome_tavolo, o.stato, o.data_ora, 
               d.quantita, a.nome_piatto, d.note
        FROM ordini o
        LEFT JOIN tavoli t ON o.id_tavolo = t.id_tavolo
        JOIN dettaglio_ordini d ON o.id_ordine = d.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.stato IN ('in_attesa', 'in_preparazione')
        ORDER BY o.data_ora ASC";

$res = $conn->query($sql);

if (!$res) {
    echo json_encode(["error" => "Errore nella lettura degli ordini: " . $conn->error]);
    exit;
}

/**
 * RAGGRUPPAMENTO RISULTATI
 * Dato che la query restituisce una riga per ogni PIATTO, ma noi vogliamo una lista di ORDINI,
 * dobbiamo raggruppare i dati in un array associativo usando l'ID dell'ordine.
 */
$ordini = [];

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_ordine'];

        // Se è la prima volta che incontriamo questo ID ordine, creiamo la sua "scatola".
        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'id_ordine' => $id,
                'tavolo' => !empty($row['nome_tavolo']) ? $row['nome_tavolo'] : "Tavolo " . $row['id_tavolo'],
                'stato' => $row['stato'],
                'ora' => date('H:i', strtotime($row['data_ora'])),
                'piatti' => []
            ];
        }

        // Aggiungiamo il piatto specifico alla lista dell'ordine corrispondente.
        $ordini[$id]['piatti'][] = [
            'nome' => $row['nome_piatto'],
            'qta' => $row['quantita'],
            'note' => $row['note'] ?? ''
        ];
    }
}

// Trasformiamo l'array associativo in un array semplice (senza chiavi ID) prima di inviarlo.
echo json_encode(array_values($ordini));
?>