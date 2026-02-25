<?php
// Tenta di agganciarti al database MySQL in locale con le credenziali di default (root)
$conn = mysqli_connect("localhost", "root", "", "ristorante_db");

// Blocca tutto e mostra l'errore se il database non risponde
if (!$conn) {
    die("Errore di connessione: " . mysqli_connect_error());
}
?>