<?php
/**
 * =========================================
 * API: Modifica Tavolo
 * =========================================
 * Permette di cambiare nome, password, posti o stato di un tavolo già registrato.
 * 
 * Per uno sviluppatore Junior:
 * Particolare attenzione qui va alla "Verifica Unicità". Se l'utente cambia 
 * il nome del Tavolo 1 in Tavolo 2, dobbiamo assicurarci che Tavolo 2 non esista già.
 */

session_start();

// Controllo sicurezza.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Operazione riservata ai manager.']);
    exit;
}

include "../../include/conn.php";
header('Content-Type: application/json');

// --- 1. RECUPERO DATI ---
$id = intval($_POST['id_tavolo'] ?? 0);
$nome = trim($_POST['nome_tavolo'] ?? '');
$password = trim($_POST['password'] ?? '');
$posti = intval($_POST['posti'] ?? 4);
$stato = trim($_POST['stato'] ?? 'libero');

// Validazione stato (usiamo 'libero' se lo stato inviato non è corretto).
$stati_validi = ['libero', 'occupato', 'riservato'];
if (!in_array($stato, $stati_validi)) {
    $stato = 'libero';
}

if ($id <= 0 || empty($nome) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Attenzione: Tutti i campi sono obbligatori!']);
    exit;
}

/**
 * --- 2. VERIFICA UNICITÀ (CON ESCLUSIONE) ---
 * Spiegazione per Junior:
 * 'id_tavolo != ?' serve a dire al database: "Cerca questo nome tra tutti gli ALTRI
 * tavoli, ma ignora quello che sto modificando adesso".
 */
$check = $conn->prepare("SELECT id_tavolo FROM tavoli WHERE nome_tavolo = ? AND id_tavolo != ?");
$check->bind_param("si", $nome, $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Errore: Un altro tavolo si chiama già così.']);
    exit;
}

// --- 3. AGGIORNAMENTO ---
$stmt = $conn->prepare("UPDATE tavoli SET nome_tavolo = ?, password = ?, posti = ?, stato = ? WHERE id_tavolo = ?");
$stmt->bind_param("ssisi", $nome, $password, $posti, $stato, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio delle modifiche.']);
}
?>