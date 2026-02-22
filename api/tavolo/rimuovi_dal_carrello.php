<?php
/**
 * =========================================
 * API: Rimuovi dal Carrello
 * =========================================
 * Gestisce la rimozione o la diminuzione di quantità di un piatto nel carrello.
 * 
 * Quando un utente preme "rimuovi", non vogliamo sempre cancellare la riga. 
 * Se ha ordinato 3 pizze, premendo rimuovi deve scendere a 2. 
 * Solo quando arriviamo a 1, la pressione successiva cancella fisicamente 
 * il piatto dal database.
 */

session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// 1. Verifica autorizzazione.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo' || !isset($_SESSION['id_tavolo'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$id_tavolo = $_SESSION['id_tavolo'];
$id_alimento = isset($_POST['id_alimento']) ? intval($_POST['id_alimento']) : 0;

if ($id_alimento <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

// 2. Troviamo il carrello aperto per questo tavolo.
$sql_ordine = "SELECT id_ordine FROM ordini WHERE id_tavolo = $id_tavolo AND stato = 'in_attesa' LIMIT 1";
$res_ordine = $conn->query($sql_ordine);

if ($res_ordine->num_rows > 0) {
    $row = $res_ordine->fetch_assoc();
    $id_ordine = $row['id_ordine'];

    // 3. Controlliamo quante unità di quel piatto ci sono nel carrello.
    $check_qta = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento");

    if ($check_qta->num_rows > 0) {
        $curr = $check_qta->fetch_assoc();

        if ($curr['quantita'] > 1) {
            // Caso A: Ce n'è più di uno, quindi sottraiamo 1 dalla quantità.
            $sql_action = "UPDATE dettaglio_ordini SET quantita = quantita - 1 WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        }
        else {
            // Caso B: È rimasto l'ultimo pezzo, quindi cancelliamo la riga.
            $sql_action = "DELETE FROM dettaglio_ordini WHERE id_ordine = $id_ordine AND id_alimento = $id_alimento";
        }

        if ($conn->query($sql_action)) {
            echo json_encode(['success' => true, 'message' => 'Carrello aggiornato correttamente.']);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Errore tecnico: ' . $conn->error]);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Piatto non trovato nel carrello.']);
    }

}
else {
    echo json_encode(['success' => false, 'message' => 'Non hai un carrello attivo da cui rimuovere piatti.']);
}
?>