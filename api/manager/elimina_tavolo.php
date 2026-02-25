<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$idTavolo = intval($_POST['id_tavolo'] ?? 0);

if ($idTavolo <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

// Rimuove i record dipendenti (dettagli e ordini) prima del tavolo
$conn->query("DELETE do FROM dettaglio_ordini do INNER JOIN ordini o ON do.id_ordine = o.id_ordine WHERE o.id_tavolo = $idTavolo");
$conn->query("DELETE FROM ordini WHERE id_tavolo = $idTavolo");

$eliminazione = $conn->prepare("DELETE FROM tavoli WHERE id_tavolo = ?");
$eliminazione->bind_param("i", $idTavolo);

if ($eliminazione->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore eliminazione.']);
}
?>