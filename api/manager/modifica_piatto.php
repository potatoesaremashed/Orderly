<?php
require_once "../../include/auth/manager_auth.php";

// Ferma lo script se l'utente cerca di accedere alla pagina direttamente dall'URL
if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Accesso negato.");

// Prendi i dati testuali dal form e assicurati che siano del tipo giusto (numeri o testo)
$idPiatto = intval($_POST['id_alimento']);
$nomePiatto = $_POST['nome_piatto'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$descrizione = $_POST['descrizione'] ?? '';
$idCategoria = intval($_POST['id_categoria'] ?? 0);

// Unisci le spunte degli allergeni in una sola riga di testo separata da virgole
$allergeni = empty($_POST['allergeni']) ? "" : implode(", ", $_POST['allergeni']);

$aggiornaImmagine = false;
$immagineBinaria = null;

// Controlla se l'utente ha caricato una nuova foto e in quel caso trasformala in dati grezzi
if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {
    $immagineBinaria = file_get_contents($_FILES["immagine"]["tmp_name"]);
    $aggiornaImmagine = true;
}

// Prepara la richiesta di modifica per il database usando due strade diverse: con o senza foto
if ($aggiornaImmagine) {
    $aggiornamento = $conn->prepare("UPDATE alimenti SET nome_piatto = ?, prezzo = ?, descrizione = ?, id_categoria = ?, lista_allergeni = ?, immagine = ? WHERE id_alimento = ?");
    $aggiornamento->bind_param("sdsissi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $immagineBinaria, $idPiatto);
} else {
    $aggiornamento = $conn->prepare("UPDATE alimenti SET nome_piatto = ?, prezzo = ?, descrizione = ?, id_categoria = ?, lista_allergeni = ? WHERE id_alimento = ?");
    $aggiornamento->bind_param("sdsisi", $nomePiatto, $prezzo, $descrizione, $idCategoria, $allergeni, $idPiatto);
}

// Lancia il comando sul database e rimanda il manager al menu in caso di vittoria
if ($aggiornamento->execute()) {
    header("Location: ../../dashboards/manager.php?msg=success#menu");
} else {
    // Altrimenti stampa l'errore nudo e crudo a schermo
    echo "Errore aggiornamento: " . $aggiornamento->error;
}
?>