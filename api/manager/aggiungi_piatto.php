<?php
session_start();
include "../../include/conn.php";

// 1. Controllo permessi
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Accesso non consentito");
}

// 2. Recupero Dati (usando real_escape_string)
$nome = $conn->real_escape_string($_POST['nome_piatto']);
$desc = $conn->real_escape_string($_POST['descrizione']);
$prezzo = floatval($_POST['prezzo']);
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;

$stringa_allergeni = "";
if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
    $stringa_allergeni = $conn->real_escape_string(implode(",", $_POST['allergeni']));
}

// 3. RECUPERO IMMAGINE
$imgData = "";

if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] == 0) {
    // Legge il file immagine caricato
    $dati_grezzi = file_get_contents($_FILES["immagine"]["tmp_name"]);
    
    // Lo "protegge" in modo che non rompa la sintassi SQL
    $imgData = $conn->real_escape_string($dati_grezzi);
}

$sql = "INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) 
        VALUES ('$nome', '$desc', $prezzo, $id_categoria, '$imgData', '$stringa_allergeni')";

if ($conn->query($sql) === TRUE) {
    header("Location: ../../dashboards/manager.php?msg=success");
} else {
    echo "Errore salvataggio: " . $conn->error;
}
?>