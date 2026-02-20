<?php
/**
 * =========================================
 * FILE: check_db.php
 * =========================================
 * Script di test diagnostico (uso interno/sviluppo).
 * Questo file viene utilizzato per verificare la struttura della tabella 'dettaglio_ordini'
 * nel database. Stampa a schermo i nomi e i tipi delle colonne esistenti.
 */

include "include/conn.php"; // Genera la connessione al database

// Esegue una query speciale "DESCRIBE" per leggere la struttura della tabella 'dettaglio_ordini'
$result = $conn->query("DESCRIBE dettaglio_ordini");

if ($result) {
    // Se la lettura va a buon fine, scorre ogni colonna e ne stampa il nome e il tipo di dato (es. INT, VARCHAR)
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    // Se c'è un errore (es. la tabella non esiste), stampa un messaggio di errore a schermo
    echo "Error: " . $conn->error;
}
?>