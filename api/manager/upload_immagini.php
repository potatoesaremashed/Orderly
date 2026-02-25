<?php
require_once "../../include/auth/manager_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST")
    die(json_encode(['success' => false, 'error' => 'Metodo non consentito']));

if (empty($_FILES['immagini'])) {
    die(json_encode(['success' => false, 'error' => 'Nessuna immagine ricevuta.']));
}

$fileCount = count($_FILES['immagini']['name']);
$aggiornati = 0;
$nonTrovati = 0;
$errori = 0;

$stmtQuery = $conn->prepare("SELECT id_alimento FROM alimenti WHERE id_alimento = ? OR LOWER(nome_piatto) = ? LIMIT 1");
$stmtUpdate = $conn->prepare("UPDATE alimenti SET immagine = ? WHERE id_alimento = ?");

for ($i = 0; $i < $fileCount; $i++) {
    if ($_FILES['immagini']['error'][$i] === UPLOAD_ERR_OK) {
        $nomeFileCompleto = $_FILES['immagini']['name'][$i];
        $tmpName = $_FILES['immagini']['tmp_name'][$i];

        $info = pathinfo($nomeFileCompleto);
        $nomeBaseLiscio = $info['filename'];

        $id_tentativo = is_numeric($nomeBaseLiscio) ? intval($nomeBaseLiscio) : 0;
        $nome_tentativo = strtolower(trim($nomeBaseLiscio));

        $stmtQuery->bind_param("is", $id_tentativo, $nome_tentativo);
        $stmtQuery->execute();
        $result = $stmtQuery->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idReale = $row['id_alimento'];

            $immagineBinaria = file_get_contents($tmpName);

            $stmtUpdate->bind_param("bi", $null, $idReale);
            $stmtUpdate->send_long_data(0, $immagineBinaria);

            if ($stmtUpdate->execute()) {
                $aggiornati++;
            }
            else {
                $errori++;
            }
        }
        else {
            $nonTrovati++;
        }
    }
    else {
        $errori++;
    }
}

echo json_encode([
    'success' => true,
    'message' => "Immagini collegate: $aggiornati.\\nNon trovati: $nonTrovati.\\nErrori: $errori."
]);
?>
