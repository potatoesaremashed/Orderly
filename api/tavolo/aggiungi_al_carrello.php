<?php
// Aggiunge un piatto (o aumenta la sua quantità) nel carrello temporaneo del tavolo

require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

// Sfrutta la sessione sicura per sapere a chi attribuire il piatto
$idTavolo = $_SESSION['id_tavolo'];
$idPiatto = intval($_POST['id_alimento'] ?? 0);
$qta = intval($_POST['quantita'] ?? 1);

// Sanity check
if ($idPiatto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Piatto non valido.']);
    exit;
}

// Cerca se il tavolo ha già una comanda aperta ma non ancora inviata (carrello = 'in_attesa')
$res = $conn->query("SELECT id_ordine FROM ordini WHERE id_tavolo = $idTavolo AND stato = 'in_attesa' LIMIT 1");

if ($res->num_rows > 0) {
    // Esiste già il carrello contenitore
    $idOrdine = $res->fetch_assoc()['id_ordine'];
} else {
    // Altrimenti ne crea testualmente uno ex-novo assegnandogli lo stato 'in_attesa'
    $conn->query("INSERT INTO ordini (id_tavolo, stato, data_ora) VALUES ($idTavolo, 'in_attesa', NOW())");
    $idOrdine = $conn->insert_id;
}

// Verifica se lo stesso piatto è già stato inserito precedentemente in questo specifico carrello
$check = $conn->query("SELECT quantita FROM dettaglio_ordini WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");

if ($check->num_rows > 0) {
    // Se è già dentro, somma semplicemente la quantità nuova alla vecchia (es: 1 pizza + 1 pizza = 2 pizze)
    $conn->query("UPDATE dettaglio_ordini SET quantita = quantita + $qta WHERE id_ordine = $idOrdine AND id_alimento = $idPiatto");
} else {
    // Se non è dentro, lo inserisce come nuova riga scontrino temporanea
    $conn->query("INSERT INTO dettaglio_ordini (id_ordine, id_alimento, quantita) VALUES ($idOrdine, $idPiatto, $qta)");
}

echo json_encode(['success' => true, 'message' => 'Aggiunto al carrello.']);
?>