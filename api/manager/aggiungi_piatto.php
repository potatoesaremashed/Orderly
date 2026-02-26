<?php
// Aggiunge un nuovo elemento Piatto/Alimento con relativi parametri, foto e allergeni

require_once "../../include/auth/manager_auth.php";

// Blocca richieste Get manuali dal browser
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

// Estrapola campi testo e numeri dal FormData 
$nomePiatto = $_POST['nome_piatto'] ?? '';
$descrizione = $_POST['descrizione'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$idCategoria = intval($_POST['id_categoria'] ?? 0);

// Concatena l'array degli allergeni in una singola stringa CSV (es. "Glutine,Lattosio") da salvare sul DB
$allergeni = empty($_POST['allergeni']) ? "" : implode(",", $_POST['allergeni']);

$immagineBinaria = null;
// Processa l'upload della foto convertendo il file fisico caricato temporaneamente in dati binari LONGBLOB nativi MySQL
if (isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] === 0) {
    $immagineBinaria = file_get_contents($_FILES["immagine"]["tmp_name"]);
}

// Inserzione nel catalogo prodotti 
$stmt = $conn->prepare("INSERT INTO alimenti (nome_piatto, descrizione, prezzo, id_categoria, immagine, lista_allergeni) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdiss", $nomePiatto, $descrizione, $prezzo, $idCategoria, $immagineBinaria, $allergeni);

if ($stmt->execute()) {
    // Redirige al termine mostrando il popup di successo ed atterrando sull'ancora #menu
    header("Location: ../../dashboards/manager.php?msg=success#menu");
} else {
    echo "Errore: " . $stmt->error;
}
?>