/**
 * =========================================
 * FILE: js/tavolo.js
 * Descrizione: Logica completa lato cliente
 * =========================================
 */

// Stato globale
let carrello = {};
let totaleSoldi = 0;
let totalePezzi = 0;
let zoomState = { id: null, nome: '', prezzo: 0, qtyAttuale: 1 };

function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';
    const nuovoTema = isDark ? 'light' : 'dark';
    
    body.setAttribute('data-theme', nuovoTema);
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    localStorage.setItem('theme', nuovoTema);
}

/**
 * Filtra i prodotti per categoria
 */
function filtraCategoria(idCat, elemento) {
    document.querySelectorAll('.btn-categoria').forEach(el => el.classList.remove('active'));
    if(elemento) elemento.classList.add('active');
    
    document.querySelectorAll('.item-prodotto').forEach(piatto => {
        const catPiatto = piatto.getAttribute('data-cat');
        // Se idCat è 'all' mostra tutto, altrimenti controlla corrispondenza
        piatto.style.display = (idCat === 'all' || catPiatto == idCat) ? 'block' : 'none';
    });
}

/**
 * Modifica la quantità nel carrello e aggiorna l'interfaccia
 */
function gestisciCarrello(id, delta, prezzo, nome) {
    const input = document.getElementById('q-' + id);
    if(!input) return; // Sicurezza
    
    let valAttuale = parseInt(input.value) || 0;
    let valNuovo = valAttuale + delta;

    if (valNuovo >= 0) {
        input.value = valNuovo;
        totaleSoldi += (delta * prezzo);
        totalePezzi += delta;

        // Aggiorna header
        const soldiHeader = document.getElementById('soldi-header');
        const pezziHeader = document.getElementById('pezzi-header');
        if(soldiHeader) soldiHeader.innerText = Math.max(0, totaleSoldi).toFixed(2);
        if(pezziHeader) pezziHeader.innerText = Math.max(0, totalePezzi);

        // Aggiorna oggetto carrello
        if (!carrello[id]) carrello[id] = { id: id, nome: nome, qta: 0, prezzo: prezzo };
        carrello[id].qta = valNuovo;
        
        if (carrello[id].qta === 0) delete carrello[id];

        // Gestione stato bottone invio
        const btnInvia = document.getElementById('btn-invia-ordine');
        if(btnInvia) {
            if (totalePezzi > 0) {
                btnInvia.removeAttribute('disabled');
                btnInvia.classList.replace('btn-secondary', 'btn-dark');
            } else {
                btnInvia.setAttribute('disabled', 'true');
                btnInvia.classList.replace('btn-dark', 'btn-secondary');
            }
        }

        // Se il modale è aperto, aggiorna la lista in tempo reale
        const modalCarrello = document.getElementById('modalCarrello');
        if (modalCarrello && modalCarrello.classList.contains('show')) {
            aggiornaModale();
        }
    }
}

/**
 * Renderizza l'HTML dentro il modale carrello
 */
