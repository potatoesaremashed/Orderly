<?php
// Tasto magico "Resetta" di colore azzurro situato nelle Card Occupate lato Gestore

require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}

// Cuore della logica di log-out centralizzata:
// Reimposta a verde "Libero" il tavolo ed estingue (NULL) il device_token di tracciatura
$stmt = $conn->prepare("UPDATE tavoli SET sessione_inizio = NOW(), stato = 'libero', device_token = NULL WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['success' => true]);
?>