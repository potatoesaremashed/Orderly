<?php
/**
 * Connessione al Database MySQL.
 * Crea un oggetto di connessione ($conn) usato da tutte le API e dashboard.
 * 
 * Parametri: host, username, password, nome_database
 * Database: ristorante_db (definito in templatedb.sql)
 */
$conn = mysqli_connect("localhost", "root", "", "ristorante_db");
?>