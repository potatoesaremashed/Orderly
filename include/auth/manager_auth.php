<?php
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'manager') {
    http_response_code(403);
    die("Accesso negato.");
}
require_once __DIR__ . '/../conn.php';
?>