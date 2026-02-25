<?php
require_once "../../include/auth/manager_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Accesso negato.");
if (empty($_POST['id_categoria'])) die("ID categoria mancante.");

$idCategoria = intval($_POST['id_categoria']);
$eliminazione = $conn->prepare("DELETE FROM categorie WHERE id_categoria = ?");
$eliminazione->bind_param("i", $idCategoria);

try {
    if ($eliminazione->execute()) {
        header("Location: ../../dashboards/manager.php?msg=cat_deleted");
    } else {
        throw new Exception($eliminazione->error);
    }
} catch (Exception $errore) {
    echo "<div style='font-family: sans-serif; padding: 40px; text-align: center; color: #721c24; background: #f8d7da;'>";
    echo "<h2>Impossibile eliminare!</h2>";
    echo "<p>Ci sono ancora piatti collegati a questa categoria. Elimina o sposta prima i piatti.</p>";
    echo "<br><a href='../../dashboards/manager.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Indietro</a>";
    echo "</div>";
}
?>
