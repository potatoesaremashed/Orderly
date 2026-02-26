<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

// Remove dependent records first
$conn->query("DELETE do FROM dettaglio_ordini do INNER JOIN ordini o ON do.id_ordine = o.id_ordine WHERE o.id_tavolo = $id");
$conn->query("DELETE FROM ordini WHERE id_tavolo = $id");

$stmt = $conn->prepare("DELETE FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);

echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'error' => 'Errore eliminazione.']);
?>