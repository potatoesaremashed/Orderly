<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$idTavolo = intval($_POST['id_tavolo'] ?? 0);
$nuovoStato = trim($_POST['stato'] ?? '');
$statiValidi = ['libero', 'occupato', 'riservato'];

if ($idTavolo <= 0 || !in_array($nuovoStato, $statiValidi)) {
    echo json_encode(['success' => false, 'error' => 'Dati non validi']);
    exit;
}

$aggiornamento = $conn->prepare("UPDATE tavoli SET stato = ? WHERE id_tavolo = ?");
$aggiornamento->bind_param("si", $nuovoStato, $idTavolo);

if ($aggiornamento->execute()) {
    echo json_encode(['success' => true, 'nuovo_stato' => $nuovoStato]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore aggiornamento database']);
}
?>