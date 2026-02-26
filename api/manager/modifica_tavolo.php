<?php
// Script chiamato asincronamente dalla modale Modifica (Tavoli) 

require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$id = intval($_POST['id_tavolo'] ?? 0);
$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);
$stato = trim($_POST['stato'] ?? 'libero');

if ($id <= 0 || empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Dati incompleti.']);
    exit;
}

// Riscrive tutto ciò che compone staticamente l'anagrafica del posto a sedere
$stmt = $conn->prepare("UPDATE tavoli SET nome_tavolo=?, password=?, posti=?, stato=? WHERE id_tavolo=?");
$stmt->bind_param("ssisi", $nome, $password, $posti, $stato, $id);

echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'error' => 'Errore: ' . $stmt->error]);
?>