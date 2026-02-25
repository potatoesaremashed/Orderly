<?php
// Esegui test diagnostico per stampare a schermo la struttura della tabella 'dettaglio_ordini'
include "include/conn.php";

// Lancia la richiesta speciale per farti descrivere i metadati (colonne e tipi di dato)
$result = $conn->query("DESCRIBE dettaglio_ordini");

// Mostra a video l'elenco dei campi se la tabella esiste, o cattura l'eccezione
if ($result) {
    echo "--- Struttura Tabella 'dettaglio_ordini' ---\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Errore SQL: " . $conn->error;
}
?>