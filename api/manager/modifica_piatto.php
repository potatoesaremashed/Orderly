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

    // --- 1. RECUPERO E PULIZIA DATI ---
    $id_alimento = intval($_POST['id_alimento']);
    $nome = $conn->real_escape_string($_POST['nome_piatto']);
    $prezzo = floatval($_POST['prezzo']);
    $desc = $conn->real_escape_string($_POST['descrizione']);
    $id_cat = intval($_POST['id_categoria']);

    // GESTIONE ALLERGENI
    $lista_allergeni = "";
    if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
        $lista_allergeni = implode(", ", $_POST['allergeni']);
    }
    $lista_allergeni = $conn->real_escape_string($lista_allergeni);

    // --- 2. GESTIONE IMMAGINE BLOB (OPZIONALE) ---
    // Di base la stringa aggiuntiva per la query è vuota
    $query_img = "";
    
    // Se è stata caricata una nuova foto...
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        
        // Legge l'intero file immagine e lo trasforma in dati grezzi
        $dati_grezzi = file_get_contents($_FILES["immagine"]["tmp_name"]);
        
        // Lo "protegge" per non rompere la sintassi SQL
        $imgData = $conn->real_escape_string($dati_grezzi);
        
        // Crea il pezzo di query per aggiornare l'immagine
        $query_img = ", immagine = '$imgData'";
    }

    // --- 3. AGGIORNAMENTO DATABASE ---
    // Se $query_img è vuota, l'immagine non viene toccata.
    // Se contiene i dati, aggiorna anche la colonna immagine.
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
    } else {
        echo "Errore del database durante la modifica: " . $conn->error;
    }
}
?>