<?php
/**
 * API: Elimina Categoria
 * Permette al manager di rimuovere una categoria.
 */

session_start();
include "../include/conn.php";

// Configurazione e Sicurezza
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non consentito");
}

// Esecuzione
if (isset($_POST['id_categoria'])) {
    $id_categoria = intval($_POST['id_categoria']);

    // Query di eliminazione
    $sql = "DELETE FROM categorie WHERE id_categoria = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_categoria);
        
        try {
            if ($stmt->execute()) {
                // Successo
                header("Location: ../dashboards/manager.php?msg=cat_deleted");
                exit;
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            // Gestione errore (es. se ci sono piatti collegati)
            echo "<div style='font-family: sans-serif; padding: 20px;'>";
            echo "<h2>Impossibile eliminare la categoria!</h2>";
            echo "<p>Molto probabilmente questa categoria contiene ancora dei piatti.</p>";
            echo "<p>Devi prima eliminare o spostare i piatti collegati a questa categoria.</p>";
            echo "<a href='../dashboards/manager.php'>Torna indietro</a>";
            echo "</div>";
        }
        $stmt->close();
    }
} else {
    echo "ID mancante.";
}

$conn->close();
?>
