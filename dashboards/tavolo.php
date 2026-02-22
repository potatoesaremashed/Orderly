<?php
/**
 * =========================================
 * DASHBOARD: Tavolo (Client Interface)
 * =========================================
 * Questa è la pagina che i clienti vedono sui tablet o i propri smartphone.
 * Funge da menu digitale interattivo.
 * 
 * 1. Visualizzazione piatti divisi per categoria.
 * 2. Filtro per allergeni (esclusione piatti pericolosi).
 * 3. Carrello dinamico (gestito via tavolo.js).
 * 4. Invio comanda in cucina con conferma via modal.
 */

session_start();
include "../include/conn.php";


/**
 * SICUREZZA
 * Solo se la sessione ha il ruolo 'tavolo' (scaturito dal login in index.php)
 * l'utente può stare su questa pagina.
 */
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    header("Location: ../index.php");
    exit;
}

include "../include/header.php";

// Carichiamo subito tutto dal DB. Il resto della "magia" lo farà il JavaScript.
$categorie = $conn->query("SELECT * FROM categorie");
$prodotti = $conn->query("SELECT * FROM alimenti");
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/tavolo.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../css/common.css?v=<?php echo time(); ?>">

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- ========== SIDEBAR: FILTRI CATEGORIA ========== -->
        <div class="col-md-3 col-lg-2 d-none d-md-block">
            <div class="sidebar-custom d-flex flex-column">
                <div class="text-center mb-5 mt-3"><img src="../imgs/ordlogo.png" width="100"></div>

                <div class="px-3 flex-grow-1 overflow-auto">
                    <small class="text-uppercase fw-bold ps-3 mb-2 d-block text-muted" style="font-size: 11px;">Esplora il Menu</small>
                    <!-- Filtro 'Tutto': richiama filtraCategoria con 'all' -->
                    <div class="btn-categoria active" onclick="filtraCategoria('all', this)">
                        <i class="fas fa-utensils me-3"></i> Tutto
                    </div>
                    <?php while ($cat = $categorie->fetch_assoc()): ?>
                        <div class="btn-categoria" onclick="filtraCategoria(<?php echo $cat['id_categoria']; ?>, this)">
                            <i class="fas fa-bookmark me-3"></i> <?php echo $cat['nome_categoria']; ?>
                        </div>
                    <?php
endwhile; ?>
                </div>

                <div class="p-4 mt-auto">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="theme-toggle-sidebar" onclick="toggleTheme()" title="Cambia Tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </div>
                        <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Chiudi Sessione">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== CONTENUTO PRINCIPALE ========== -->
        <div class="col-md-9 col-lg-10">
            <!-- HEADER STICKY: Cerca e Stato Carrello -->
            <div class="sticky-header d-flex justify-content-between align-items-center">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="search-bar" class="search-input" placeholder="Cerca un piatto..." oninput="renderProdotti()">
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2">
                    <div class="d-none d-sm-flex align-items-center me-2 bg-surface rounded-pill px-3 py-2 border shadow-sm">
                        <small class="text-uppercase fw-bold text-muted me-2" style="font-size: 10px;">Conto Stimato</small>
                        <div class="fw-bold fs-5 text-price price-stable"><span id="soldi-header">0.00</span>€</div>
                    </div>

                    <button class="btn btn-dark rounded-pill px-3 py-2 px-md-4 py-md-3 shadow-sm d-flex align-items-center" onclick="apriStorico()">
                        <i class="fas fa-receipt"></i>
                        <span class="d-none d-lg-inline fw-bold ms-2">I miei Ordini</span>
                    </button>

                    <button class="btn btn-dark rounded-pill px-3 py-2 px-md-4 py-md-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalFiltri">
                        <i class="fas fa-filter"></i>
                        <span class="d-none d-lg-inline fw-bold ms-2">Filtra</span>
                    </button>

                    <button class="btn btn-dark rounded-pill px-3 py-2 px-md-4 py-md-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCarrello" onclick="aggiornaModale()">
                        <i class="fas fa-shopping-bag fa-lg"></i>
                        <span class="d-none d-lg-inline fw-bold ms-2">Vai al Carrello</span>
                        <span id="pezzi-header" class="ms-1">0</span>
                    </button>
                </div>
            </div>

            <div class="p-4 pb-5">
                <!-- Mobile Category Bar (per schermi piccoli) -->
                <div class="mobile-cat-bar d-md-none mb-3">
                    <div class="mobile-cat-btn active" onclick="filtraCategoria('all', this)">Tutto</div>
                    <?php
