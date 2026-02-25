<?php
require_once "../../include/auth/manager_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Accesso negato.");

$nomeCategoria = trim($_POST['nome_categoria'] ?? '');
$idMenu = intval($_POST['id_menu'] ?? 0);

if (empty($nomeCategoria)) die("La categoria deve avere un nome!");

$inserimento = $conn->prepare("INSERT INTO categorie (nome_categoria, id_menu) VALUES (?, ?)");
$inserimento->bind_param("si", $nomeCategoria, $idMenu);

if ($inserimento->execute()) {
    header("Location: ../../dashboards/manager.php?msg=cat_success");
} else {
    echo "Errore salvataggio: " . $inserimento->error;
}
?>
