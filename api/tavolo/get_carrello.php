<?php
/**
 * API: Recupera Carrello
 * ----------------------
 * Restituisce la lista dei piatti attualmente presenti nell'ordine 'in_attesa'
 * del tavolo loggato. Usata dal frontend per popolare la modale del carrello
 * mantenendo i dati sincronizzati con il database.
 */
session_start();
include "../../../include/conn.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode([]); 
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

// Recupera i piatti dell'ordine "in_attesa"
$sql = "SELECT d.id_alimento, d.quantita, a.nome_piatto, a.prezzo 
        FROM dettaglio_ordini d
        JOIN ordini o ON d.id_ordine = o.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.id_tavolo = $id_tavolo AND o.stato = 'in_attesa'";

$res = $conn->query($sql);
$carrello = [];

while($row = $res->fetch_assoc()) {
    $carrello[] = $row;
}

echo json_encode($carrello);
?>