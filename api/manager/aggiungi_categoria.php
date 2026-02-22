<?php
/**
 * =========================================
 * API: Aggiungi Categoria
 * =========================================
 * Questo file gestisce la creazione di nuove categorie (es. Antipasti, Primi, Dessert).
 * Viene chiamato dal pannello Manager quando si vuole espandere il menu.
 * 
 * Per uno sviluppatore Junior:
 * Qui vediamo come gestire i dati inviati da un form HTML, come proteggerli 
 * e come rimandare l'utente alla pagina precedente dopo il salvataggio.
 */

// --- 1. CONFIGURAZIONE E SICUREZZA ---
session_start();
include "../../include/conn.php"; // Collega il database.

// Verifichiamo che chi chiama questa pagina sia effettivamente un Manager.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    // Se non ha i permessi, lo "buttiamo fuori" riportandolo al login.
    header("Location: ../../index.php");
    exit;
}

// Accettiamo solo richieste di tipo POST. Scrivere l'URL nel browser (GET) non funzionerà.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Accesso non consentito: devi usare il form.");
}

// --- 2. RACCOLTA E PULIZIA DATI ---
// trim() rimuove spazi vuoti inutili all'inizio e alla fine (es. " Pasta " -> "Pasta").
$nome_categoria = trim($_POST['nome_categoria']);
// intval() forza il valore a essere un numero intero, evitando scherzi o errori.
$id_menu = intval($_POST['id_menu']);


// Un "controllo di guardia": se il nome è vuoto, è inutile procedere.
if (empty($nome_categoria)) {
    die("Errore: La categoria deve avere un nome!");
}

// --- 3. SALVATAGGIO NEL DATABASE ---
/**
 * Usiamo i "Prepared Statements" (SQL preparate).
 * Invece di scrivere i dati direttamente nella query, usiamo dei punti di domanda (?).
 * Questo impedisce agli hacker di inserire codice maligno (SQL Injection).
 */
$sql = "INSERT INTO categorie (nome_categoria, id_menu) VALUES (?, ?)";

if ($stmt = $conn->prepare($sql)) {
    // "si" significa: il primo dato è una Stringa (s), il secondo è un Intero (i).
    $stmt->bind_param("si", $nome_categoria, $id_menu);

    if ($stmt->execute()) {
        /**
         * SUCCESSO: Se tutto va bene, usiamo header("Location: ...") per 
         * riportare l'utente alla dashboard aggiungendo un parametro 'msg'
         * che possiamo usare per mostrare un avviso verde di successo.
         */
        header("Location: ../../dashboards/manager.php?msg=cat_success");
        exit;
    }
    else {
        echo "Errore catturato dal database: " . $stmt->error;
    }
    $stmt->close(); // Chiude la "pratica" dell'invio.
}
else {
    echo "Errore tecnico nella preparazione: " . $conn->error;
}

$conn->close(); // Chiude la connessione al database.
?>
