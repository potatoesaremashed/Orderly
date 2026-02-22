<?php
/**
 * =========================================
 * FILE: include/conn.php
 * =========================================
 * Questo file gestisce il "ponte" (connessione) tra il codice PHP e il database MySQL.
 * Viene incluso in quasi tutti i file del progetto per permettere le operazioni sui dati
 * (lettura ordini, inserimento piatti, ecc.).
 * 
 * Parametri di connessione:
 * - "localhost": Il server del database (in locale)
 * - "root": L'utente amministratore predefinito di MySQL
 * - "": La password (vuota di default in XAMPP)
 * - "ristorante_db": Il nome del database che contiene le tabelle del progetto
 */

// mysqli_connect() è la funzione PHP che tenta di aprire la connessione
$conn = mysqli_connect("localhost", "root", "", "ristorante_db");

// Suggerimento per Junior: Se la connessione fallisce, è bene saperlo subito
if (!$conn) {
    die("Errore di connessione: " . mysqli_connect_error());
}
?>