<?php
/**
 * =========================================
 * API: Elimina Tavolo
 * =========================================
 * Rimuove definitivamente un tavolo dal sistema.
 * 
 * Quando eliminiamo un tavolo, dobbiamo prima "pulire" la cronologia degli ordini
 * e dei dettagli ordini collegati a quel tavolo. Se non lo facessimo, il database
 * potrebbe bloccare l'azione per via dei vincoli di integrità.
 */

session_start();

// Sicurezza: solo il Manager ha il "potere" di eliminare tavoli.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato.']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

/**
 * --- 1. PULIZIA DATI COLLEGATI ---
 * Eliminiamo prima i dettagli dei piatti ordinati su questo tavolo (dettaglio_ordini)
 * e poi gli ordini stessi (ordini).
 */
$conn->query("DELETE do FROM dettaglio_ordini do 
              INNER JOIN ordini o ON do.id_ordine = o.id_ordine 
              WHERE o.id_tavolo = $id");

$conn->query("DELETE FROM ordini WHERE id_tavolo = $id");

/**
 * --- 2. ELIMINAZIONE TAVOLO ---
 * Ora che non ci sono più ordini "appesi", possiamo cancellare il tavolo in sicurezza.
 */
$stmt = $conn->prepare("DELETE FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Errore database durante l\'eliminazione.']);
}
?>