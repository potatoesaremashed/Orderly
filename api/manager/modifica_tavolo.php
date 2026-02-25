<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

// Leggi le informazioni del tavolo dal box di modifica e ripuliscile
$idTavolo = intval($_POST['id_tavolo'] ?? 0);
$nomeTavolo = trim($_POST['nome_tavolo'] ?? '');
$passwordTavolo = trim($_POST['password'] ?? '');
$numeroPosti = intval($_POST['posti'] ?? 4);
$statoTavolo = trim($_POST['stato'] ?? 'libero');

// Blocca il processo e invia un errore se i campi principali sono sballati
if ($idTavolo <= 0 || empty($nomeTavolo) || empty($passwordTavolo)) {
    echo json_encode(['success' => false, 'error' => 'Dati incompleti.']);
    exit;
}

// Imposta la modifica sicura sulla tabella passandogli tutti i dati freschi
$aggiornamento = $conn->prepare("UPDATE tavoli SET nome_tavolo=?, password=?, posti=?, stato=? WHERE id_tavolo=?");
$aggiornamento->bind_param("ssisi", $nomeTavolo, $passwordTavolo, $numeroPosti, $statoTavolo, $idTavolo);

// Salva e notifica l'esito della modifica all'app
if ($aggiornamento->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore aggiornamento: ' . $aggiornamento->error]);
}
?>