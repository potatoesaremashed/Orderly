<?php
/**
 * API: get_tavoli.php
 * Restituisce tutti i tavoli dal database in formato JSON.
 * Usato dalla dashboard manager per popolare la griglia tavoli.
 */
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

include "../../include/conn.php";

header('Content-Type: application/json');

$result = $conn->query("SELECT t.id_tavolo, t.nome_tavolo, t.password, 
    COALESCE(t.stato, 'libero') as stato, 
    COALESCE(t.posti, 4) as posti, t.id_menu,
    (SELECT COUNT(*) FROM ordini o WHERE o.id_tavolo = t.id_tavolo AND o.stato != 'pronto') as ordini_attivi
    FROM tavoli t ORDER BY t.nome_tavolo ASC");

$tavoli = [];
while ($row = $result->fetch_assoc()) {
    $tavoli[] = $row;
}

echo json_encode($tavoli);
?>