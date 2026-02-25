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

// 2. Recupero Dati (tramite prepared statement)
$nome = $_POST['nome_piatto'];
$desc = $_POST['descrizione'];
$prezzo = floatval($_POST['prezzo']);
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;

$stringa_allergeni = "";
if (isset($_POST['allergeni']) && is_array($_POST['allergeni'])) {
    $stringa_allergeni = implode(",", $_POST['allergeni']);
}

// 3. RECUPERO IMMAGINE
$imgData = null;

if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] == 0) {
    // Legge il file immagine caricato come stringa binaria
    $imgData = file_get_contents($_FILES["immagine"]["tmp_name"]);
}

$sql = "INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssdiss", $nome, $desc, $prezzo, $id_categoria, $imgData, $stringa_allergeni);
    
    if ($stmt->execute()) {
        header("Location: ../../dashboards/manager.php?msg=success#menu");
    } else {
        echo "Errore salvataggio: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Errore query: " . $conn->error;
}
?>