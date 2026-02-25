<?php
/**
 * =========================================
 * DASHBOARD: Manager (Pannello di Controllo)
 * =========================================
 * Questa è l'interfaccia principale per il proprietario del ristorante.
 * Da qui si gestisce tutto: tavoli, menu, categorie e prezzi.
 * 
 * Questa è la pagina più complessa del progetto. Utilizza una struttura "SPA"
 * (Single Page Application) rudimentale: invece di caricare più file, 
 * mostriamo e nascondiamo diversi 'div' (sezioni) cliccando sulla sidebar.
 */

session_start();

/**
 * SICUREZZA
 * Verifichiamo che chi entra abbia davvero la "chiave" da manager.
 */
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'manager') {
    header("Location: ../index.php");
    exit;
}

include "../include/conn.php";
include "../include/header.php";

// Recuperiamo la lista dei tavoli per mostrarli subito nella prima tab.
$tavoli = $conn->query("SELECT * FROM tavoli ORDER BY nome_tavolo ASC");
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Agganciamo i CSS specifici per il manager -->
<link rel="stylesheet" href="../css/manager.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../css/common.css?v=<?php echo time(); ?>">

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <!-- ========== SIDEBAR (MENU LATERALE) ========== -->
        <div class="col-md-3 col-lg-2 d-none d-md-block">
            <div class="sidebar-custom d-flex flex-column">
                <div class="text-center mb-5 mt-3"><img src="../imgs/ordlogo.png" width="100"></div>

                <div class="px-3 flex-grow-1">
                    <small class="text-uppercase fw-bold ps-3 mb-2 d-block text-muted" style="font-size: 11px;">Pannello Admin</small>
                    <!-- I bottoni della sidebar scatenano la funzione switchPage() in manager.js -->
                    <div class="btn-sidebar active" onclick="switchPage('tavoli', this)">
                        <i class="fas fa-chair me-3"></i> Gestione Tavoli
                    </div>
                    <div class="btn-sidebar" onclick="switchPage('menu', this)">
                        <i class="fas fa-utensils me-3"></i> Gestione Menu
                    </div>
                </div>

                <div class="p-4 mt-auto">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="theme-toggle-sidebar" onclick="toggleTheme()" title="Cambia Tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </div>
                        <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Esci">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== CONTENUTO PRINCIPALE ========== -->
        <div class="col-md-9 col-lg-10">

            <!-- Mobile Nav Bar: Barra di navigazione per cellulari -->
            <div class="mobile-nav-bar d-md-none">
                <div class="mobile-nav-btn active" onclick="switchPage('tavoli', this)">
                    <i class="fas fa-chair"></i> Tavoli
                </div>
                <div class="mobile-nav-btn" onclick="switchPage('menu', this)">
                    <i class="fas fa-utensils"></i> Menu
                </div>
            </div>

            <!-- ===== SEZIONE: GESTIONE TAVOLI ===== -->
            <div id="page-tavoli" class="page-section active">
                <div class="page-header">
                    <div>
                        <h2 class="fw-bold m-0">Gestione Tavoli</h2>
                        <p class="text-muted m-0 small">Controlla lo stato delle prenotazioni in tempo reale</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="apriModalAggiungi()">
                            <i class="fas fa-plus me-2"></i>Nuovo Tavolo
                        </button>
                    </div>
                </div>

                <!-- Filtri veloci per i tavoli -->
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filtraTavoli('tutti', this)">
                        <i class="fas fa-th me-1"></i> Tutti
                        <span class="filter-count" id="count-tutti">0</span>
                    </button>
                    <button class="filter-tab" onclick="filtraTavoli('libero', this)">
                        <span class="status-dot dot-libero"></span> Liberi
                        <span class="filter-count" id="count-libero">0</span>
                    </button>
                    <button class="filter-tab" onclick="filtraTavoli('occupato', this)">
                        <span class="status-dot dot-occupato"></span> Occupati
                        <span class="filter-count" id="count-occupato">0</span>
                    </button>
                    <button class="filter-tab" onclick="filtraTavoli('riservato', this)">
                        <span class="status-dot dot-riservato"></span> Riservati
                        <span class="filter-count" id="count-riservato">0</span>
                    </button>
                </div>

                <!-- Griglia dei tavoli: Viene svuotata e riempita via JavaScript -->
                <div class="tavoli-grid" id="tavoli-grid">
                </div>
            </div>

            <!-- ===== SEZIONE: GESTIONE MENU ===== -->
            <div id="page-menu" class="page-section" style="display: none;">
                <div class="container py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold m-0">Gestione Menu</h2>
                            <p class="text-muted m-0 small">Aggiungi, modifica o elimina piatti dal menu</p>
                        </div>
                    </div>

                    <!-- Messaggio di successo dopo un'operazione -->
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                        <div id="success-alert" class="alert alert-success border-0 shadow-sm rounded-3 mb-4 text-center fw-bold text-success">
                            Menu aggiornato correttamente!
                        </div>
                    <?php
