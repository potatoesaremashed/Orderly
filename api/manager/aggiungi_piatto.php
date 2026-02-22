<?php
/**
 * =========================================
 * API: Aggiungi Piatto
 * =========================================
 * Questo file è il "cuore" della creazione dei piatti nel menu.
 * Oltre a salvare i testi (nome, descrizione, prezzo), gestisce il caricamento 
 * e il ridimensionamento delle immagini caricate dal Manager.
 * 
 * Per uno sviluppatore Junior:
 * Gestire le immagini è complesso. Qui vediamo come creare una cartella se non esiste,
 * come dare un nome unico ai file e come comprimerli per non rallentare il sito.
 */

// --- 1. GESTIONE ERRORI E SICUREZZA ---
// Queste righe servono durante lo sviluppo per vedere subito se qualcosa si rompe.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../include/conn.php";

// Verifica che l'utente sia autorizzato (ruolo Manager).
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

// Accettiamo solo dati inviati via POST da un form.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Accesso negato.");
}

// --- 2. RACCOLTA E PULIZIA DATI ---
/**
 * real_escape_string è una funzione che pulisce il testo da caratteri che 
 * potrebbero "imbrogliare" il database (es. apici singoli ').
 */
$nome = $conn->real_escape_string($_POST['nome_piatto']);
$desc = $conn->real_escape_string($_POST['descrizione']);
$prezzo = floatval($_POST['prezzo']); // Converte il prezzo in un numero con decimali.
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;

/**
 * GESTIONE ALLERGENI
 * Il frontend invia gli allergeni come una lista di checkbox (array).
 * Per salvarli nel DB in modo semplice, li uniamo in una singola stringa separata da virgole.
 * Es: ["Uova", "Latte"] -> "Uova,Latte"
 */
$stringa_allergeni = "";
if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
    $stringa_allergeni = implode(",", $_POST['allergeni']);
    $stringa_allergeni = $conn->real_escape_string($stringa_allergeni);
}

// --- 3. GESTIONE IMMAGINE (UPLOADING & RESIZING) ---
$target_dir = "../../imgs/prodotti/"; // Cartella dove salveremo le foto.

// Se la cartella non esiste (magari è la prima installazione), creala automaticamente.
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

/**
 * Generiamo un nome file unico usando uniqid(). 
 * Questo evita che due foto con lo stesso nome (es. "pizza.jpg") si sovrascrivano.
 */
$nuovo_nome_img = "piatto_" . uniqid() . ".jpg";
$target_file = $target_dir . $nuovo_nome_img;

// Controlliamo se è stata effettivamente caricata una foto nel form.
if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] == 0) {
    $source_path = $_FILES["immagine"]["tmp_name"]; // Percorso temporaneo del file appena caricato.
    $imgInfo = getimagesize($source_path); // Legge le dimensioni e il tipo di immagine.

    if ($imgInfo === false) {
        die("Il file caricato non sembra essere un'immagine valida.");
    }

    $width = $imgInfo[0];
    $height = $imgInfo[1];
    $type = $imgInfo[2];

    // Creiamo una "copia digitale" dell'immagine in base al suo formato originale.
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
        /**
         * RIDIMENSIONAMENTO (RESIZING)
         * Se l'immagine è troppo grande (es. 4000px), caricarla intera sarebbe lentissimo.
         * La rimpiccioliamo a una larghezza massima di 800px mantenendo le proporzioni.
         */
        $max_width = 800;
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = ($height / $width) * $new_width;
        }
        else {
            $new_width = $width;
            $new_height = $height;
        }

        // Creiamo una tela vuota delle nuove dimensioni.
        $dst_image = imagecreatetruecolor($new_width, $new_height);

        // Gestione sfondo bianco (evita sfondo nero se l'originale ha trasparenze).
        $white = imagecolorallocate($dst_image, 255, 255, 255);
        imagefill($dst_image, 0, 0, $white);

        // Copiamo l'immagine originale sulla nuova tela "rimpicciolendola".
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Salviamo il risultato come JPEG di buona qualità (75%) sul server.
        if (!imagejpeg($dst_image, $target_file, 75)) {
            die("Errore: impossibile salvare l'immagine sul server. Controlla i permessi della cartella.");
        }

        // Pulizia della memoria (molto importante in PHP).
        imagedestroy($src_image);
        imagedestroy($dst_image);
    }
    else {
        $nuovo_nome_img = "default.jpg"; // Se il formato non è supportato, usa un'immagine di base.
    }
}
else {
    $nuovo_nome_img = "default.jpg"; // Se l'utente non ha caricato nulla.
}

// --- 4. INSERIMENTO FINALE NEL DATABASE ---
$sql = "INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) 
        VALUES ('$nome', '$desc', $prezzo, $id_categoria, '$nuovo_nome_img', '$stringa_allergeni')";

if ($conn->query($sql) === TRUE) {
    // Tutto OK: torna alla dashboard.
    header("Location: ../../dashboards/manager.php?msg=success");
}
else {
    echo "Errore del database: " . $conn->error;
}
?>