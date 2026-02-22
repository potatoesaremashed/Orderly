<?php
/**
 * =========================================
 * API: Elimina Categoria
 * =========================================
 * Rimuove una categoria dal menu.
 * 
 * In un database relazionale, non puoi cancellare una categoria se questa 
 * contiene ancora dei piatti (Foreign Key Constraint). 
 * Se provi a farlo, il database restituirà un errore per proteggere i dati.
 */

session_start();
include "../../include/conn.php";

// 1. Verifica Sicurezza
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// 2. Controllo Metodo (solo POST è permesso per cancellazioni)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non consentito: usa il pulsante Elimina nel pannello.");
}

// 3. Esecuzione
if (isset($_POST['id_categoria'])) {
    $id_categoria = intval($_POST['id_categoria']);

    // Prepariamo la query per cancellare solo quella specifica categoria.
    $sql = "DELETE FROM categorie WHERE id_categoria = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_categoria);

        /**
         * COSTRUTTO TRY-CATCH
         * Usiamo questo blocco per "catturare" eventuali errori del database
         * (es. vincoli di integrità) ed evitare che il sito mostri una pagina bianca brutta.
         */
        try {
            if ($stmt->execute()) {
                // Se tutto va bene, torniamo alla dashboard con un messaggio di successo.
                header("Location: ../../dashboards/manager.php?msg=cat_deleted");
                exit;
            }
            else {
                throw new Exception($stmt->error);
            }
        }
        catch (Exception $e) {
            /**
             * Se scatta questo blocco, vuol dire che il database ha rifiutato la cancellazione.
             * Probabilmente perché ci sono piatti collegati.
             */
            echo "<div style='font-family: sans-serif; padding: 40px; text-align: center; color: #721c24; background: #f8d7da;'>";
            echo "<h2>🚨 Azione Bloccata!</h2>";
            echo "<p>Impossibile eliminare questa categoria perché ci sono ancora piatti collegati.</p>";
            echo "<p>Per favore, elimina o sposta prima i piatti contenuti in questa categoria.</p>";
            echo "<br><a href='../../dashboards/manager.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Indietro</a>";
            echo "</div>";
        }
        $stmt->close();
    }
}
else {
    echo "Errore: ID categoria mancante.";
}

$conn->close();
?>
