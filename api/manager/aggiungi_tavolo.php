<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);

if (empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Nome e Password sono obbligatori.']);
    exit;
}

$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ?");
$check->bind_param("s", $nome);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Esiste già un tavolo con questo nome.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tavoli (nome_tavolo, password, posti, id_menu) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $nome, $password, $posti);

echo json_encode($stmt->execute() ? ['success' => true, 'id' => $stmt->insert_id] : ['success' => false, 'error' => 'Errore durante il salvataggio.']);
?>