endif; ?>

                    <div class="row g-4">
                        <!-- COLONNA AGGIUNTA PIATTO -->
                        <div class="col-lg-8">
                            <div class="card-custom">
                                <h5 class="card-title"><i class="fas fa-utensils me-2 text-warning"></i>Nuovo Piatto</h5>
                                <form action="../api/manager/aggiungi_piatto.php" method="POST" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <input type="text" name="nome_piatto" class="form-control" required placeholder="Nome del piatto">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" step="0.01" name="prezzo" class="form-control" required placeholder="Prezzo (€)">
                                        </div>
                                        <div class="col-md-12">
                                            <select name="id_categoria" class="form-select" required>
                                                <option value="" selected disabled>Seleziona Categoria</option>
                                                <?php
// Loop PHP: creiamo le opzioni del menu a tendina prendendole dal DB.
$res = $conn->query("SELECT * FROM categorie");
while ($cat = $res->fetch_assoc()) {
    echo "<option value='" . $cat['id_categoria'] . "'>" . $cat['nome_categoria'] . "</option>";
}
?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="descrizione" class="form-control" rows="2" placeholder="Descrizione ingredienti..."></textarea>
                                        </div>

                                        <!-- Scelta multipla degli allergeni -->
                                        <div class="col-12">
                                            <label class="small text-muted fw-bold mb-2">ALLERGENI PRESENTI</label>
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
                                            <label class="small text-muted fw-bold">FOTO DEL PIATTO</label>
                                            <input type="file" name="immagine" class="form-control" accept="image/*" required>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <button type="submit" class="btn-main">Aggiungi Piatto</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- COLONNA CATEGORIE -->
                        <div class="col-lg-4">
                            <div class="card-custom mb-4">
                                <h5 class="card-title"><i class="fas fa-tags me-2 text-primary"></i>Nuova Categoria</h5>
                                <form action="../api/manager/aggiungi_categoria.php" method="POST" class="d-flex gap-2">
                                    <input type="text" name="nome_categoria" class="form-control" placeholder="Es: Burger" required>
                                    <input type="hidden" name="id_menu" value="1">
                                    <button type="submit" class="btn btn-dark rounded-3"><i class="fas fa-plus"></i></button>
                                </form>
                            </div>

                            <div class="card-custom">
                                <h5 class="card-title">Gestione Categorie</h5>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <table class="table-custom">
                                        <tbody>
                                            <?php
