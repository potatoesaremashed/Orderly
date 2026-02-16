<?php
session_start();
include "../include/conn.php";

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_alimento = intval($_POST['id_alimento']);
    $nome = $conn->real_escape_string($_POST['nome_piatto']);
    $prezzo = floatval($_POST['prezzo']);
    $desc = $conn->real_escape_string($_POST['descrizione']);
    $id_cat = intval($_POST['id_categoria']);

    $lista_allergeni = "";
    if (isset($_POST['allergeni'])) {
        $lista_allergeni = implode(", ", $_POST['allergeni']);
    }
    $lista_allergeni = $conn->real_escape_string($lista_allergeni);

    $query_img = ""; 
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        $target_dir = "../imgs/prodotti/";
        $filename = time() . "_" . basename($_FILES["immagine"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {
            $query_img = ", immagine = '$filename'";
        }
    }

    $sql = "UPDATE alimenti SET 
            nome_piatto = '$nome', 
            prezzo = $prezzo, 
            descrizione = '$desc', 
            id_categoria = $id_cat,
            lista_allergeni = '$lista_allergeni' 
            $query_img 
            WHERE id_alimento = $id_alimento";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../dashboards/manager.php?msg=success");
    } else {
        echo "Errore modifica: " . $conn->error;
    }
}
?>