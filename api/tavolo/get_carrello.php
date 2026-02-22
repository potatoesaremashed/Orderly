<?php
/**
 * =========================================
 * API: Recupera Carrello
 * =========================================
 * Questa API fornisce al frontend la lista dei piatti "parcheggiati" nel carrello.
 * Viene usata per mostrare il riepilogo prima di inviare l'ordine definitivo in cucina.
 * 
 * Per uno sviluppatore Junior:
 * Qui usiamo una query JOIN che unisce tre tabelle:
 * - dettaglio_ordini (d): per le quantità.
 * - ordini (o): per filtrare solo l'ordine 'in_attesa' di questo tavolo.
 * - alimenti (a): per prendere i nomi e i prezzi dei piatti.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Se l'utente non è loggato come tavolo, restituiamo un carrello vuoto.
if (!isset($_SESSION['id_tavolo'])) {
    echo json_encode([]);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];

/**
 * QUERY DI RECUPERO
 * Prendiamo i dettagli dei piatti collegati all'ordine aperto per questo tavolo.
 */
$sql = "SELECT d.id_alimento, d.quantita, a.nome_piatto, a.prezzo 
        FROM dettaglio_ordini d
        JOIN ordini o ON d.id_ordine = o.id_ordine
        JOIN alimenti a ON d.id_alimento = a.id_alimento
        WHERE o.id_tavolo = $id_tavolo AND o.stato = 'in_attesa'";

$res = $conn->query($sql);
$carrello = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $carrello[] = $row; // Aggiungiamo ogni piatto trovato alla lista.
    }
}

// Rispondiamo al frontend con la lista in formato JSON.
echo json_encode($carrello);
?>