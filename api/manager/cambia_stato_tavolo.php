<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
$stato = trim($_POST['stato'] ?? '');
$statiValidi = ['libero', 'occupato', 'riservato'];

if ($id <= 0 || !in_array($stato, $statiValidi)) {
    echo json_encode(['success' => false, 'error' => 'Dati non validi']);
    exit;
}

$stmt = $conn->prepare("UPDATE tavoli SET stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("si", $stato, $id);

echo json_encode($stmt->execute() ? ['success' => true, 'nuovo_stato' => $stato] : ['success' => false, 'error' => 'Errore aggiornamento']);
?>