$catMobile = $conn->query("SELECT * FROM categorie");
while ($cm = $catMobile->fetch_assoc()): ?>
                        <div class="mobile-cat-btn" onclick="filtraCategoria(<?php echo $cm['id_categoria']; ?>, this)">
                            <?php echo $cm['nome_categoria']; ?>
                        </div>
                    <?php
endwhile; ?>
                </div>

                <!-- GRIGLIA PRODOTTI -->
                <div class="row g-4">
                    <?php while ($p = $prodotti->fetch_assoc()): ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 item-prodotto" data-cat="<?php echo $p['id_categoria']; ?>">
                            <!-- La "Card" del prodotto scatta l'apertura dello Zoom se cliccata -->
                            <div class="card-prodotto" onclick="apriZoom(event, this)"
                                data-id="<?php echo $p['id_alimento']; ?>"
                                data-nome="<?php echo htmlspecialchars($p['nome_piatto']); ?>"
                                data-desc="<?php echo htmlspecialchars($p['descrizione']); ?>"
                                data-prezzo="<?php echo $p['prezzo']; ?>"
                                data-img="../imgs/prodotti/<?php echo $p['immagine']; ?>"
                                data-allergeni="<?php echo htmlspecialchars($p['lista_allergeni']); ?>">

                                <div class="img-wrapper">
                                    <img src="../imgs/prodotti/<?php echo $p['immagine']; ?>" class="img-prodotto" loading="lazy">
                                    <div class="price-tag"><?php echo $p['prezzo']; ?>€</div>
                                </div>

                                <div class="card-body">
                                    <h5 class="piatto-title"><?php echo $p['nome_piatto']; ?></h5>
                                    <p class="piatto-desc"><?php echo $p['descrizione']; ?></p>
                                    <div class="mb-4" style="min-height: 25px;">
                                        <?php
    // Esplodiamo la stringa degli allergeni salvata nel DB per creare i piccoli badge.
    $allergeni = explode(',', $p['lista_allergeni']);
    foreach ($allergeni as $a) {
        if (trim($a) != "")
            echo "<span class='badge-alg'>" . trim($a) . "</span>";
    }
?>
                                    </div>

                                    <!-- Controller di quantità rapido sotto ogni card -->
                                    <div class="mt-auto d-flex justify-content-center align-items-center pt-3" style="border-top: 1px solid var(--border-color);">
                                        <div class="qty-capsule-card d-flex align-items-center justify-content-between" style="background: var(--capsule-bg); border-radius: 15px; padding: 6px; width: 100%;">
                                            <button class="btn-card-qty" onclick="btnCardQty(event, <?php echo $p['id_alimento']; ?>, -1, <?php echo $p['prezzo']; ?>, '<?php echo addslashes($p['nome_piatto']); ?>')">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span id="q-<?php echo $p['id_alimento']; ?>" class="fw-bold fs-5" style="min-width: 30px; text-align: center;">0</span>
                                            <button class="btn-card-qty" onclick="btnCardQty(event, <?php echo $p['id_alimento']; ?>, 1, <?php echo $p['prezzo']; ?>, '<?php echo addslashes($p['nome_piatto']); ?>')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALI (FINESTRE POPUP) ================= -->

<!-- MODAL: Storico Ordini Inviati -->
<div class="modal fade" id="modalOrdini" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h3 class="modal-title fw-bold">I tuoi Piatti in Arrivo 📋</h3>
                    <p class="m-0 text-muted">Controlla lo stato delle tue ordinazioni</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2" id="corpo-ordini" style="min-height: 300px; max-height: 60vh; overflow-y: auto;">
                <!-- Riempito via JS leggendo api/tavolo/leggi_ordini_tavolo.php -->
            </div>
            <div class="modal-footer border-0 p-4 bg-light-custom d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase fw-bold text-muted">Totale già ordinato</small>
                    <h2 class="m-0 fw-bold text-price price-stable"><span id="totale-storico">0.00</span>€</h2>
                </div>
                <button type="button" class="btn btn-dark rounded-pill px-5 py-3 fw-bold" data-bs-dismiss="modal">CHIUDI</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Riepilogo Carrello prima dell'invio -->
<div class="modal fade" id="modalCarrello" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h3 class="modal-title fw-bold">Cosa desideri ordinare? 🧾</h3>
                    <p class="m-0 text-muted">Manda questi piatti in cucina</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="corpo-carrello" style="min-height: 300px;">
                <!-- Sezione dinamica gestita da tavolo.js -->
            </div>
            <div class="modal-footer border-0 p-4 d-flex justify-content-between align-items-center bg-light-custom">
                <div>
                    <small class="text-uppercase fw-bold text-muted">Costo attuale</small>
                    <h2 class="m-0 fw-bold text-price price-stable"><span id="totale-modale">0.00</span>€</h2>
                </div>
                <button id="btn-invia-ordine" class="btn btn-dark rounded-pill px-5 py-3 fs-5 fw-bold shadow" disabled>ORDINATE ORA! <i class="fas fa-paper-plane ms-2"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Zoom Dettaglio Prodotto -->
