<?php
/**
 * API: aggiungi_tavolo.php
 * Aggiunge un nuovo tavolo al database.
 * Parametri POST: nome_tavolo, password, posti
 */
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

include "../include/conn.php";
header('Content-Type: application/json');

$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);

if (empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Nome e password sono obbligatori']);
    exit;
}

// Verifica che il nome non esista già
$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ?");
$check->bind_param("s", $nome);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Un tavolo con questo nome esiste già']);
    exit;
}

// Inserimento nuovo tavolo (associato al menu 1 di default)
$stmt = $conn->prepare("INSERT INTO tavoli (nome_tavolo, password, posti, id_menu) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $nome, $password, $posti);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'inserimento']);
}
?>