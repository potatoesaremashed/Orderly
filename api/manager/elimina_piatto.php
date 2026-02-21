<?php
/**
 * API: Elimina Piatto
 * Permette al manager di rimuovere un piatto dal menu.
 */

// Configurazione e Sicurezza
session_start();
include "../include/conn.php";

// Verifica che l'utente sia un Manager (fondamentale per non far cancellare piatti a chiunque)
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

// Accettiamo solo richieste POST (dal form che ti ho dato prima)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non consentito");
}

// Recupero Dati
// Prendiamo l'ID del piatto che vogliamo eliminare
if (isset($_POST['id_alimento'])) {
    $id_alimento = intval($_POST['id_alimento']);
    
    // Eliminazione dal Database
    // Grazie a "ON DELETE CASCADE" nel database, questo cancellerà 
    // automaticamente anche il piatto dagli ordini storici.
    $sql = "DELETE FROM alimenti WHERE id_alimento = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_alimento);
        
        if ($stmt->execute()) {
            // Successo: Torniamo alla dashboard
            header("Location: ../dashboards/manager.php?msg=deleted");
            exit;
        } else {
            echo "Errore durante l'eliminazione: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Errore nella query: " . $conn->error;
    }
} else {
    echo "ID alimento mancante.";
}

$conn->close();
?>
