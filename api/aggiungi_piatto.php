<?php
session_start();
include "../include/conn.php";

// 1. SICUREZZA: Verifica che l'utente sia loggato come Manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- RICEZIONE E PULIZIA DATI ---
    $nome = $conn->real_escape_string($_POST['nome_piatto']);
    $prezzo = floatval($_POST['prezzo']);
    $desc = $conn->real_escape_string($_POST['descrizione']);
    $cat = intval($_POST['id_categoria']);

    // --- NUOVA GESTIONE ALLERGENI (CHECKBOX) ---
    // Verifichiamo se sono stati selezionati allergeni
    if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
        // Uniamo l'array in una stringa separata da virgole (es: "Glutine,Uova,Latte")
        $stringa_allergeni = implode(',', $_POST['allergeni']);
        $allergeni = $conn->real_escape_string($stringa_allergeni);
    } else {
        $allergeni = ""; // Nessun allergene selezionato
    }

    // --- GESTIONE UPLOAD IMMAGINE ---
    $target_dir = "../imgs/prodotti/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["immagine"]["name"], PATHINFO_EXTENSION));
    $new_file_name = "piatto_" . uniqid() . "." . $imageFileType;
    $target_file = $target_dir . $new_file_name;
    
    $uploadOk = 1;
    $erroreMsg = "";

    // Controlli immagine
    $check = getimagesize($_FILES["immagine"]["tmp_name"]);
    if($check === false) {
        $erroreMsg = "Il file non è un'immagine.";
        $uploadOk = 0;
    }

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "webp" ) {
        $erroreMsg = "Solo file JPG, JPEG, PNG e WEBP sono ammessi.";
        $uploadOk = 0;
    }

    // --- ESECUZIONE ---
    if ($uploadOk == 0) {
        header("Location: ../dashboards/manager.php?msg=error&info=" . urlencode($erroreMsg));
        exit;
    } else {
        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
            
            // Query di inserimento
            $sql = "INSERT INTO alimenti (nome_piatto, prezzo, descrizione, lista_allergeni, immagine, id_categoria) 
                    VALUES ('$nome', $prezzo, '$desc', '$allergeni', '$new_file_name', $cat)";

            if ($conn->query($sql) === TRUE) {
                header("Location: ../dashboards/manager.php?msg=success");
                exit;
            } else {
                echo "Errore Database: " . $conn->error;
            }

        } else {
            echo "Errore tecnico nel caricamento del file.";
        }
    }

} else {
    header("Location: ../dashboards/manager.php");
    exit;
}
?>