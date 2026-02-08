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

<style>
    /* --- VARIABILI COLORI --- */
    :root {
        --primary: #ff9f43;
        --dark: #2d3436;
        --bg-color: #f8f9fa;
        --surface-color: #ffffff;
        --text-main: #2d3436;
        --text-muted: #636e72; 
        --border-color: rgba(0,0,0,0.05);
        --card-radius: 20px;
        --price-color: #00a8ff; /* BLU */
        --shadow-color: rgba(0,0,0,0.05);
        --capsule-bg: #f1f2f6;
    }

    /* --- DARK MODE CONFIG (Aggiornata per contrasto alto) --- */
    [data-theme="dark"] {
        --bg-color: #121212;
        --surface-color: #1e1e1e;
        --text-main: #ffffff !important; /* Forza il bianco */
        --text-muted: #e0e0e0 !important; /* Grigio chiarissimo quasi bianco */
        --border-color: rgba(255,255,255,0.1);
        --shadow-color: rgba(0,0,0,0.5);
        --capsule-bg: #2d3436;
        --dark: #ffffff;
    }

    body { 
        background-color: var(--bg-color); 
        font-family: 'Poppins', sans-serif; 
        color: var(--text-main);
        user-select: none; 
        transition: background-color 0.3s, color 0.3s;
    }

    /* Override per Bootstrap in Dark Mode */
    [data-theme="dark"] .text-muted { color: var(--text-muted) !important; }
    [data-theme="dark"] .text-dark { color: #fff !important; }

    /* Forza il colore BLU per i prezzi */
    .text-price { color: var(--price-color) !important; }
    
    /* --- SIDEBAR & LAYOUT --- */
    .sidebar-custom {
        background: var(--surface-color);
        min-height: 100vh;
        border-right: 1px solid var(--border-color);
        padding-top: 2rem;
        transition: background 0.3s;
    }

    .sticky-header {
        background: rgba(var(--surface-color), 0.95);
        backdrop-filter: blur(12px);
        padding: 1.5rem;
        position: sticky; top: 0; z-index: 999;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.3s;
    }
    [data-theme="dark"] .sticky-header { background: rgba(30, 30, 30, 0.95); }

    .btn-categoria {
        display: flex; align-items: center;
        padding: 18px 25px; margin-bottom: 8px;
        border-radius: 15px;
        color: var(--text-muted); font-weight: 600; font-size: 1.1rem;
        cursor: pointer; transition: 0.2s;
    }
    .btn-categoria:hover { background-color: rgba(255, 159, 67, 0.1); color: var(--primary); }
    .btn-categoria.active { background-color: var(--primary); color: white !important; box-shadow: 0 4px 15px rgba(255, 159, 67, 0.4); }
    .btn-categoria.active .text-muted { color: white !important; }

    /* --- CARD PRODOTTO --- */
    .card-prodotto {
        background: var(--surface-color);
        border-radius: var(--card-radius);
        overflow: hidden;
        transition: transform 0.2s, background 0.3s;
        height: 100%; position: relative;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 15px var(--shadow-color);
    }
    .card-prodotto:hover { transform: translateY(-5px); box-shadow: 0 10px 25px var(--shadow-color); border-color: var(--price-color); cursor: pointer; }
    
    .img-wrapper { height: 180px; overflow: hidden; position: relative; }
    .img-prodotto { width: 100%; height: 100%; object-fit: cover; }
    .price-tag {
        position: absolute; bottom: 12px; right: 12px;
        background: var(--surface-color); padding: 6px 18px; border-radius: 30px;
        font-weight: 700; font-size: 1.1rem; color: var(--price-color) !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .card-body { padding: 1.5rem; }
    .piatto-title { font-weight: 700; margin-bottom: 5px; font-size: 1.2rem; color: var(--text-main); }
    .piatto-desc { font-size: 0.9rem; color: var(--text-muted); height: 45px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

    /* --- CONTROLLI QUANTITÀ --- */
    .qty-capsule {
        background: var(--capsule-bg);
        border-radius: 50px; padding: 5px;
        display: flex; justify-content: space-between; align-items: center;
        width: 100%; max-width: 150px; height: 55px;
        z-index: 10; position: relative;
    }
    .btn-circle {
        width: 45px; height: 45px;
        border-radius: 50%; border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: transform 0.1s; flex-shrink: 0;
    }
    .btn-circle:active { transform: scale(0.9); }
    .btn-minus { background: var(--surface-color); color: #ff6b6b; border: 1px solid var(--border-color); }
    .btn-plus { background: var(--text-main); color: var(--bg-color); }
    .qty-input { background: transparent; border: none; width: 50px; text-align: center; font-weight: 700; font-size: 1.4rem; color: var(--text-main); }

    /* --- STILE BOTTONE VERDE (Aggiungi) --- */
    .btn-green-custom {
        background-color: #2ecc71; 
        color: white;
        border: none;
        transition: all 0.2s;
    }
    .btn-green-custom:hover {
        background-color: #27ae60; 
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(46, 204, 113, 0.4);
    }
    .btn-green-custom:active { transform: scale(0.98); }

    /* --- BADGES & UI --- */
    .badge-alg {
        background: var(--surface-color); color: #e67e22; border: 1px solid var(--primary); 
        font-size: 0.75rem; font-weight: 600; padding: 5px 12px; border-radius: 50px;
        margin-right: 5px; margin-bottom: 5px; display: inline-block;
    }
    .theme-toggle {
        cursor: pointer; width: 45px; height: 45px;
        border-radius: 50%; background: var(--capsule-bg);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; transition: 0.3s;
        color: var(--text-main);
    }
    #pezzi-header {
        display: inline-block; min-width: 28px; height: 28px; line-height: 20px;
        text-align: center; background-color: white !important; color: #212529 !important;
        font-weight: 800; padding: 4px; border-radius: 50%; margin-left: 8px;
    }

    /* --- MODALI FIX COLORI --- */
    .modal-content-custom { background-color: var(--surface-color); color: var(--text-main); border-radius: 25px; border: none; }
    .bg-light-custom { background-color: var(--bg-color) !important; }
    .btn-close { filter: invert(var(--invert-val)); } 
    [data-theme="dark"] { --invert-val: 1; } [data-theme="light"] { --invert-val: 0; }
    
    /* Fix specifico per gli elementi del carrello che diventavano bianchi/neri */
    .list-group-item {
        background-color: transparent !important; /* Rende trasparente lo sfondo della lista */
        color: var(--text-main) !important; /* Forza il colore del testo corretto */
        border-color: var(--border-color) !important;
    }
</style>

<div class="container-fluid">
    <div class="row g-0">
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar-custom">
            <div class="text-center mb-5 mt-3"><img src="../imgs/ordnobg.png" width="100"></div>
            <div class="px-3">
                <small class="text-uppercase fw-bold ps-3 mb-2 d-block text-muted" style="font-size: 11px;">Menu</small>
                <div class="btn-categoria active" onclick="filtraCategoria('all', this)"><i class="fas fa-utensils me-3"></i> Tutto</div>
                <?php while($cat = $categorie->fetch_assoc()): ?>
                    <div class="btn-categoria" onclick="filtraCategoria(<?php echo $cat['id_categoria']; ?>, this)">
                        <i class="fas fa-bookmark me-3"></i> <?php echo $cat['nome_categoria']; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="sticky-header d-flex justify-content-between align-items-center">
                <div><h2 class="m-0 fw-bold">Ordina al Tavolo</h2><p class="m-0 text-muted">Scegli i piatti che preferisci</p></div>
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema"><i class="fas fa-moon" id="theme-icon"></i></div>
                    
                    <div class="text-end d-none d-sm-block me-3 ms-3">
                        <small class="text-uppercase fw-bold d-block text-muted" style="font-size: 11px;">Totale Ordine</small>
                        <div class="fw-bold fs-3 text-price price-stable"><span id="soldi-header">0.00</span>€</div>
                    </div>

                    <button class="btn btn-dark rounded-pill px-4 py-3 shadow-sm d-flex align-items-center" onclick="apriStorico()">
                        <i class="fas fa-receipt"></i> 
                    </button>
                    
                    <button class="btn btn-dark rounded-pill px-4 py-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCarrello" onclick="aggiornaModale()">
                        <i class="fas fa-shopping-bag fa-lg"></i> 
                        <span class="d-none d-md-inline fw-bold ms-2">Carrello</span>
                        <span id="pezzi-header">0</span>
                    </button>
                </div>
            </div>

            <div class="p-4 pb-5">
                <div class="row g-4">
                    <?php while($p = $prodotti->fetch_assoc()): ?>
                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 item-prodotto" data-cat="<?php echo $p['id_categoria']; ?>">
                        <div class="card-prodotto" onclick="apriZoom(this)" 
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
                                    $allergeni = explode(',', $p['lista_allergeni']); 
                                    foreach($allergeni as $a) { 
                                        if(trim($a) != "") echo "<span class='badge-alg'>".trim($a)."</span>"; 
                                    } 
                                    ?>
                                </div>
                                
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid var(--border-color);">
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
        <div><h3 class="modal-title fw-bold">Il tuo Ordine 🧾</h3><p class="m-0 text-muted">Controlla e modifica le quantità</p></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="corpo-carrello" style="min-height: 300px;"></div>
      <div class="modal-footer border-0 p-4 d-flex justify-content-between align-items-center bg-light-custom">
        <div><small class="text-uppercase fw-bold text-muted">Totale Finale</small><h2 class="m-0 fw-bold text-price price-stable"><span id="totale-modale">0.00</span>€</h2></div>
        <button id="btn-invia-ordine" class="btn btn-dark rounded-pill px-5 py-3 fs-5 fw-bold shadow" disabled>INVIA ORDINE <i class="fas fa-paper-plane ms-2"></i></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalZoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content modal-content-custom shadow-lg overflow-hidden">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-lg-6 position-relative bg-light-custom" style="min-height: 350px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <img id="zoom-img" src="" class="w-100 h-100" style="object-fit: cover; position: absolute; top:0; left:0;">
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
                                    <button class="btn-circle btn-minus" onclick="updateZoomQty(-1)"><i class="fas fa-minus"></i></button>
                                    <span class="qty-input" id="zoom-qty-display">1</span>
                                    <button class="btn-circle btn-plus" onclick="updateZoomQty(1)"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>

                            <button class="btn btn-green-custom w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm d-flex justify-content-between px-4" id="btn-zoom-add" onclick="confermaZoom()">
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
  <div id="liveToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
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
                <div class="mb-4"><i class="fas fa-question-circle fa-5x text-primary animate__animated animate__pulse animate__infinite"></i></div>
                <h2 class="fw-bold mb-3">Sei pronto?</h2>
                <p class="text-muted mb-4 fs-5">L'ordine verrà inviato direttamente alla cucina.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-bold" data-bs-dismiss="modal">ANNULLA</button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow" id="confirm-send-btn">SÌ, ORDINA!</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSuccesso" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom border-0 shadow-lg text-center p-5">
            <div class="success-animation">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>
            </div>
            <h2 class="fw-bold mt-4 mb-2">Ordine Inviato!</h2>
            <p class="text-muted">La cucina ha ricevuto la tua comanda.</p>
        </div>
    </div>
</div>

<script>
// --- STATE MANAGEMENT ---
let carrello = {}; 
let totaleSoldi = 0; 
let totalePezzi = 0;
// Stato temporaneo per il modale (inizia sempre da 1 quando apri)
let zoomState = { id: null, nome: '', prezzo: 0, qtyAttuale: 1 };

// --- THEME & SETUP ---
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    if (body.getAttribute('data-theme') === 'dark') {
        body.setAttribute('data-theme', 'light'); icon.classList.replace('fa-sun', 'fa-moon'); localStorage.setItem('theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark'); icon.classList.replace('fa-moon', 'fa-sun'); localStorage.setItem('theme', 'dark');
    }
}
if (localStorage.getItem('theme') === 'dark') { document.body.setAttribute('data-theme', 'dark'); document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun'); }

function filtraCategoria(idCat, elemento) {
    document.querySelectorAll('.btn-categoria').forEach(el => el.classList.remove('active')); elemento.classList.add('active');
    document.querySelectorAll('.item-prodotto').forEach(piatto => { piatto.style.display = (idCat === 'all' || piatto.getAttribute('data-cat') == idCat) ? 'block' : 'none'; });
}

// --- LOGICA CARRELLO ---
function gestisciCarrello(id, delta, prezzo, nome) {
    const input = document.getElementById('q-' + id);
    let valAttuale = parseInt(input.value);
    let valNuovo = valAttuale + delta;
    
    if (valNuovo >= 0) {
        input.value = valNuovo;
        totaleSoldi += (delta * prezzo); 
        totalePezzi += delta;
        
        document.getElementById('soldi-header').innerText = totaleSoldi.toFixed(2);
        document.getElementById('pezzi-header').innerText = totalePezzi;
        
        if (!carrello[id]) carrello[id] = { id: id, nome: nome, qta: 0, prezzo: prezzo };
        carrello[id].qta = valNuovo;
        if (carrello[id].qta === 0) delete carrello[id];
        
        checkInvioButton();
        if (document.getElementById('modalCarrello').classList.contains('show')) aggiornaModale();
    }
}

function checkInvioButton() {
    const btn = document.getElementById('btn-invia-ordine');
    if (totalePezzi > 0) { btn.removeAttribute('disabled'); btn.classList.remove('btn-secondary'); btn.classList.add('btn-dark'); } 
    else { btn.setAttribute('disabled', 'disabled'); btn.classList.remove('btn-dark'); btn.classList.add('btn-secondary'); }
}

function aggiornaModale() {
    const container = document.getElementById('corpo-carrello');
    const totaleSpan = document.getElementById('totale-modale');
    if (Object.keys(carrello).length === 0) { container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center h-100 py-5"><div class="display-1 mb-3" style="opacity:0.3">🍽️</div><h5 class="fw-bold text-muted">Il carrello è vuoto</h5></div>`; totaleSpan.innerText = '0.00'; return; }
    let html = '<div class="list-group list-group-flush">';
    for (const [id, item] of Object.entries(carrello)) {
        let parziale = (item.qta * item.prezzo).toFixed(2);
        html += `
            <div class="cart-item list-group-item d-flex justify-content-between align-items-center border-0 mb-1">
                <div><h5 class="m-0 fw-bold">${item.nome}</h5><small class="text-price fw-bold fs-6">${item.prezzo}€ cad.</small></div>
                <div class="d-flex align-items-center gap-3">
                    <div class="qty-capsule">
                        <button class="btn-circle btn-minus" onclick="gestisciCarrello(${id}, -1, ${item.prezzo}, '${item.nome.replace(/'/g, "\\'")}')"><i class="fas fa-minus"></i></button>
                        <span class="qty-input">${item.qta}</span>
                        <button class="btn-circle btn-plus" onclick="gestisciCarrello(${id}, 1, ${item.prezzo}, '${item.nome.replace(/'/g, "\\'")}')"><i class="fas fa-plus"></i></button>
                    </div>
                    <span class="fw-bold fs-4 text-price price-stable">${parziale}€</span>
                </div>
            </div>`;
    }
    html += '</div>'; container.innerHTML = html; totaleSpan.innerText = totaleSoldi.toFixed(2);
}

// --- LOGICA ZOOM & AGGIUNTA ---
function apriZoom(card) {
    const id = card.getAttribute('data-id');
    const nome = card.getAttribute('data-nome');
    const desc = card.getAttribute('data-desc');
    const prezzo = parseFloat(card.getAttribute('data-prezzo'));
    const img = card.getAttribute('data-img');
    const allergeniRaw = card.getAttribute('data-allergeni');

    document.getElementById('zoom-nome').innerText = nome;
    document.getElementById('zoom-desc').innerText = desc;
    document.getElementById('zoom-prezzo-unitario').innerText = prezzo.toFixed(2);
    document.getElementById('zoom-img').src = img;
    
    const divAlg = document.getElementById('zoom-allergeni'); divAlg.innerHTML = '';
    if(allergeniRaw) { allergeniRaw.split(',').forEach(a => { if(a.trim()) divAlg.innerHTML += `<span class="badge-alg">${a.trim()}</span>`; }); } 
    else { divAlg.innerHTML = '<span class="small text-muted">Nessuno</span>'; }

    zoomState = { id: id, nome: nome, prezzo: prezzo, qtyAttuale: 1 };
    
    refreshZoomUI();
    new bootstrap.Modal(document.getElementById('modalZoom')).show();
}

function updateZoomQty(delta) {
    let nuovoValore = zoomState.qtyAttuale + delta;
    if (nuovoValore < 1) nuovoValore = 1; 
    zoomState.qtyAttuale = nuovoValore;
    refreshZoomUI();
}

function refreshZoomUI() {
    document.getElementById('zoom-qty-display').innerText = zoomState.qtyAttuale;
    document.getElementById('zoom-btn-totale').innerText = (zoomState.qtyAttuale * zoomState.prezzo).toFixed(2) + '€';
}

function confermaZoom() {
    let qtaDaAggiungere = zoomState.qtyAttuale;
    if (qtaDaAggiungere > 0) {
        gestisciCarrello(zoomState.id, qtaDaAggiungere, zoomState.prezzo, zoomState.nome);
        mostraToast(zoomState.nome);
    }
    bootstrap.Modal.getInstance(document.getElementById('modalZoom')).hide();
}

function mostraToast(nomePiatto) {
    document.getElementById('toast-msg').innerText = `${nomePiatto} aggiunto al carrello!`;
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
    toast.show();
}

// --- INVIO ORDINE ---
document.getElementById('btn-invia-ordine').onclick = function() {
    bootstrap.Modal.getInstance(document.getElementById('modalCarrello')).hide();
    new bootstrap.Modal(document.getElementById('modalConfermaOrdine')).show();
};

document.getElementById('confirm-send-btn').onclick = function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Invio in corso...';

    const listaProdotti = Object.values(carrello).map(item => { return { id: item.id, qta: item.qta }; });

    fetch('../api/invia_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prodotti: listaProdotti })
    })
    .then(res => res.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('modalConfermaOrdine')).hide();
        if(data.success) {
            new bootstrap.Modal(document.getElementById('modalSuccesso')).show();
            setTimeout(() => { location.reload(); }, 2500);
        } else {
            alert("Errore: " + data.message);
            btn.disabled = false; btn.innerText = "SÌ, ORDINA!";
        }
    })
    .catch(err => {
        console.error(err);
        alert("Errore di connessione!");
        btn.disabled = false; btn.innerText = "SÌ, ORDINA!";
    });
};
</script>

<?php include "../include/footer.php"; ?>