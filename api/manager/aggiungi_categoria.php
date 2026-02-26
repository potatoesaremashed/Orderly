<?php
// Aggiunge una nuova categoria ('Primi', 'Secondi') al Menu del Ristorante.

require_once "../../include/auth/manager_auth.php";

// Previene gli accessi diretti tramite barra degli indirizzi URL
if ($_SERVER["REQUEST_METHOD"] !== "POST") //se il metodo non fosse POST
    die("Accesso negato.");

// Recupera i dati inviati dal Form Modal "Aggiungi Categoria"
$nome = trim($_POST['nome_categoria'] ?? ''); //assegna il valore di nome_categoria all'input se non esiste null
$idMenu = intval($_POST['id_menu'] ?? 0); //assegna il valore di id_menu all'input se non esiste null

if (empty($nome))
    die("La categoria deve avere un nome!");

// Inserisce nel Database proteggendosi contro SQL-Injection con bind_param
$stmt = $conn->prepare("INSERT INTO categorie (nome_categoria, id_menu) VALUES (?, ?)");
$stmt->bind_param("si", $nome, $idMenu); //s=string, i=integer

if ($stmt->execute()) {
    // Se ha successo, ricarica la pagina principale forzando un feedback visivo verde 
    header("Location: ../../dashboards/manager.php?msg=cat_success");
} else {
    echo "Errore: " . $stmt->error;
}
?>