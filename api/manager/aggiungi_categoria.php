<?php
/**
 * API: Aggiungi Categoria
 * Gestisce l'inserimento di nuove categorie nel menu
 */

// Configurazione e Sicurezza
session_start();
include "../../include/conn.php";

// Verifica che l'utente sia loggato come Manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// Verifica che la chiamata arrivi da un form (POST) e non scrivendo l'indirizzo nel browser
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Accesso non consentito");
}

// Raccolta Dati dal Form
// Prendiamo il nome e puliamo eventuali caratteri strani (sicurezza base)
$nome_categoria = trim($_POST['nome_categoria']);
$id_menu = intval($_POST['id_menu']); // Assicuriamoci che sia un numero intero

// Controllo validità: non accettiamo categorie senza nome
if (empty($nome_categoria)) {
    die("Errore: Il nome della categoria non può essere vuoto.");
}

// Inserimento nel Database
// Usiamo i Prepared Statements (?) per evitare SQL Injection
$sql = "INSERT INTO categorie (nome_categoria, id_menu) VALUES (?, ?)";

if ($stmt = $conn->prepare($sql)) {
    // Colleghiamo i parametri: "si" sta per Stringa (nome), Intero (id_menu)
    $stmt->bind_param("si", $nome_categoria, $id_menu);

    if ($stmt->execute()) {
        // SUCCESSO: Torniamo alla dashboard con un messaggio positivo
        header("Location: ../../dashboards/manager.php?msg=cat_success");
        exit;
    } else {
        // ERRORE SQL
        echo "Errore nell'esecuzione della query: " . $stmt->error;
    }
    $stmt->close();
} else {
    // ERRORE PREPARAZIONE
    echo "Errore nella preparazione della query: " . $conn->error;
}

$conn->close();
?>
