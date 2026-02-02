<?php
/**
 * API: Aggiungi Piatto (Manager) + Ottimizzazione Immagini
 * --------------------------------------------------------
 * Script per l'inserimento di nuovi prodotti nel menu.
 * INCLUDE LOGICA DI RESIZE:
 * Prima di salvare l'immagine, lo script la ridimensiona a max 800px di larghezza
 * e la converte in JPG compresso per evitare rallentamenti nell'app mobile.
 */
session_start();
include "../include/conn.php";
// ... resto del codice ...
session_start();
include "../include/conn.php";

// Verifica permessi Manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

$nome = $conn->real_escape_string($_POST['nome_piatto']);
$desc = $conn->real_escape_string($_POST['descrizione']);
$prezzo = floatval($_POST['prezzo']);
$categoria = $conn->real_escape_string($_POST['categoria']);

// Gestione Immagine Ottimizzata
$target_dir = "../imgs/prodotti/";
$nuovo_nome_img = "piatto_" . uniqid() . ".jpg"; // Convertiamo tutto in JPG
$target_file = $target_dir . $nuovo_nome_img;
$uploadOk = 1;

if(isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] == 0) {
    
    $source_path = $_FILES["immagine"]["tmp_name"];
    list($width, $height, $type) = getimagesize($source_path);
    
    // Crea risorsa immagine in base al tipo originale
    $src_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $src_image = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG:  $src_image = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF:  $src_image = imagecreatefromgif($source_path); break;
    }

    if ($src_image) {
        // Calcola nuove dimensioni (Max larghezza 800px)
        $max_width = 800;
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = ($height / $width) * $new_width;
        } else {
            $new_width = $width;
            $new_height = $height;
        }

        // Crea nuova immagine vuota
        $dst_image = imagecreatetruecolor($new_width, $new_height);
        
        // Mantieni trasparenza per PNG (convertendo in bianco se passa a JPG) o sfondo bianco
        $white = imagecolorallocate($dst_image, 255, 255, 255);
        imagefill($dst_image, 0, 0, $white);

        // Copia e ridimensiona
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Salva come JPG compresso (Qualità 75/100) -> Questo riduce drasticamente il peso!
        if(imagejpeg($dst_image, $target_file, 75)) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
        }

        // Libera memoria
        imagedestroy($src_image);
        imagedestroy($dst_image);
    } else {
        $uploadOk = 0; // Formato non supportato
    }
} else {
    $nuovo_nome_img = "default.png"; // Immagine se non caricata
}

if ($uploadOk) {
    $sql = "INSERT INTO alimenti (nome_piatto, descrizione, prezzo, categoria, immagine, disponibile) 
            VALUES ('$nome', '$desc', $prezzo, '$categoria', '$nuovo_nome_img', 1)";
    $conn->query($sql);
}

header("Location: ../dashboards/manager.php");
?>