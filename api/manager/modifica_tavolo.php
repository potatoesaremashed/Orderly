<?php
/**
 * API: modifica_tavolo.php
 * Modifica i dati di un tavolo esistente.
 * Parametri POST: id_tavolo, nome_tavolo, password, posti
 */
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);
$stato = trim($_POST['stato'] ?? 'libero');

// Validazione stato
$stati_validi = ['libero', 'occupato', 'riservato'];
if (!in_array($stato, $stati_validi)) {
    $stato = 'libero';
}

if ($id <= 0 || empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Tutti i campi sono obbligatori']);
    exit;
}

// Verifica che il nome non sia già usato da un altro tavolo
$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ? AND id_tavolo != ?");
$check->bind_param("si", $nome, $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Un altro tavolo con questo nome esiste già']);
    exit;
}

$stmt = $conn->prepare("UPDATE tavoli SET nome_tavolo = ?, password = ?, posti = ?, stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("ssisi", $nome, $password, $posti, $stato, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Errore durante la modifica']);
}
?>