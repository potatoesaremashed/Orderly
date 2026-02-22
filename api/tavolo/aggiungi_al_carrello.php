<?php
/**
 * =========================================
 * API: Aggiungi al Carrello
 * =========================================
 * Questa API gestisce l'inserimento di un piatto nel carrello dell'utente.
 * 
 * Logica per lo sviluppo:
 * 1. Verifica se esiste già un ordine in stato 'in_attesa' (il nostro "carrello aperto").
 * 2. Se non esiste, crea un nuovo record nella tabella 'ordini'.
 * 3. Inserisce o aggiorna la quantità del piatto scelto.
 * 
 * Il "carrello" in questo progetto è salvato direttamente nel database. 
 * Questo permette al cliente di non perdere i piatti se ricarica la pagina.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// --- 1. VERIFICA SICUREZZA ---
// Il cliente deve essere loggato al suo tavolo per poter ordinare.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo' || !isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Devi essere loggato al tavolo per aggiungere piatti.']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo']; // Recuperiamo l'ID del tavolo dalla sessione.
$id_alimento = isset($_POST['id_alimento']) ? intval($_POST['id_alimento']) : 0;
$quantita = isset($_POST['quantita']) ? intval($_POST['quantita']) : 1;

if ($id_alimento <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

/**
 * --- 2. GESTIONE ORDINE APERTO ---
 * Un ordine in stato 'in_attesa' funge da carrello. 
 * Controlliamo se ce n'è già uno per questo tavolo.
 */
$sql_ordine = "SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa' LIMIT 1";
$res_ordine = $conn->query($sql_ordine);

if ($res_ordine->num_rows > 0) {
    // Usiamo l'ordine già esistente.
    $row = $res_ordine->fetch_assoc();
    $id_ordine = $row['id_ordine'];
}
else {
    // Se non c'è un ordine aperto, ne creiamo uno nuovo "vuoto".
    $conn->query("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES ($id_tavolo, 'in_attesa', NOW())");
    $id_ordine = $conn->insert_id; // Otteniamo l'ID appena generato dal database.
}

/**
 * --- 3. AGGIUNGI O AGGIORNA IL PIATTO ---
 * Se il piatto è già nel carrello, aumentiamo solo la quantità. 
 * Altrimenti, creiamo una nuova riga nel dettaglio degli ordini.
 */
$check_dettaglio = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento");

if ($check_dettaglio->num_rows > 0) {
    // Il piatto c'è già: somma la nuova quantità a quella vecchia.
    $sql_update = "UPDATE dettaglio_ordini SET quantita = quantita + $quantita WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
    $conn->query($sql_update);
}
else {
    // Il piatto non c'è: inseriscilo per la prima volta.
    $sql_insert = "INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($id_ordine, $id_alimento, $quantita)";
    $conn->query($sql_insert);
}

echo json_encode(['success' => true, 'message' => 'Piatto aggiunto con successo al carrello!']);
?>