<?php
/**
 * =========================================
 * API: Modifica Piatto
 * =========================================
 * Permette al manager di aggiornare i dati di un piatto esistente.
 * Aggiorna: nome, prezzo, descrizione, categoria, allergeni e (opzionalmente) l'immagine.
 * 
 * Qui usiamo una tecnica di "query dinamica". Se l'utente non carica una nuova foto,
 * non aggiorniamo il campo immagine lasciando quella vecchia.
 */

session_start();
include "../../include/conn.php";

// Verifica permessi: solo il manager può modificare i piatti.
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// Accettiamo solo richieste POST (invio form).
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. RECUPERO E PULIZIA DATI ---
    $id_alimento = intval($_POST['id_alimento']);
    $nome = $conn->real_escape_string($_POST['nome_piatto']);
    $prezzo = floatval($_POST['prezzo']);
    $desc = $conn->real_escape_string($_POST['descrizione']);
    $id_cat = intval($_POST['id_categoria']);

    /**
     * GESTIONE ALLERGENI
     * Trasforma l'array delle checkbox selezionate in una stringa pulita.
     */
    $lista_allergeni = "";
    if (isset($_POST['allergeni'])) {
        $lista_allergeni = implode(", ", $_POST['allergeni']);
    }
    $lista_allergeni = $conn->real_escape_string($lista_allergeni);

    /**
     * --- 2. GESTIONE IMMAGINE (OPZIONALE) ---
     * Se l'input 'immagine' non è vuoto, salviamo il nuovo file.
     */
    $query_img = "";
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        $target_dir = "../../imgs/prodotti/";
        // Usiamo time() per generare un nome unico basato sul secondo attuale.
        $filename = time() . "_" . basename($_FILES["immagine"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
            // Se il caricamento riesce, prepariamo il pezzetto di query per il DB.
            $query_img = ", immagine = '$filename'";
        }
    }

    // --- 3. AGGIORNAMENTO DATABASE ---
    $sql = "UPDATE alimenti SET 
            nome_piatto = '$nome', 
            prezzo = $prezzo, 
            descrizione = '$desc', 
            id_categoria = $id_cat,
            lista_allergeni = '$lista_allergeni' 
            $query_img 
            WHERE id_alimento = $id_alimento";

    if ($conn->query($sql) === TRUE) {
        // SUCCESSO: Torna alla lista dei piatti.
        header("Location: ../../dashboards/manager.php?msg=success");
    }
    else {
        echo "Errore del database durante la modifica: " . $conn->error;
    }
}
?>