<div class="modal fade" id="modalZoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content modal-content-custom shadow-lg overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-lg-6 position-relative bg-light-custom" style="min-height: 350px; overflow:hidden;">
                        <img id="zoom-img" src="" class="w-100 h-100" style="object-fit: cover; position: absolute; top:0; left:0;">
                    </div>

                    <div class="col-lg-6 p-4 p-md-5 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">APPROFONDIMENTO</span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <h1 class="fw-bold mb-2" id="zoom-nome">Nome Piatto</h1>
                        <h4 class="fw-bold mb-4 text-muted"><span id="zoom-prezzo-unitario">0.00</span>€</h4>

                        <p class="lead mb-4 text-muted flex-grow-1" id="zoom-desc">Descrizione...</p>

                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-bold mb-2 text-muted">Allergeni evidenziati</h6>
                            <div id="zoom-allergeni"></div>
                        </div>

                        <div class="mb-4 flex-grow-1">
                            <h6 class="text-uppercase small fw-bold mb-2 text-muted">Note per lo Chef <small>(opz.)</small></h6>
                            <textarea class="form-control rounded-3" id="zoom-note" rows="2" placeholder="Es. Ben cotto, senza salse..." style="resize: none; background: #f8f9fa; border: 1px solid #ddd;"></textarea>
                        </div>

                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold fs-5">Seleziona quantità</span>
                                <div class="qty-capsule" style="width: 140px;">
                                    <button class="btn-circle btn-minus" onclick="updateZoomQty(-1)"><i class="fas fa-minus"></i></button>
                                    <span class="qty-input" id="zoom-qty-display">1</span>
                                    <button class="btn-circle btn-plus" onclick="updateZoomQty(1)"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <button class="btn btn-green-custom w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm d-flex justify-content-between px-4" id="btn-zoom-add" onclick="confermaZoom()">
                                <span>Aggiungi</span>
                                <span id="zoom-btn-totale">0.00€</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtri Allergeni -->
<div class="modal fade" id="modalFiltri" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h3 class="modal-title fw-bold">Gestione Intolleranze 🚫</h3>
                    <p class="m-0 text-muted">Seleziona cosa NON puoi mangiare</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="row g-2" id="lista-allergeni-filtro">
                    <?php
$allergeni = ["Glutine", "Crostacei", "Uova", "Pesce", "Arachidi", "Soia", "Lattosio", "Frutta a guscio", "Sedano", "Senape", "Sesamo", "Solfiti", "Molluschi"];
foreach ($allergeni as $a) {
    $safeId = str_replace(' ', '_', $a);
    echo '<div class="col-6">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" value="' . $a . '" id="f_' . $safeId . '">
                                    <label class="form-check-label fw-bold w-100 m-0" for="f_' . $safeId . '" style="cursor:pointer">' . $a . '</label>
                                </div>
                              </div>';
}
?>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 bg-light-custom justify-content-between">
                <button type="button" class="btn btn-link text-muted" onclick="resettaFiltriAllergeni()">Resetta Filtri</button>
                <button type="button" class="btn btn-dark rounded-pill px-5 fw-bold" onclick="applicaFiltriAllergeni()" data-bs-dismiss="modal">SALVA</button>
            </div>
        </div>
    </div>
</div>

<!-- Toasts e Messaggi -->
<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
    <div id="liveToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                <i class="fas fa-check-circle me-2"></i> <span id="toast-msg">Operazione riuscita!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Modal Finale dopo invio ordine -->
<div class="modal fade" id="modalSuccesso" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom border-0 shadow-lg text-center p-5">
            <div class="mb-4"><i class="fas fa-check-circle fa-5x text-success"></i></div>
            <h2 class="fw-bold mb-2">Comanda Inviata!</h2>
            <p class="text-muted">Il tuo ordine è stato ricevuto. Buon appetito!</p>
            <button class="btn btn-dark rounded-pill px-5 py-2 mt-3" data-bs-dismiss="modal">Perfetto!</button>
        </div>
    </div>
</div>

<!-- Carichiamo il file JS che gestisce il menu lato cliente -->
<script src="../js/tavolo.js?v=<?php echo time(); ?>"></script>

<?php include "../include/footer.php"; ?>
