<?php
// Endpoint in uso in kanban cucia per dire "Spunta, sto preparando", "Spunta, è pronto!"

session_start();
include "../../include/conn.php"; //import del file che viene usato come funzione
header('Content-Type: application/json'); //previene la lettura automatica in html del browser indicandogli il tipo di file json

// definizione di una array associativa
$ruoli_ammessi = ['cuoco', 'manager', 'admin'];

if (!isset($_SESSION['ruolo']) || !in_array($_SESSION['ruolo'], $ruoli_ammessi)) { //se il ruolo della sessione non esistesse o non fosse valido
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']); //stampa json con success false e messaggio non autorizzato dopo aver convertito il json in un array associativa di php
    exit;
}

$input = json_decode(file_get_contents('php://input'), true); //decodifica il json in array associativa
$id_ordine = $input['id_ordine'] ?? null; //assegna il valore di id_ordine all'input se non esiste null
$nuovo_stato = $input['nuovo_stato'] ?? null; //assegna il valore di nuovo_stato all'input se non esiste null

// Pipeline standard su cui viaggia una comanda 
$stati_validi = ['in_attesa', 'in_preparazione', 'pronto']; //array associativa con i stati validi

if (!$id_ordine || !in_array($nuovo_stato, $stati_validi)) { //se l'id_ordine non esiste o il nuovo_stato non è valido
    echo json_encode(['success' => false, 'message' => 'Dati non validi.']); //stampa json con success false e messaggio "dati non validi"
    exit;
}

// Inserisce il comando che retrocede di colore il badge sulla schermata dei tavoli in attesa fuori dalla cucina
$stmt = $conn->prepare("UPDATE ordini SET stato = ? WHERE id_ordine = ?"); //prepara la query
$stmt->bind_param("si", $nuovo_stato, $id_ordine); //lega i parametri alla query (s=string, i=integer)

//stampa json con success true o false e messaggio di errore se necessario
echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Errore: ' . $conn->error]);