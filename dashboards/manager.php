<?php
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}
include "../include/conn.php";
include "../include/header.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/manager.css">
<link rel="stylesheet" href="../css/common.css">


<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Gestione Menu</h2>
            <p class="text-muted m-0 small">Benvenuto, <?php echo $_SESSION['username']; ?></p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i>
            </div>
            <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Abbandona Dashboard">
                <i class="fas fa-sign-out-alt p-2 mt-auto"></i>
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            <i class="fas fa-check-circle me-2"></i> Operazione completata!
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card-custom">
                <h5 class="card-title"><i class="fas fa-utensils me-2 text-warning"></i>Nuovo Piatto</h5>
                <form action="../api/aggiungi_piatto.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="nome_piatto" class="form-control" required
                                placeholder="Nome del piatto">
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" name="prezzo" class="form-control" required
                                placeholder="Prezzo (€)">
                        </div>
                        <div class="col-md-12">
                            <select name="id_categoria" class="form-select" required>
                                <option value="" selected disabled>Seleziona Categoria</option>
                                <?php
                                $res = $conn->query("SELECT * FROM categorie");
                                while ($cat = $res->fetch_assoc()) {
                                    echo "<option value='" . $cat['id_categoria'] . "'>" . $cat['nome_categoria'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <textarea name="descrizione" class="form-control" rows="2"
                                placeholder="Descrizione ingredienti..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="small text-muted fw-bold mb-2">ALLERGENI</label>
                            <div class="d-flex flex-wrap gap-2 p-3 rounded allergeni-box">
                                <?php
                                $allergeni = ["Glutine", "Crostacei", "Uova", "Pesce", "Arachidi", "Soia", "Latte", "Frutta a guscio", "Sedano", "Senape", "Sesamo", "Solfiti", "Molluschi"];
                                foreach ($allergeni as $a) {
                                    echo "<div class='form-check form-check-inline m-0 me-3'>
                                            <input class='form-check-input' type='checkbox' name='allergeni[]' value='$a' id='al_$a'>
                                            <label class='form-check-label small' for='al_$a'>$a</label>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="small text-muted fw-bold">FOTO PIATTO</label>
                            <input type="file" name="immagine" class="form-control" accept="image/*" required>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn-main">Aggiungi al Menu</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom mb-4">
                <h5 class="card-title"><i class="fas fa-tags me-2 text-primary"></i>Nuova Categoria</h5>
                <form action="../api/aggiungi_categoria.php" method="POST" class="d-flex gap-2">
                    <input type="text" name="nome_categoria" class="form-control" placeholder="Es: Dolci" required>
                    <input type="hidden" name="id_menu" value="1">
                    <button type="submit" class="btn btn-dark rounded-3"><i class="fas fa-plus"></i></button>
                </form>
            </div>

            <div class="card-custom">
                <h5 class="card-title">Lista Categorie</h5>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="table-custom">
                        <tbody>
                            <?php
                            $res_cat = $conn->query("SELECT * FROM categorie ORDER BY nome_categoria");
                            while ($row = $res_cat->fetch_assoc()) {
                                echo "<tr>
                                        <td><strong>" . $row['nome_categoria'] . "</strong></td>
                                        <td class='text-end'>
                                            <form action='../api/elimina_categoria.php' method='POST' onsubmit='return confirm(\"Eliminare questa categoria?\");'>
                                                <input type='hidden' name='id_categoria' value='" . $row['id_categoria'] . "'>
                                                <button class='btn-action btn-delete'><i class='fas fa-trash'></i></button>
                                            </form>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card-custom">
                <h5 class="card-title"><i class="fas fa-book-open me-2 text-info"></i>Menu Completo</h5>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Piatto</th>
                                <th class="col-desc">Descrizione</th>
                                <th>Prezzo</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query aggiornata per prendere anche id_categoria
                            $result = $conn->query("SELECT * FROM alimenti ORDER BY nome_piatto ASC");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $allergeniSafe = htmlspecialchars($row['lista_allergeni'], ENT_QUOTES);
                                    $descSafe = htmlspecialchars($row['descrizione'], ENT_QUOTES);
                                    $nomeSafe = htmlspecialchars($row['nome_piatto'], ENT_QUOTES);

                                    echo "<tr>
                                            <td class='fw-bold'>" . $row['nome_piatto'] . "</td>
                                            <td class='col-desc small text-muted'>" . substr($row['descrizione'], 0, 40) . "...</td>
                                            <td style='color: var(--primary); font-weight:bold;'>" . number_format($row['prezzo'], 2) . " €</td>
                                            <td class='text-end'>
                                                <div class='d-flex justify-content-end gap-2'>
                                                    <button type='button' class='btn btn-warning btn-sm text-white' 
                                                        onclick='apriModalModifica(this)'
                                                        data-id='" . $row['id_alimento'] . "'
                                                        data-nome='" . $nomeSafe . "'
                                                        data-desc='" . $descSafe . "'
                                                        data-prezzo='" . $row['prezzo'] . "'
                                                        data-cat='" . $row['id_categoria'] . "'
                                                        data-img='" . $row['immagine'] . "'
                                                        data-allergeni='" . $allergeniSafe . "'>
                                                        <i class='fas fa-edit'></i>
                                                    </button>

                                                    <form action='../api/elimina_piatto.php' method='POST' onsubmit='return confirm(\"Eliminare questo piatto?\");' style='margin:0;'>
                                                        <input type='hidden' name='id_alimento' value='" . $row['id_alimento'] . "'>
                                                        <button type='submit' class='btn btn-danger btn-sm'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Nessun piatto inserito.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalModifica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Modifica Piatto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../api/modifica_piatto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_alimento" id="mod_id">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="small text-muted">Nome Piatto</label>
                            <input type="text" name="nome_piatto" id="mod_nome" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Prezzo (€)</label>
                            <input type="number" step="0.01" name="prezzo" id="mod_prezzo" class="form-control"
                                required>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted">Categoria</label>
                            <select name="id_categoria" id="mod_cat" class="form-select" required>
                                <?php
                                // Ricarica le categorie da presentare nel modal
                                $res_mod = $conn->query("SELECT * FROM categorie");
                                while ($cat = $res_mod->fetch_assoc()) {
                                    echo "<option value='" . $cat['id_categoria'] . "'>" . $cat['nome_categoria'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted">Descrizione</label>
                            <textarea name="descrizione" id="mod_desc" class="form-control" rows="3"
                                style="resize: none;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="small text-muted fw-bold mb-2">ALLERGENI</label>
                            <div class="d-flex flex-wrap gap-2 p-3 rounded bg-allergeni-custom">
                                <?php
                                $allergeniList = ["Glutine", "Crostacei", "Uova", "Pesce", "Arachidi", "Soia", "Latte", "Frutta a guscio", "Sedano", "Senape", "Sesamo", "Solfiti", "Molluschi"];
                                foreach ($allergeniList as $a) {
                                    echo "<div class='form-check form-check-inline m-0 me-3'>
                                    <input class='form-check-input mod-allergeni' type='checkbox' name='allergeni[]' value='$a' id='mod_al_$a'>
                                    <label class='form-check-label small' for='mod_al_$a'>$a</label>
                                </div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3">
                                <img id="preview_img" src=""
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                <div class="w-100">
                                    <label class="small text-muted">Cambia Foto (Lascia vuoto per mantenere
                                        l'attuale)</label>
                                    <input type="file" name="immagine" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-0 mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary fw-bold">Salva Modifiche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../js/manager.js"></script>

<?php include "../include/footer.php"; ?>