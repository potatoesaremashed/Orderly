<?php
/**
 * API: elimina_tavolo.php
 * Elimina un tavolo dal database tramite il suo ID.
 * Parametro POST: id_tavolo
 */
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido']);
    exit;
}

// Elimina prima gli ordini associati al tavolo (dettagli + ordini)
$conn->query("DELETE do FROM dettaglio_ordini do 
              INNER JOIN ordini o ON do.id_ordine = o.id_ordine 
              WHERE o.id_tavolo = $id");
$conn->query("DELETE FROM ordini WHERE id_tavolo = $id");

// Elimina il tavolo
$stmt = $conn->prepare("DELETE FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'eliminazione']);
}
?>