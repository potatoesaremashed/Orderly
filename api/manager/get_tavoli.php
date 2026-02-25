<?php
require_once "../../include/auth/manager_auth.php";
header('Content-Type: application/json');

// Costruisci la query per pescare tutti i tavoli e i loro ordini aperti in un colpo solo
$queryTavoli = "SELECT t.id_tavolo, t.nome_tavolo, t.password,
        COALESCE(t.stato, 'libero') as stato,
        COALESCE(t.posti, 4) as posti, t.id_menu,
        (SELECT COUNT(*) FROM ordini o WHERE o.id_tavolo = t.id_tavolo AND o.stato != 'pronto') as ordini_attivi
        FROM tavoli t ORDER BY t.nome_tavolo ASC";

// Fai la domanda al database
$risultato = $conn->query($queryTavoli);

// Impacchetta tutti i tavoli in un array strutturato bello pronto (o crea array vuoto se non c'è niente)
$listaTavoli = $risultato ? $risultato->fetch_all(MYSQLI_ASSOC) : [];

// Trasforma i tavoli in formato JSON e inviali all'interfaccia grafica
echo json_encode($listaTavoli);
?>