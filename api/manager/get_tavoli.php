<?php
// Endpoint in background (Polling) che scarica lo "stato dell'arte" di tutte le entità Tavoli 

require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

// Operazione di lettura asincrona: scarichiamo tutto per dipingere le Card sul pannello Manager
// Nello stesso colpo calcoliamo, per ogni tavolo, il numero di ordini "attivi" (Non ancora pronti e serviti caldi)
$result = $conn->query("SELECT t.id_tavolo, t.nome_tavolo, t.password,
    COALESCE(t.stato, 'libero') as stato, COALESCE(t.posti, 4) as posti, t.id_menu,
    (SELECT COUNT(*) FROM ordini o WHERE o.id_tavolo = t.id_tavolo AND o.stato != 'pronto') as ordini_attivi
    FROM tavoli t ORDER BY t.nome_tavolo ASC");

// Rilascia JSON comprensibile a fetch() in Javascript
echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);
?>