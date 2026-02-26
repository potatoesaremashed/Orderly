<?php
// Endpoint chiamato per dismettere un tavolo fisico o un tablet di ordinazione

require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

// Fase Critica: prima di cancellare il tavolo, distrugge in blocco tutto lo storico e gli scontrini
// ad esso associati per sventare errori fatali di "Foreing Key Constraint" imposti dal database relazionale MariaDB.
$conn->query("DELETE do FROM dettaglio_ordini do INNER JOIN ordini o ON do.id_ordine = o.id_ordine WHERE o.id_tavolo = $id");
$conn->query("DELETE FROM ordini WHERE id_tavolo = $id");

// Ora cancella infine il tavolo stesso senza generare eccezioni
$stmt = $conn->prepare("DELETE FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);

echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'error' => 'Errore eliminazione.']);
?>