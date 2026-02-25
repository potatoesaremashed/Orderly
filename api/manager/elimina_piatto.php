<?php
require_once "../../include/auth/manager_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Accesso negato.");
if (empty($_POST['id_alimento'])) die("ID piatto mancante.");

$idPiatto = intval($_POST['id_alimento']);
$eliminazione = $conn->prepare("DELETE FROM alimenti WHERE id_alimento = ?");
$eliminazione->bind_param("i", $idPiatto);

if ($eliminazione->execute()) {
    header("Location: ../../dashboards/manager.php?msg=deleted");
} else {
    echo "Errore eliminazione: " . $eliminazione->error;
}
?>
