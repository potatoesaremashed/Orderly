<?php
// API: Termina la sessione di un singolo tavolo
// Resetta lo storico ordini (sessione_inizio), lo stato e il token dispositivo
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

// Ricevi l'ID del tavolo da resettare
$idTavolo = $_POST['id_tavolo'] ?? '';

// Resetta solo il tavolo specifico
$id = intval($idTavolo);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tavolo non valido.']);
    exit;
}
$stmt = $conn->prepare("UPDATE tavoli SET sessione_inizio = NOW(), stato = 'libero', device_token = NULL WHERE id_tavolo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['success' => true]);
?>