<?php
session_start();
// Controllo accesso: solo manager
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}
include "../include/conn.php";
include "../include/header.php";
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard Gestione</h1>
        <span class="badge bg-primary fs-6">Benvenuto, <?php echo $_SESSION['username'] ?? 'Manager'; ?></span>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Piatto aggiunto al menu con successo!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Aggiungi Nuovo Piatto</h5>
        </div>
        <div class="card-body">
            <form action="../api/aggiungi_piatto.php" method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nome Piatto</label>
                    <input type="text" name="nome_piatto" class="form-control" required placeholder="Es: Carbonara">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Prezzo (€)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="prezzo" class="form-control" required placeholder="0.00">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Categoria</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="" selected disabled>Seleziona una categoria...</option>
                            <?php
                            // Recupera le categorie esistenti dal DB
                            $res_cat = $conn->query("SELECT * FROM categorie");
                            while($cat = $res_cat->fetch_assoc()) {
                                echo "<option value='".$cat['id_categoria']."'>".$cat['nome_categoria']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Descrizione</label>
                    <textarea name="descrizione" class="form-control" rows="3" placeholder="Descrivi il piatto..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Allergeni</label>
                    <div class="card p-3 bg-light border-0">
                        <div class="row">
                            <?php
                            // Lista standard allergeni (Normativa UE + comuni)
                            $lista_allergeni = [
                                "Glutine", "Crostacei", "Uova", "Pesce", 
                                "Arachidi", "Soia", "Latte", "Lattosio",
                                "Frutta a guscio", "Sedano", "Senape", 
                                "Sesamo", "Solfiti", "Lupini", "Molluschi"
                            ];

                            foreach ($lista_allergeni as $allergene) {
                                echo '
                                <div class="col-6 col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allergeni[]" value="'.$allergene.'" id="all_'.$allergene.'">
                                        <label class="form-check-label" for="all_'.$allergene.'">
                                            '.$allergene.'
                                        </label>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-text">Seleziona tutti gli allergeni presenti nel piatto.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Immagine Piatto</label>
                    <input type="file" name="immagine" class="form-control" accept="image/*" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">Aggiungi al Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "../include/footer.php"; ?>