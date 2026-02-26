<?php
require_once "../../include/auth/manager_auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die("Accesso negato.");
if (empty($_POST['id_categoria']))
    die("ID categoria mancante.");

$id = intval($_POST['id_categoria']);
$stmt = $conn->prepare("DELETE FROM categorie WHERE id_categoria = ?");
$stmt->bind_param("i", $id);

try {
    if ($stmt->execute()) {
        header("Location: ../../dashboards/manager.php?msg=cat_deleted");
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif;padding:40px;text-align:center;color:#721c24;background:#f8d7da;'>";
    echo "<h2>Impossibile eliminare!</h2>";
    echo "<p>Ci sono ancora piatti collegati a questa categoria.</p>";
    echo "<br><a href='../../dashboards/manager.php' style='padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>Indietro</a>";
    echo "</div>";
}
?>