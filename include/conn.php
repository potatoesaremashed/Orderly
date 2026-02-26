<?php
// Configurazione ai parametri del database MySQL
$conn = mysqli_connect("localhost", "root", "", "ristorante_db");

// Interrompe l'esecuzione e mostra un errore se la connessione fallisce
if (!$conn)
    die("Errore di connessione: " . mysqli_connect_error());
?>