<?php
/**
 * =========================================
 * API: Modifica Piatto (Versione BLOB)
 * =========================================
 * Permette al manager di aggiornare i dati di un piatto esistente.
 * Se viene caricata una nuova immagine, viene salvata direttamente
 * nel database come BLOB.
 */

session_start();
include "../../include/conn.php";

// Verifica permessi: solo il manager può modificare i piatti.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// Accettiamo solo richieste POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. RECUPERO DATI ---
    $id_alimento = intval($_POST['id_alimento']);
    $nome = $_POST['nome_piatto'];
    $prezzo = floatval($_POST['prezzo']);
    $desc = $_POST['descrizione'];
    $id_cat = intval($_POST['id_categoria']);

    // GESTIONE ALLERGENI
    $lista_allergeni = "";
    if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
        $lista_allergeni = implode(", ", $_POST['allergeni']);
    }

    // --- 2. GESTIONE IMMAGINE BLOB (OPZIONALE) ---
    $imgData = null;
    $aggiorna_immagine = false;
    
    // Se è stata caricata una nuova foto:
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        $imgData = file_get_contents($_FILES["immagine"]["tmp_name"]);
        $aggiorna_immagine = true;
    }

    // --- 3. AGGIORNAMENTO DATABASE CON PREPARED STATEMENTS ---
    if ($aggiorna_immagine) {
        $sql = "UPDATE alimenti SET 
                nome_piatto = ?, 
                prezzo = ?, 
                descrizione = ?, 
                id_categoria = ?,
                lista_allergeni = ?,
                immagine = ?
                WHERE id_alimento = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sdsissi", $nome, $prezzo, $desc, $id_cat, $lista_allergeni, $imgData, $id_alimento);
        }
    } else {
        $sql = "UPDATE alimenti SET 
                nome_piatto = ?, 
                prezzo = ?, 
                descrizione = ?, 
                id_categoria = ?,
                lista_allergeni = ? 
                WHERE id_alimento = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sdsisi", $nome, $prezzo, $desc, $id_cat, $lista_allergeni, $id_alimento);
        }
    }

    if ($stmt) {
        if ($stmt->execute()) {
            // SUCCESSO: Torna alla lista dei piatti.
            header("Location: ../../dashboards/manager.php?msg=success");
        } else {
            echo "Errore del database durante la modifica: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Errore preparazione query: " . $conn->error;
    }
}
?>