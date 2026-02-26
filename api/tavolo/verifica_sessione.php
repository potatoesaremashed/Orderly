<?php
// Questo microscopico script protegge contro i clienti furbetti.
// Viene interrogato in LOOP ogni 5 secondi da js/tavolo.js per accertarsi che un amministratore non abbia chiuso il conto

require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = intval($_SESSION['id_tavolo']);
$tokenCookie = $_COOKIE['device_token_' . $idTavolo] ?? '';

$stmt = $conn->prepare("SELECT stato, device_token FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $idTavolo);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// La sessione del cliente esplode e viene dichiarato invalida e rimandata alla home se:
// 1. Il tavolo non è più "Occupato" (es: manager ha cliccato libero)
// 2. Oppure la chiave crittografica del cookie (device_token) è stata piallata (NULL) dal DB
// 3. Oppure il token presente qui non corrisponde più a quello del DB (qualcun'altro si è loggato col qr e ci ha usurpato il tavolo)

$valida = $row && $row['stato'] === 'occupato' && !empty($row['device_token']) && $row['device_token'] === $tokenCookie;

echo json_encode(['valida' => $valida]);
?>