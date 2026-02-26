<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

$stmt = $conn->prepare("UPDATE tavoli SET sessione_inizio = NOW(), stato = 'libero', device_token = NULL WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['success' => true]);
?>