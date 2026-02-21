<?php
/**
 * =========================================
 * API: Modifica Piatto
 * =========================================
 * Permette al manager di aggiornare i dati di un piatto esistente.
 * Aggiorna: nome, prezzo, descrizione, categoria, allergeni e (opzionalmente) l'immagine.
 * Se viene caricata una nuova immagine, sovrascrive quella precedente.
 */

// Avvia la sessione per accedere ai dati dell'utente loggato
session_start();
include "../include/conn.php";

// Verifica permessi: solo il manager può modificare i piatti
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

// Accettiamo solo richieste POST (invio form)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Raccolta dati dal form di modifica
    $id_alimento = intval($_POST['id_alimento']);         // ID del piatto da modificare
    $nome = $conn->real_escape_string($_POST['nome_piatto']); // Nome del piatto (sanitizzato)
    $prezzo = floatval($_POST['prezzo']);                  // Prezzo (convertito a numero decimale)
    $desc = $conn->real_escape_string($_POST['descrizione']); // Descrizione (sanitizzata)
    $id_cat = intval($_POST['id_categoria']);              // ID della categoria di appartenenza

    // Gestione Allergeni:
    // Trasforma l'array delle checkbox selezionate in una stringa separata da virgole
    // Es: ["Glutine", "Uova"] → "Glutine, Uova"
    $lista_allergeni = "";
    if (isset($_POST['allergeni'])) {
        $lista_allergeni = implode(", ", $_POST['allergeni']);
    }
    $lista_allergeni = $conn->real_escape_string($lista_allergeni);

    // Gestione Immagine (opzionale):
    // Se l'utente carica una nuova immagine, la salviamo e aggiorniamo il percorso nel DB
    $query_img = "";
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        $target_dir = "../imgs/prodotti/";
        // Genera un nome file unico usando il timestamp per evitare conflitti
        $filename = time() . "_" . basename($_FILES["immagine"]["name"]);
        $target_file = $target_dir . $filename;

        // Sposta il file dalla cartella temporanea alla cartella delle immagini
        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
            $query_img = ", immagine = '$filename'"; // Aggiunge il campo immagine alla query
        }
    }

    // Query di aggiornamento del piatto nel database
    $sql = "UPDATE alimenti SET 
            nome_piatto = '$nome', 
            prezzo = $prezzo, 
            descrizione = '$desc', 
            id_categoria = $id_cat,
            lista_allergeni = '$lista_allergeni' 
            $query_img 
            WHERE id_alimento = $id_alimento";

    // Esecuzione query e redirect alla dashboard
    if ($conn->query($sql) === TRUE) {
        header("Location: ../dashboards/manager.php?msg=success");
    } else {
        echo "Errore modifica: " . $conn->error;
    }
}
?>