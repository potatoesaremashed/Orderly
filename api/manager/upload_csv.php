<?php
require_once "../../include/auth/manager_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die(json_encode(['success' => false, 'error' => 'Metodo non consentito']));

if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'error' => 'Nessun file ricevuto o errore durante il caricamento.']));
}

$fileTmpPath = $_FILES['file_csv']['tmp_name'];
$importati = 0;
$aggiornati = 0;
$errori = 0;

if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
    // Prima riga: intestazioni (saltiamo)
    $header = fgetcsv($handle, 1000, ",");

    // Prepariamo la query per fare insert o update
    // Struttura CSV ipotetica: id_alimento,nome_piatto,prezzo,descrizione,lista_allergeni,immagine,id_categoria
    $stmt = $conn->prepare("INSERT INTO alimenti (id_alimento, nome_piatto, prezzo, descrizione, lista_allergeni, id_categoria) 
                            VALUES (?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                                nome_piatto = VALUES(nome_piatto),
                                prezzo = VALUES(prezzo),
                                descrizione = VALUES(descrizione),
                                lista_allergeni = VALUES(lista_allergeni),
                                id_categoria = VALUES(id_categoria)");

    if (!$stmt) {
        die(json_encode(['success' => false, 'error' => "Errore preparazione query: " . $conn->error]));
    }

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) < 7)
            continue; // Salta le righe incomplete

        $id_alimento = intval($data[0]);
        $nome_piatto = $data[1];
        $prezzo = floatval($data[2]);
        $descrizione = $data[3];
        $lista_allergeni = $data[4];
        $id_categoria = intval($data[6]);

        // Controlla se il piatto esiste già per conteggio
        $check = $conn->query("SELECT id_alimento FROM alimenti WHERE id_alimento = $id_alimento");
        $esisteva = $check->num_rows > 0;

        $stmt->bind_param("isdssi", $id_alimento, $nome_piatto, $prezzo, $descrizione, $lista_allergeni, $id_categoria);

        if ($stmt->execute()) {
            if ($esisteva)
                $aggiornati++;
            else
                $importati++;
        }
        else {
            $errori++;
        }
    }
    fclose($handle);

    echo json_encode([
        'success' => true,
        'message' => "CSV elaborato. Aggiunti: $importati, Aggiornati: $aggiornati." . ($errori > 0 ? " Errori: $errori" : "")
    ]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Impossibile leggere il file CSV.']);
}
?>
