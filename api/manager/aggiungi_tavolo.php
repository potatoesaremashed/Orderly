<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

$nomeTavolo = trim($_POST['nome_tavolo'] ?? '');
$passwordTavolo = trim($_POST['password'] ?? '');
$numeroPosti = intval($_POST['posti'] ?? 4);

if (empty($nomeTavolo) || empty($passwordTavolo)) {
    echo json_encode(['success' => false, 'error' => 'Nome e Password sono obbligatori.']);
    exit;
}

// Verifica che il nome non sia già in uso da un altro tavolo
$checkEsistenza = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ?");
$checkEsistenza->bind_param("s", $nomeTavolo);
$checkEsistenza->execute();

if ($checkEsistenza->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Esiste già un tavolo con questo nome.']);
    exit;
}

// Inserisce il nuovo tavolo assegnandolo al Menu Principale (id_menu = 1)
$inserimento = $conn->prepare("INSERT INTO tavoli (nome_tavolo, password, posti, id_menu) VALUES (?, ?, ?, 1)");
$inserimento->bind_param("ssi", $nomeTavolo, $passwordTavolo, $numeroPosti);

if ($inserimento->execute()) {
    echo json_encode(['success' => true, 'id' => $inserimento->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio.']);
}
?>