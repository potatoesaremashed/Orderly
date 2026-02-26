<?php
require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

$nome = trim($_POST['nome_categoria'] ?? '');
$idMenu = intval($_POST['id_menu'] ?? 0);
if (empty($nome))
    die("La categoria deve avere un nome!");

$stmt = $conn->prepare("INSERT INTO categorie (nome_categoria, id_menu) VALUES (?, ?)");
$stmt->bind_param("si", $nome, $idMenu);

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=cat_success");
} else {
    echo "Errore: " . $stmt->error;
}
?>