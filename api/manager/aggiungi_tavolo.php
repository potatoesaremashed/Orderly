<?php
/**
 * =========================================
 * API: Aggiungi Tavolo
 * =========================================
 * Questo file permette di registrare un nuovo punto di servizio (tavolo) nel ristorante.
 * Ogni tavolo avrà un nome (es. "Tavolo 1") e una password per permettere ai clienti 
 * di accedere al menu digitale in modo protetto.
 * 
 * Per uno sviluppatore Junior:
 * Qui vediamo come gestire i dati inviati da un form HTML, come proteggerli 
 * e come rimandare l'utente alla pagina precedente dopo il salvataggio.
 */

session_start();

/**
 * SICUREZZA
 * Solo il Manager può creare nuovi tavoli. Controlliamo la sessione.
 */
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403); // Errore: Proibito (Forbidden).
    echo json_encode(['error' => 'Non hai i permessi per creare tavoli.']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json'); // La risposta sarà un oggetto leggibile da JavaScript.

// --- 1. RECUPERO E PULIZIA DATI ---
$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);

if (empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Attenzione: Nome e Password sono obbligatori.']);
    exit;
}

/**
 * --- 2. VERIFICA UNICITÀ ---
 * Non possiamo avere due tavoli con lo stesso nome, altrimenti l'app andrebbe in confusione.
 */
$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ?");
$check->bind_param("s", $nome);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Errore: Esiste già un tavolo con questo nome.']);
    exit;
}

/**
 * --- 3. INSERIMENTO ---
 * Salviamo il tavolo nel database. Associamo 'id_menu = 1' come default (Menu Principale).
 */
$stmt = $conn->prepare("INSERT INTO tavoli (nome_tavolo, password, posti, id_menu) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $nome, $password, $posti);

if ($stmt->execute()) {
    // Restituiamo success=true e l'ID appena creato (molto utile per il frontend).
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Errore tecnico durante l\'inserimento.']);
}
?>