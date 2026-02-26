<?php
// Script attivato premendo l'icona Cestino dal pannello di Gestione Menu del Manager

require_once "../../include/auth/manager_auth.php";

// Misura di sicurezza base
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");

// Verifica la consistenza dei payload inviati
if (empty($_POST['id_alimento']))
    die("ID piatto mancante.");

$id = intval($_POST['id_alimento']);
// Elimina irrimediabilmente la riga dal listino prodotti
$stmt = $conn->prepare("DELETE FROM alimenti WHERE id_alimento = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../dashboards/manager.php?msg=deleted");
} else {
    echo "Errore: " . $stmt->error;
}
?>