<?php
/**
 * =========================================
 * API: Elimina Piatto
 * =========================================
 * Permette al manager di rimuovere un piatto dal menu.
 * 
 * Quando eliminiamo un record in un database moderno, spesso usiamo la clausola
 * "ON DELETE CASCADE". Questo significa che se eliminiamo il piatto, il database 
 * pulirà automaticamente anche i riferimenti a quel piatto negli ordini passati.
 */

// --- 1. CONFIGURAZIONE E SICUREZZA ---
session_start();
include "../../include/conn.php";

// Verifica che l'utente sia un Manager (fondamentale per la sicurezza).
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// Accettiamo solo richieste POST (invio form).
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Azione non consentita via URL.");
}

// --- 2. RECUPERO DATI E ESECUZIONE ---
if (isset($_POST['id_alimento'])) {
    $id_alimento = intval($_POST['id_alimento']);

    // Query di eliminazione.
    $sql = "DELETE FROM alimenti WHERE id_alimento = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_alimento);

        if ($stmt->execute()) {
            // SUCCESSO: Torniamo alla dashboard con un messaggio di conferma.
            header("Location: ../../dashboards/manager.php?msg=deleted");
            exit;
        }
        else {
            echo "Errore del database durante l'eliminazione: " . $stmt->error;
        }
        $stmt->close();
    }
    else {
        echo "Errore tecnico nella query: " . $conn->error;
    }
}
else {
    echo "Errore: ID piatto mancante.";
}

$conn->close();
?>
