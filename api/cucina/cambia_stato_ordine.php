<?php
session_start();
include "../../include/conn.php";
header('Content-Type: application/json');

// Definisci chi ha il diritto di fare queste modifiche (solo cuochi, manager e admin)
$ruoli_ammessi = ['cuoco', 'manager', 'admin'];

// Ferma tutto se chi sta cliccando non ha fatto il login oppure non è nel gruppo autorizzato
if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) {
    echo json_encode(['success' => false, 'message' => 'Non hai i permessi per questa operazione.']);
    exit;
}

// Intercetta e decodifica i dati JSON che arrivano da Javascript
$input = json_decode(file_get_contents('php://input'), true);
$id_ordine = $input['id_ordine'] ?? null;
$nuovo_stato = $input['nuovo_stato'] ?? null;

// Stabilisci il recinto di sicurezza con gli stati validi previsti
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto'];

// Nega il salvataggio al database se mancano i dati chiave o se lo stato è roba inventata
if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) {
    echo json_encode(['success' => false, 'message' => 'Dati ordine mancanti o non validi.']);
    exit;
}

// Invia l'istruzione di aggiornamento tabella con i segnaposti sql
$sql = "UPDATE ordini SET stato = ? WHERE id_ordine = ?";
$stmt = $conn->prepare($sql);

// Associa la stringa (stato) e il numero (id ordine) per sanificare l'inserimento
$stmt->bind_param("si", $nuovo_stato, $id_ordine);

// Restituisci via json l'esito della chiamata a fine processo
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio dei dati: ' . $conn->error]);
}
?>