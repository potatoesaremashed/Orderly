<?php
require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

$nomePiatto = $_POST['nome_piatto'] ?? '';
$descrizione = $_POST['descrizione'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$idCategoria = intval($_POST['id_categoria'] ?? 0);
$allergeni = empty($_POST['allergeni']) ? "" : implode(",", $_POST['allergeni']);

$immagineBinaria = null;
if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] === 0) {
    $immagineBinaria = file_get_contents($_FILES["immagine"]["tmp_name"]);
}

$stmt = $conn->prepare("INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdiss", $nomePiatto, $descrizione, $prezzo, $idCategoria, $immagineBinaria, $allergeni);

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=success#menu");
} else {
    echo "Errore: " . $stmt->error;
}
?>