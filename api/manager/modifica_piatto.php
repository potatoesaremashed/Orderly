<?php
require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

$idPiatto = intval($_POST['id_alimento']);
$nomePiatto = $_POST['nome_piatto'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$descrizione = $_POST['descrizione'] ?? '';
$idCategoria = intval($_POST['id_categoria'] ?? 0);
$allergeni = empty($_POST['allergeni']) ? "" : implode(", ", $_POST['allergeni']);

$immagineBinaria = null;
$aggiornaImmagine = isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0;
if ($aggiornaImmagine) {
    $immagineBinaria = file_get_contents($_FILES["immagine"]["tmp_name"]);
}

if ($aggiornaImmagine) {
    $stmt = $conn->prepare("UPDATE alimenti SET nome_piatto=?, prezzo=?, descrizione=?, id_categoria=?, lista_allergeni=?, immagine=? WHERE id_alimento=?");
    $stmt->bind_param("sdsissi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $immagineBinaria, $idPiatto);
} else {
    $stmt = $conn->prepare("UPDATE alimenti SET nome_piatto=?, prezzo=?, descrizione=?, id_categoria=?, lista_allergeni=? WHERE id_alimento=?");
    $stmt->bind_param("sdsisi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $idPiatto);
}

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=success#menu");
} else {
    echo "Errore: " . $stmt->error;
}
?>