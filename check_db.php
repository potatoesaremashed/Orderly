<?php
/**
 * =========================================
 * FILE: check_db.php
 * =========================================
 * Script di test diagnostico (uso interno/sviluppo).
 * Questo file viene utilizzato per verificare la struttura della tabella 'dettaglio_ordini'
 * nel database. Stampa a schermo i nomi e i tipi delle colonne esistenti.
 * 
 * Questo è un esempio di come usare SQL per interrogare la struttura stessa del database
 * (metadati) invece che i dati contenuti nelle tabelle.
 */

include "include/conn.php"; // Genera la connessione al database.

// Esegue una query speciale "DESCRIBE" per leggere la struttura della tabella 'dettaglio_ordini'.
$result = $conn->query("DESCRIBE dettaglio_ordini");

if ($result) {
    echo "--- Struttura Tabella 'dettaglio_ordini' ---\n";
    // Scorriamo i risultati come se fossero righe di una tabella normale.
    while ($row = $result->fetch_assoc()) {
        // 'Field' è il nome della colonna, 'Type' è il tipo (es. int, varchar).
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
}
else {
    // Se c'è un errore (es. la tabella non esiste), stampa un messaggio di errore a schermo.
    echo "Errore SQL: " . $conn->error;
}
?>