<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$result = $conn->query("SELECT t.id_tavolo, t.nome_tavolo, t.password,
    COALESCE(t.stato, 'libero') as stato, COALESCE(t.posti, 4) as posti, t.id_menu,
    (SELECT COUNT(*) FROM ordini o WHERE o.id_tavolo = t.id_tavolo AND o.stato != 'pronto') as ordini_attivi
    FROM tavoli t ORDER BY t.nome_tavolo ASC");

echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);
?>