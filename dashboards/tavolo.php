<?php
session_start();
include "../include/conn.php";

// Controllo accesso
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    header("Location: ../index.php");
    exit;
}
include "../include/header.php";

// Recupero dati
$categorie = $conn->query("SELECT * FROM categorie");
$prodotti = $conn->query("SELECT * FROM alimenti");
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/tavolo.css">
<link rel="stylesheet" href="../css/common.css">



<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-md-3 col-lg-2 d-none d-md-block">
            <div class="sidebar-custom">
                <div class="text-center mb-5 mt-3"><img src="../imgs/ordlogo.png" width="100"></div>
                <div class="px-3">
                    <small class="text-uppercase fw-bold ps-3 mb-2 d-block text-muted" style="font-size: 11px;">Menu</small>
                    <div class="btn-categoria active" onclick="filtraCategoria('all', this)"><i
                            class="fas fa-utensils me-3"></i> Tutto</div>
                    <?php while ($cat = $categorie->fetch_assoc()): ?>
                        <div class="btn-categoria" onclick="filtraCategoria(<?php echo $cat['id_categoria']; ?>, this)">
                            <i class="fas fa-bookmark me-3"></i> <?php echo $cat['nome_categoria']; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="sticky-header d-flex justify-content-between align-items-center">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="search-bar" class="search-input" placeholder="Cerca un piatto..."
                        oninput="renderProdotti()">
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema"><i class="fas fa-moon"
                            id="theme-icon"></i></div>

                    <a href="../logout.php" class="theme-toggle text-decoration-none text-danger ms-2"
                        title="Abbandona Tavolo" style="border: 1px solid var(--border-color);">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>

                    <div class="text-end d-none d-sm-block me-3 ms-3">
                        <small class="text-uppercase fw-bold d-block text-muted" style="font-size: 11px;">Totale
                            Ordine</small>
                        <div class="fw-bold fs-3 text-price price-stable"><span id="soldi-header">0.00</span>€</div>
                    </div>

                    <button class="btn btn-dark rounded-pill px-4 py-3 shadow-sm d-flex align-items-center"
                        onclick="apriStorico()">
                        <i class="fas fa-receipt"></i>
                        <span class="d-none d-md-inline fw-bold ms-2">Ordini</span>
                    </button>

                    <button class="btn btn-dark rounded-pill px-4 py-3 shadow-sm d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#modalFiltri">
                        <i class="fas fa-filter"></i>
                        <span class="d-none d-md-inline fw-bold ms-2">Filtro</span>
                    </button>

                    <button class="btn btn-dark rounded-pill px-4 py-3 shadow-sm d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#modalCarrello" onclick="aggiornaModale()">
                        <i class="fas fa-shopping-bag fa-lg"></i>
                        <span class="d-none d-md-inline fw-bold ms-2">Carrello</span>
                        <span id="pezzi-header">0</span>
                    </button>
                </div>
            </div>

            <div class="p-4 pb-5">
                <!-- Mobile Category Bar (visible on small screens only) -->
                <div class="mobile-cat-bar d-md-none mb-3">
                    <div class="mobile-cat-btn active" onclick="filtraCategoria('all', this)">Tutto</div>
                    <?php
                    $catMobile = $conn->query("SELECT * FROM categorie");
                    while ($cm = $catMobile->fetch_assoc()): ?>
                        <div class="mobile-cat-btn" onclick="filtraCategoria(<?php echo $cm['id_categoria']; ?>, this)">
                            <?php echo $cm['nome_categoria']; ?>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="row g-4">
                    <?php while ($p = $prodotti->fetch_assoc()): ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 item-prodotto"
                            data-cat="<?php echo $p['id_categoria']; ?>">
                            <div class="card-prodotto" onclick="apriZoom(this)" data-id="<?php echo $p['id_alimento']; ?>"
                                data-nome="<?php echo htmlspecialchars($p['nome_piatto']); ?>"
                                data-desc="<?php echo htmlspecialchars($p['descrizione']); ?>"
                                data-prezzo="<?php echo $p['prezzo']; ?>"
                                data-img="../imgs/prodotti/<?php echo $p['immagine']; ?>"
                                data-allergeni="<?php echo htmlspecialchars($p['lista_allergeni']); ?>">

                                <div class="img-wrapper">
                                    <img src="../imgs/prodotti/<?php echo $p['immagine']; ?>" class="img-prodotto"
                                        loading="lazy">
                                    <div class="price-tag"><?php echo $p['prezzo']; ?>€</div>
                                </div>

                                <div class="card-body">
                                    <h5 class="piatto-title"><?php echo $p['nome_piatto']; ?></h5>
                                    <p class="piatto-desc"><?php echo $p['descrizione']; ?></p>
                                    <div class="mb-4" style="min-height: 25px;">
                                        <?php
                                        $allergeni = explode(',', $p['lista_allergeni']);
                                        foreach ($allergeni as $a) {
                                            if (trim($a) != "")
                                                echo "<span class='badge-alg'>" . trim($a) . "</span>";
                                        }
                                        ?>
                                    </div>

                                    <div class="mt-auto d-flex justify-content-between align-items-center pt-3"
                                        style="border-top: 1px solid var(--border-color);">
                                        <small class="fw-bold text-uppercase text-muted">Quantità</small>
                                        <input type="hidden" id="q-<?php echo $p['id_alimento']; ?>" value="0">
                                        <div class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                Vedi <i class="fas fa-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCarrello" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h3 class="modal-title fw-bold">Il tuo Ordine 🧾</h3>
                    <p class="m-0 text-muted">Controlla e modifica le quantità</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="corpo-carrello" style="min-height: 300px;"></div>
            <div class="modal-footer border-0 p-4 d-flex justify-content-between align-items-center bg-light-custom">
                <div><small class="text-uppercase fw-bold text-muted">Totale Finale</small>
                    <h2 class="m-0 fw-bold text-price price-stable"><span id="totale-modale">0.00</span>€</h2>
                </div>
                <button id="btn-invia-ordine" class="btn btn-dark rounded-pill px-5 py-3 fs-5 fw-bold shadow"
                    disabled>INVIA ORDINE <i class="fas fa-paper-plane ms-2"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalZoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content modal-content-custom shadow-lg overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-lg-6 position-relative bg-light-custom"
                        style="min-height: 350px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <img id="zoom-img" src="" class="w-100 h-100"
                            style="object-fit: cover; position: absolute; top:0; left:0;">
                    </div>

                    <div class="col-lg-6 p-4 p-md-5 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">DETTAGLI</span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <h1 class="fw-bold mb-2" id="zoom-nome">Nome Piatto</h1>
                        <h4 class="fw-bold mb-4 text-muted"><span id="zoom-prezzo-unitario">0.00</span>€</h4>

                        <p class="lead mb-4 text-muted flex-grow-1" id="zoom-desc">Descrizione del piatto...</p>

                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-bold mb-2 text-muted">Allergeni</h6>
                            <div id="zoom-allergeni"></div>
                        </div>

                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold fs-5">Seleziona quantità</span>

                                <div class="qty-capsule" style="width: 140px;">
                                    <button class="btn-circle btn-minus" onclick="updateZoomQty(-1)"><i
                                            class="fas fa-minus"></i></button>
                                    <span class="qty-input" id="zoom-qty-display">1</span>
                                    <button class="btn-circle btn-plus" onclick="updateZoomQty(1)"><i
                                            class="fas fa-plus"></i></button>
                                </div>
                            </div>

                            <button
                                class="btn btn-green-custom w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm d-flex justify-content-between px-4"
                                id="btn-zoom-add" onclick="confermaZoom()">
                                <span>Aggiungi al carrello</span>
                                <span id="zoom-btn-totale">0.00€</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 2000">
    <div id="liveToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold fs-6">
                <i class="fas fa-check-circle me-2"></i> <span id="toast-msg">Prodotto aggiunto!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfermaOrdine" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4"><i
                        class="fas fa-question-circle fa-5x text-primary animate__animated animate__pulse animate__infinite"></i>
                </div>
                <h2 class="fw-bold mb-3">Sei pronto?</h2>
                <p class="text-muted mb-4 fs-5">L'ordine verrà inviato direttamente alla cucina.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-bold"
                        data-bs-dismiss="modal">ANNULLA</button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow"
                        id="confirm-send-btn">SÌ, ORDINA!</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalFiltri" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom shadow-lg">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h3 class="modal-title fw-bold">Filtra Allergeni 🚫</h3>
                    <p class="m-0 text-muted">Escludi piatti con questi allergeni</p>
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
                <button type="button" class="btn btn-link text-muted"
                    onclick="resettaFiltriAllergeni()">Resetta</button>
                <button type="button" class="btn btn-dark rounded-pill px-5 fw-bold" onclick="applicaFiltriAllergeni()"
                    data-bs-dismiss="modal">APPLICA</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSuccesso" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom border-0 shadow-lg text-center p-5">
            <div class="success-animation">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                </svg>
            </div>
            <h2 class="fw-bold mt-4 mb-2">Ordine Inviato!</h2>
            <p class="text-muted">La cucina ha ricevuto la tua comanda.</p>
        </div>
    </div>
</div>

<script src="../js/tavolo.js"></script>


<?php include "../include/footer.php"; ?>