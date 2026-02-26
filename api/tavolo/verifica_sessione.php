<?php
require_once "../../include/auth/tavolo_auth.php";
header('Content-Type: application/json');

$idTavolo = intval($_SESSION['id_tavolo']);
$tokenCookie = $_COOKIE['device_token_' . $idTavolo] ?? '';

$stmt = $conn->prepare("SELECT stato, device_token FROM tavoli WHERE id_tavolo = ?");
$stmt->bind_param("i", $idTavolo);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// Session is invalid if: table is free/reserved, or device_token was cleared by admin
$valida = $row && $row['stato'] === 'occupato' && !empty($row['device_token']) && $row['device_token'] === $tokenCookie;

echo json_encode(['valida' => $valida]);
?>