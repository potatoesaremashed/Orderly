<?php
/**
 * =========================================
 * API: Get Tavoli
 * =========================================
 * Funzione di "lettura" (Read) che restituisce la situazione attuale dei tavoli.
 * Viene usata dalla dashboard Manager per mostrare i tavoli in tempo reale.
 * 
 * Qui usiamo SQL avanzato (Subquery) per contare gli ordini di ogni tavolo 
 * direttamente dentro la query principale, risparmiando molto tempo al server.
 */

session_start();

// Controllo sicurezza: solo per manager.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato.']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

/**
 * LA QUERY "INTELLIGENTE"
 * Spiegazione per Junior:
 * - COALESCE(campo, 'default'): Se il campo è vuoto (NULL), usa il valore di default.
 * - (SELECT COUNT(*)...): Questa è una Sottoquery. Per ogni tavolo, va a contare 
 *   quanti ordini "attivi" (non ancora pronti) sono presenti.
 */
$sql = "SELECT t.id_tavolo, t.nome_tavolo, t.password, 
        COALESCE(t.stato, 'libero') as stato, 
        COALESCE(t.posti, 4) as posti, t.id_menu,
        (SELECT COUNT(*) FROM ordini o WHERE o.id_tavolo = t.id_tavolo AND o.stato != 'pronto') as ordini_attivi
        FROM tavoli t 
        ORDER BY t.nome_tavolo ASC";

$result = $conn->query($sql);

$tavoli = [];
while ($row = $result->fetch_assoc()) {
    // Aggiungiamo ogni riga trovata all'array $tavoli.
    $tavoli[] = $row;
}

// Spediamo tutto al frontend come JSON.
echo json_encode($tavoli);
?>