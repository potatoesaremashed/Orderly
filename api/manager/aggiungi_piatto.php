<?php
/**
 * =========================================
 * API: Aggiungi Piatto
 * =========================================
 * Questo file viene eseguito quando il Manager compila il form "Nuovo Piatto".
 * Riceve i dati testuali (nome, descrizione, prezzo, allergeni) e carica l'immagine 
 * del piatto sul server. Infine, salva tutto nel database per renderlo disponibile ai tavoli.
 */
// ATTIVAZIONE REPORTING ERRORI
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../include/conn.php";

// Verifica permessi Manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Accesso non consentito");
}

// RACCOLTA DATI
$nome = $conn->real_escape_string($_POST['nome_piatto']);
$desc = $conn->real_escape_string($_POST['descrizione']);
$prezzo = floatval($_POST['prezzo']);
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;

// GESTIONE ALLERGENI 
// Trasforma l'array delle checkbox in una stringa separata da virgole
$stringa_allergeni = "";
if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
    $stringa_allergeni = implode(",", $_POST['allergeni']);
    $stringa_allergeni = $conn->real_escape_string($stringa_allergeni);
}

// GESTIONE IMMAGINE 
$target_dir = "../imgs/prodotti/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$nuovo_nome_img = "piatto_" . uniqid() . ".jpg";
$target_file = $target_dir . $nuovo_nome_img;
$uploadOk = 1;

if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] == 0) {

    $source_path = $_FILES["immagine"]["tmp_name"];
    $imgInfo = getimagesize($source_path);

    if ($imgInfo === false) {
        die("Il file caricato non è un'immagine.");
    }

    $width = $imgInfo[0];
    $height = $imgInfo[1];
    $type = $imgInfo[2];

    $src_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $src_image = imagecreatefromgif($source_path);
            break;
    }

    if ($src_image) {
        $max_width = 800;
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = ($height / $width) * $new_width;
        } else {
            $new_width = $width;
            $new_height = $height;
        }

        $dst_image = imagecreatetruecolor($new_width, $new_height);
        $white = imagecolorallocate($dst_image, 255, 255, 255);
        imagefill($dst_image, 0, 0, $white);
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Salva
        if (!imagejpeg($dst_image, $target_file, 75)) {
            echo "Errore permessi cartella immagini.";
            exit;
        }

        imagedestroy($src_image);
        imagedestroy($dst_image);
    } else {
        $nuovo_nome_img = "default.jpg";
    }
} else {
    $nuovo_nome_img = "default.jpg";
}
$sql = "INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) 
        VALUES ('$nome', '$desc', $prezzo, $id_categoria, '$nuovo_nome_img', '$stringa_allergeni')";

if ($conn->query($sql) === TRUE) {
    header("Location: ../dashboards/manager.php?msg=success");
} else {
    echo "Errore SQL: " . $conn->error;
}
?>