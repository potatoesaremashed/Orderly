<?php
require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");
if (empty($_POST['id_alimento']))
    die("ID piatto mancante.");

$id = intval($_POST['id_alimento']);
$stmt = $conn->prepare("DELETE FROM alimenti WHERE id_alimento = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=deleted");
} else {
    echo "Errore: " . $stmt->error;
}
?>