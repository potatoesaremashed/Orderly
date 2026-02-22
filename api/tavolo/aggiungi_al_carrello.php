<?php
/**
 * API: Aggiungi al Carrello
 * -------------------------
 * Questa API gestisce l'inserimento di un piatto nel carrello dell'utente.
 * Logica:
 * 1. Verifica se esiste già un ordine in stato 'in_attesa' per il tavolo corrente.
 * 2. Se non esiste, crea un nuovo record nella tabella 'ordini'.
 * 3. Inserisce o aggiorna la quantità del piatto nella tabella 'dettaglio_ordini'.
 */
session_start();
include "../../../include/conn.php";
header('Content-Type: application/json');

// 1. Verifica Sicurezza
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo' || !isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];
$id_alimento = isset($_POST['id_alimento']) ? intval($_POST['id_alimento']) : 0;
$quantita = isset($_POST['quantita']) ? intval($_POST['quantita']) : 1;

if ($id_alimento <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID alimento non valido']);
    exit;
}

// 2. Cerca un ordine aperto ("in_attesa") per questo tavolo
$sql_ordine = "SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa' LIMIT 1";
$res_ordine = $conn->query($sql_ordine);

if ($res_ordine->num_rows > 0) {
    $row = $res_ordine->fetch_assoc();
    $id_ordine = $row['id_ordine'];
} else {
    // Se non esiste, creane uno nuovo
    $conn->query("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES ($id_tavolo, 'in_attesa', NOW())");
    $id_ordine = $conn->insert_id;
}

// 3. Aggiungi o aggiorna il dettaglio ordine
// Controlliamo se il piatto è già nel carrello per sommare la quantità
$check_dettaglio = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento");

if ($check_dettaglio->num_rows > 0) {
    // Aggiorna quantità
    $sql_update = "UPDATE dettaglio_ordini SET quantita = quantita + $quantita WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
    $conn->query($sql_update);
} else {
    // Inserisci nuovo
    $sql_insert = "INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($id_ordine, $id_alimento, $quantita)";
    $conn->query($sql_insert);
}

echo json_encode(['success' => true, 'message' => 'Aggiunto al carrello']);
?>