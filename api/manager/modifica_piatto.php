<?php
// Riceve i pacchetti POST Form Multipart dal Modal "Modifica Piatto" 

require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

$idPiatto = intval($_POST['id_alimento']);
$nomePiatto = $_POST['nome_piatto'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$descrizione = $_POST['descrizione'] ?? '';
$idCategoria = intval($_POST['id_categoria'] ?? 0);

// Appiattisce le checkbox Array in una sola casella CSV 
$allergeni = empty($_POST['allergeni']) ? "" : implode(", ", $_POST['allergeni']);

$immagineBinaria = null;
// Verifica se il manager ha effettivamente uploadato un nuovo file, ignorando invece ricaricamenti a vuoti.
$aggiornaImmagine = isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0;

if ($aggiornaImmagine) {
    // Si preleva il BLOB binario
    $immagineBinaria = file_get_contents($_FILES["immagine"]["tmp_name"]);
}

// Fork dei comandi SQL a seconda se è presente la foto rimpiazzo oppure dobbiamo ignorare la vecchia immagine e mantenerla.
if ($aggiornaImmagine) {
    $stmt = $conn->prepare("UPDATE alimenti SET nome_piatto=?, prezzo=?, descrizione=?, id_categoria=?, lista_allergeni=?, immagine=? WHERE id_alimento=?");
    $stmt->bind_param("sdsissi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $immagineBinaria, $idPiatto);
} else {
    // Modifica rapida solo descrizioni / prezzi omettiendo l'UPDATE dell'immagine.
    $stmt = $conn->prepare("UPDATE alimenti SET nome_piatto=?, prezzo=?, descrizione=?, id_categoria=?, lista_allergeni=? WHERE id_alimento=?");
    $stmt->bind_param("sdsisi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $idPiatto);
}

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=success#menu");
} else {
    echo "Errore: " . $stmt->error;
}
?>