$res_cat = $conn->query("SELECT * FROM categorie ORDER BY nome_categoria");
while ($row = $res_cat->fetch_assoc()) {
    echo "<tr>
                                                        <td><strong>" . $row['nome_categoria'] . "</strong></td>
                                                        <td class='text-end'>
                                                            <form action='../api/manager/elimina_categoria.php' method='POST' onsubmit='return confirm(\"Attenzione! Eliminare questa categoria cancellerà tutti i piatti collegati. Continuare?\");'>
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

                    <!-- SEZIONE DRAG E DROP: CSV e IMMAGINI -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card-custom text-center dropzone-card h-100" id="dropzone-csv" onclick="document.getElementById('input-csv').click()">
                                <h5><i class="fas fa-file-csv text-success mb-2" style="font-size: 2rem;"></i></h5>
                                <h6 class="fw-bold">Carica Menu da CSV</h6>
                                <p class="small text-muted mb-0">Trascina qui il tuo file <b>menu.csv</b> per aggiornare massivamente i piatti.</p>
                                <input type="file" id="input-csv" accept=".csv" style="display: none;">
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="card-custom text-center dropzone-card h-100" id="dropzone-img" onclick="document.getElementById('input-img').click()">
                                <h5><i class="fas fa-images text-primary mb-2" style="font-size: 2rem;"></i></h5>
                                <h6 class="fw-bold">Carica Immagini Piatti</h6>
                                <p class="small text-muted mb-0">Trascina foto (es. <b>1.jpg</b> o <b>Carbonara.jpg</b>) o clicca per collegarle rapidamente.</p>
                                <input type="file" id="input-img" accept="image/*" multiple style="display: none;">
                            </div>
                        </div>
                    </div>

                    <!-- TABELLA MENU COMPLETO -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card-custom">
                                <h5 class="card-title"><i class="fas fa-book-open me-2 text-info"></i>Lista Piatti Attivi</h5>
                                <div class="table-responsive">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>
                                                <th>Piatto</th>
                                                <th class="col-desc">Estratto Descrizione</th>
                                                <th>Prezzo</th>
                                                <th class="text-end">Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
$result = $conn->query("SELECT * FROM alimenti ORDER BY nome_piatto ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Sanitizziamo i dati per evitare che simboli strani rompano il codice HTML.
        $allergeniSafe = htmlspecialchars($row['lista_allergeni'], ENT_QUOTES);
        $descSafe = htmlspecialchars($row['descrizione'], ENT_QUOTES);
        $nomeSafe = htmlspecialchars($row['nome_piatto'], ENT_QUOTES);

        echo "<tr>
                                                            <td class='fw-bold'>" . $row['nome_piatto'] . "</td>
                                                            <td class='col-desc small text-muted'>" . substr($row['descrizione'], 0, 80) . "...</td>
                                                            <td class='fw-bold text-success'>" . number_format($row['prezzo'], 2) . " €</td>
                                                            <td class='text-end'>
                                                                <div class='d-flex justify-content-end gap-2'>
                                                                    <!-- Passiamo i dati al modal di modifica tramite attributi 'data-' -->
                                                                    <button type='button' class='btn btn-warning btn-sm text-white' 
                                                                        onclick='apriModalModifica(this)'
                                                                        data-id='" . $row['id_alimento'] . "'
                                                                        data-nome='" . $nomeSafe . "'
                                                                        data-desc='" . $descSafe . "'
                                                                        data-prezzo='" . $row['prezzo'] . "'
                                                                        data-cat='" . $row['id_categoria'] . "'
                                                                        data-img='" . ($row['immagine'] ? 'data:image/jpeg;base64,' . base64_encode($row['immagine']) : '') . "'
                                                                        data-allergeni='" . $allergeniSafe . "'>
                                                                        <i class='fas fa-edit'></i>
                                                                    </button>

                                                                    <form action='../api/manager/elimina_piatto.php' method='POST' onsubmit='return confirm(\"Vuoi davvero eliminare questo piatto dal menu?\");' style='margin:0;'>
                                                                        <input type='hidden' name='id_alimento' value='" . $row['id_alimento'] . "'>
                                                                        <button type='submit' class='btn btn-danger btn-sm'>
                                                                            <i class='fas fa-trash'></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>";
    }
}
else {
    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Non ci sono ancora piatti nel menu. Inizia ad aggiungerne uno!</td></tr>";
}
?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALI (FINESTRE POPUP) ================= -->
<?php include "../include/modals/manager_modals.php"; ?>

<!-- Colleghiamo il file JavaScript che gestisce tutte le interazioni della dashboard -->
<script src="../js/manager.js?v=<?php echo time(); ?>"></script>

<?php include "../include/footer.php"; ?>
