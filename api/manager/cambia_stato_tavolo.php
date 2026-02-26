<?php
// Endpoint asincrono JS per variare forzatamente (dal manager) lo status colorato in griglia di un determinato tavolo.

require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

// Mappa l'id del tavolo da mutare
$id = intval($_POST['id_tavolo'] ?? 0);
$stato = trim($_POST['stato'] ?? '');

// Lista bianca degli strati supportati, blocco di anomalie e data corruption
$statiValidi = ['libero', 'occupato', 'riservato'];

if ($id <= 0 || !in_array($stato, $statiValidi)) {
    echo json_encode(['success' => false, 'error' => 'Dati non validi']);
    exit;
}

// Esegue istruzione di UPDATE della riga in anagrafica 
$stmt = $conn->prepare("UPDATE tavoli SET stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("si", $stato, $id);

echo json_encode($stmt->execute() ? ['success' => true, 'nuovo_stato' => $stato] : ['success' => false, 'error' => 'Errore aggiornamento']);
?>