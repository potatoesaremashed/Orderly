<?php
// Endpoint chiamato rigorosamente via JS (Fetch API) per generare e autenticare un nuovo device Tavolo

require_once "../../include/auth/manager_auth.php";
// Dichiara come formato in uscita il JSON
header('Content-Type: application/json');

// Acquisisce i campi
$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);

// Controlli base frontiera
if (empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Nome e Password sono obbligatori.']);
    exit;
}

// Interroga per verificare omonimia e scongiurare collisioni nel DB dei tavoli
$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ?");
$check->bind_param("s", $nome);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Esiste già un tavolo con questo nome.']);
    exit;
}

// Finalizza inserendo il nuovo punto raccolta comande
$stmt = $conn->prepare("INSERT INTO tavoli (nome_tavolo, password, posti, id_menu) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $nome, $password, $posti);

// Risposta ternaria asincrona interpretata dal javascript
echo json_encode($stmt->execute() ? ['success' => true, 'id' => $stmt->insert_id] : ['success' => false, 'error' => 'Errore durante il salvataggio.']);
?>