function aggiornaModale() {
    const container = document.getElementById('corpo-carrello');
    const totaleSpan = document.getElementById('totale-modale');
    
    if (Object.keys(carrello).length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                <div class="display-1 mb-3" style="opacity:0.3">🍽️</div>
                <h5 class="fw-bold text-muted">Il carrello è vuoto</h5>
            </div>`;
        totaleSpan.innerText = '0.00';
        return;
    }

    let html = '<div class="list-group list-group-flush w-100 px-3 py-2">'; 
    for (const [id, item] of Object.entries(carrello)) {
        let parziale = (item.qta * item.prezzo).toFixed(2);
        // Nota: uso replace su nome per evitare problemi con apostrofi nel JS in linea
        let nomeSafe = item.nome.replace(/'/g, "\\'");
        
        html += `
            <div class="cart-item list-group-item d-flex align-items-center border-0 mb-3 px-0" style="background: transparent;">
                
                <div style="flex: 1; min-width: 0;">
                    <h5 class="m-0 fw-bold text-truncate">${item.nome}</h5>
                    <small class="text-muted">${item.prezzo}€ cad.</small>
                </div>

                <div class="qty-capsule d-flex align-items-center justify-content-center mx-2" 
                     style="background: var(--capsule-bg); border-radius: 50px; width: 110px; height: 45px; flex-shrink: 0;">
                    
                    <button class="btn-circle btn-minus" 
                            style="width: 32px; height: 32px;"
                            onclick="gestisciCarrello(${id}, -1, ${item.prezzo}, '${nomeSafe}')">
                        <i class="fas fa-minus small"></i>
                    </button>
                    
                    <span class="text-center fw-bold" style="width: 35px; font-size: 1.1rem;">${item.qta}</span>
                    
                    <button class="btn-circle btn-plus" 
                            style="width: 32px; height: 32px;"
                            onclick="gestisciCarrello(${id}, 1, ${item.prezzo}, '${nomeSafe}')">
                        <i class="fas fa-plus small"></i>
                    </button>
                </div>

                <div style="width: 80px; flex-shrink: 0;" class="text-end">
                    <span class="fw-bold fs-5 text-price" style="color: var(--primary);">${parziale}€</span>
                </div>
            </div>`;
    }
    container.innerHTML = html + '</div>';
    totaleSpan.innerText = Math.max(0, totaleSoldi).toFixed(2);
}

/**
 * Svuota il carrello senza ricaricare la pagina
 */
function resettaOrdineDopoInvio() {
    carrello = {};
    totaleSoldi = 0;
    totalePezzi = 0;
    
    document.getElementById('soldi-header').innerText = '0.00';
    document.getElementById('pezzi-header').innerText = '0';
    
    // Resetta tutti gli input numerici nella pagina principale
    document.querySelectorAll('input[id^="q-"]').forEach(input => input.value = 0);
    
    // Disabilita il tasto invio
    const btnInvia = document.getElementById('btn-invia-ordine');
    if(btnInvia) {
        btnInvia.setAttribute('disabled', 'true');
        btnInvia.classList.replace('btn-dark', 'btn-secondary');
    }
}

/**
 * -------------------------------------------------------
 * GESTIONE EVENTI (PONTE TRA CARRELLO E CONFERMA)
 * -------------------------------------------------------
 */

// 1. Quando clicco "INVIA ORDINE" nel carrello -> Apre Modale Conferma
const btnInviaOrdine = document.getElementById('btn-invia-ordine');
if (btnInviaOrdine) {
    btnInviaOrdine.addEventListener('click', function() {
        // Chiudi carrello
        const modalCarrelloEl = document.getElementById('modalCarrello');
        const modalCarrello = bootstrap.Modal.getInstance(modalCarrelloEl);
        if(modalCarrello) modalCarrello.hide();

        // Apri conferma
        const modalConfermaEl = document.getElementById('modalConfermaOrdine');
        const modalConferma = new bootstrap.Modal(modalConfermaEl);
        modalConferma.show();
    });
}

// 2. Quando clicco "SÌ, ORDINA!" nella conferma -> Fa la chiamata API
const btnConfirmSend = document.getElementById('confirm-send-btn');
if (btnConfirmSend) {
    btnConfirmSend.onclick = function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Invio...';

        const listaProdotti = Object.values(carrello).map(item => ({ id: item.id, qta: item.qta }));

        fetch('../api/invia_ordine.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prodotti: listaProdotti })
        })
        .then(res => res.text()) // O res.json() se il tuo PHP risponde JSON
        .then(text => {
            console.log("Risposta server:", text);

            // Nasconde il modale di conferma
            const modalConfermaEl = document.getElementById('modalConfermaOrdine');
            const modalConferma = bootstrap.Modal.getInstance(modalConfermaEl);
            if(modalConferma) modalConferma.hide();
            
            // Mostra il successo
            const modalSuccesso = new bootstrap.Modal(document.getElementById('modalSuccesso'));
            modalSuccesso.show();
            
            // Svuota tutto UI e Logica
            resettaOrdineDopoInvio();
            
            // Chiude il modale di successo dopo 2.5 secondi
            setTimeout(() => {
                modalSuccesso.hide();
                btn.disabled = false;
                btn.innerHTML = 'SÌ, ORDINA!';
            }, 2500);
        })
        .catch(err => {
            console.error("Errore invio:", err);
            // In caso di errore, chiudi modale e resetta bottone
            const modalConfermaEl = document.getElementById('modalConfermaOrdine');
            const modalConferma = bootstrap.Modal.getInstance(modalConfermaEl);
            if(modalConferma) modalConferma.hide();
            
            btn.disabled = false;
            btn.innerHTML = 'SÌ, ORDINA!';
            alert("Errore di connessione. Riprova.");
        });
    };
}

/**
 * GESTIONE ZOOM PRODOTTO
 */
function apriZoom(card) {
    const d = card.dataset;
    document.getElementById('zoom-nome').innerText = d.nome;
    document.getElementById('zoom-desc').innerText = d.desc;
    document.getElementById('zoom-prezzo-unitario').innerText = d.prezzo;
    document.getElementById('zoom-img').src = d.img;
    
    const divAlg = document.getElementById('zoom-allergeni');
    divAlg.innerHTML = d.allergeni ? d.allergeni.split(',').map(a => `<span class="badge-alg">${a.trim()}</span>`).join('') : '<small>Nessuno</small>';
    
    // Reset stato zoom
    zoomState = { id: d.id, nome: d.nome, prezzo: parseFloat(d.prezzo), qtyAttuale: 1 };
    refreshZoomUI();
    
    new bootstrap.Modal(document.getElementById('modalZoom')).show();
}

function updateZoomQty(delta) {
    zoomState.qtyAttuale = Math.max(1, zoomState.qtyAttuale + delta);
    refreshZoomUI();
}

function refreshZoomUI() {
    document.getElementById('zoom-qty-display').innerText = zoomState.qtyAttuale;
    
    // Calcolo totale parziale nel bottone
    let tot = (zoomState.qtyAttuale * zoomState.prezzo).toFixed(2);
    document.getElementById('zoom-btn-totale').innerText = tot + '€';
}

function confermaZoom() {
    gestisciCarrello(zoomState.id, zoomState.qtyAttuale, zoomState.prezzo, zoomState.nome);
    
    // Feedback visivo (Toast)
    const toastEl = document.getElementById('liveToast');
    document.getElementById('toast-msg').innerText = `${zoomState.nome} aggiunto!`;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Chiudi modale
    const modalZoom = bootstrap.Modal.getInstance(document.getElementById('modalZoom'));
    modalZoom.hide();
}