/**
 * =========================================
 * FILE: js/tavolo.js
 * =========================================
 * Logica completa lato cliente per la dashboard del tavolo (cliente).
 * 
 * Funzionalità gestite:
 * - Carrello: aggiunta/rimozione prodotti, aggiornamento quantità e totali
 * - Filtri: per categoria (sidebar), per allergeni (modale), per ricerca testuale
 * - Zoom prodotto: modale dettaglio con quantità, note e aggiunta al carrello
 * - Storico ordini: visualizzazione degli ordini inviati con stato e dettagli
 * - Invio ordine: conferma, chiamata API e animazione successo
 * - Tema: toggle dark/light mode con salvataggio in localStorage
 */

// =============================================
// STATO GLOBALE DELL'APPLICAZIONE
// =============================================
// Oggetto carrello: { id_prodotto: { id, nome, qta, prezzo, note } }
let carrello = {};
// Totale in euro e numero totale di pezzi nel carrello
let totaleSoldi = 0;
let totalePezzi = 0;
// Stato del modale zoom (dettaglio prodotto)
let zoomState = { id: null, nome: '', prezzo: 0, qtyAttuale: 1, note: '' };
// Filtri attivi: categoria selezionata e allergeni da escludere
let filtri = { categoria: 'all', allergeni: [] };

// =============================================
// GESTIONE TEMA (DARK / LIGHT MODE)
// =============================================

/**
 * Alterna tra tema chiaro e scuro.
 * Salva la preferenza nel localStorage del browser.
 */
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';
    const nuovoTema = isDark ? 'light' : 'dark';

    // Applica il nuovo tema al body
    body.setAttribute('data-theme', nuovoTema);
    // Aggiorna l'icona: luna (chiaro) ↔ sole (scuro)
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    // Salva la preferenza per le visite future
    localStorage.setItem('theme', nuovoTema);
}

// =============================================
// GESTIONE FILTRI E RICERCA
// =============================================

/**
 * Filtra i prodotti per categoria.
 * Viene chiamata quando l'utente clicca su una categoria nella sidebar o nella barra mobile.
 * @param {string|number} idCat - ID della categoria o 'all' per mostrare tutto
 * @param {HTMLElement} elemento - Il bottone cliccato (per evidenziarlo come attivo)
 */
function filtraCategoria(idCat, elemento) {
    // Rimuove la classe 'active' da tutti i bottoni categoria (sidebar + mobile)
    document.querySelectorAll('.btn-categoria').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.mobile-cat-btn').forEach(el => el.classList.remove('active'));
    // Aggiunge 'active' al bottone cliccato
    if (elemento) elemento.classList.add('active');

    // Aggiorna il filtro e ri-renderizza i prodotti
    filtri.categoria = idCat;
    renderProdotti();
}

/**
 * Applica i filtri allergeni selezionati nel modale "Filtri".
 * Raccoglie i valori delle checkbox selezionate e nasconde i piatti che contengono quegli allergeni.
 */
function applicaFiltriAllergeni() {
    filtri.allergeni = [];
    document.querySelectorAll('#modalFiltri input[type="checkbox"]:checked').forEach(cb => {
        filtri.allergeni.push(cb.value);
    });
    renderProdotti();
}

/**
 * Resetta tutti i filtri allergeni (deseleziona tutte le checkbox).
 */
function resettaFiltriAllergeni() {
    document.querySelectorAll('#modalFiltri input[type="checkbox"]').forEach(cb => cb.checked = false);
    filtri.allergeni = [];
    renderProdotti();
}

/**
 * Renderizza i prodotti visibili applicando tutti i filtri attivi:
 * 1. Categoria (sidebar)
 * 2. Allergeni (modale filtri)
 * 3. Ricerca testuale (barra di ricerca)
 * 
 * Ogni prodotto viene mostrato o nascosto in base ai filtri combinati.
 */
function renderProdotti() {
    // Recupera il testo di ricerca dalla barra
    const searchText = (document.getElementById('search-bar')?.value || '').toLowerCase().trim();

    // Itera su tutti i prodotti nella pagina
    document.querySelectorAll('.item-prodotto').forEach(piatto => {
        const catPiatto = piatto.getAttribute('data-cat');
        const card = piatto.querySelector('.card-prodotto');
        const allergeniRaw = card ? (card.getAttribute('data-allergeni') || '') : '';
        const allergeniPiatto = allergeniRaw.toLowerCase().split(',').map(s => s.trim());
        const nomePiatto = card ? (card.getAttribute('data-nome') || '').toLowerCase() : '';

        let mostra = true;

        // 1. Filtro per Categoria
        if (filtri.categoria !== 'all' && catPiatto != filtri.categoria) {
            mostra = false;
        }

        // 2. Filtro per Allergeni (esclusione: se il piatto contiene un allergene escluso, nascondilo)
        if (mostra && filtri.allergeni.length > 0) {
            const haAllergeneEscluso = filtri.allergeni.some(escluso => allergeniPiatto.includes(escluso.toLowerCase()));
            if (haAllergeneEscluso) mostra = false;
        }

        // 3. Filtro per Ricerca Testuale (il nome del piatto deve contenere il testo cercato)
        if (mostra && searchText && !nomePiatto.includes(searchText)) {
            mostra = false;
        }

        // Mostra o nascondi il prodotto
        piatto.style.display = mostra ? 'block' : 'none';
    });
}

// =============================================
// GESTIONE CARRELLO
// =============================================

/**
 * Funzione principale per gestire il carrello (aggiunta/rimozione prodotti).
 * Aggiorna lo stato locale del carrello, i contatori nell'header,
 * e la UI delle card e del modale carrello.
 * 
 * @param {number} id - ID del prodotto
 * @param {number} delta - Variazione quantità (+1 per aggiungere, -1 per rimuovere)
 * @param {number} prezzo - Prezzo unitario del prodotto
 * @param {string} nome - Nome del prodotto
 * @param {string|null} note - Note per la cucina (null = non modificare le note esistenti)
 */
function gestisciCarrello(id, delta, prezzo, nome, note = null) {
    // Cerca l'elemento che mostra la quantità nella card del prodotto
    const input = document.getElementById('q-' + id);
    if (!input) return; // Sicurezza: se l'elemento non esiste, esci

    // Calcola la nuova quantità
    let valAttuale = parseInt(input.innerText) || 0;
    let valNuovo = valAttuale + delta;

    // Non permettere quantità negative
    if (valNuovo >= 0) {
        // Aggiorna il display della quantità nella card
        input.innerText = valNuovo;
        // Aggiorna i totali globali
        totaleSoldi += (delta * prezzo);
        totalePezzi += delta;

        // Aggiorna i contatori nell'header (Totale € e badge Carrello)
        const soldiHeader = document.getElementById('soldi-header');
        const pezziHeader = document.getElementById('pezzi-header');
        if (soldiHeader) soldiHeader.innerText = Math.max(0, totaleSoldi).toFixed(2);
        if (pezziHeader) pezziHeader.innerText = Math.max(0, totalePezzi);

        // Aggiorna l'oggetto carrello in memoria
        if (!carrello[id]) carrello[id] = { id: id, nome: nome, qta: 0, prezzo: prezzo, note: '' };

        carrello[id].qta = valNuovo;
        // Aggiorna le note solo se vengono passate esplicitamente
        if (note !== null) {
            carrello[id].note = note;
        }

        // Aggiorna il numeretto sulla card del prodotto (tra i bottoni − e +)
        updateCardQtyUI(id, valNuovo);

        // Se la quantità è 0, rimuovi il prodotto dal carrello
        if (carrello[id].qta === 0) delete carrello[id];

        // Gestione stato bottone "INVIA ORDINE" (abilitato/disabilitato)
        const btnInvia = document.getElementById('btn-invia-ordine');
        if (btnInvia) {
            if (totalePezzi > 0) {
                btnInvia.removeAttribute('disabled');
                btnInvia.classList.replace('btn-secondary', 'btn-dark');
            } else {
                btnInvia.setAttribute('disabled', 'true');
                btnInvia.classList.replace('btn-dark', 'btn-secondary');
            }
        }

        // Se il modale del carrello è aperto, aggiorna la lista in tempo reale
        const modalCarrello = document.getElementById('modalCarrello');
        if (modalCarrello && modalCarrello.classList.contains('show')) {
            aggiornaModale();
        }
    }
}

/**
 * Renderizza l'HTML dentro il modale carrello.
 * Mostra la lista dei prodotti con quantità, prezzo unitario e subtotale.
 * Se il carrello è vuoto, mostra un messaggio di stato vuoto.
 */
function aggiornaModale() {
    const container = document.getElementById('corpo-carrello');
    const totaleSpan = document.getElementById('totale-modale');

    // Caso: carrello vuoto
    if (Object.keys(carrello).length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                <div class="display-1 mb-3" style="opacity:0.3">🍽️</div>
                <h5 class="fw-bold text-muted">Il carrello è vuoto</h5>
            </div>`;
        totaleSpan.innerText = '0.00';
        return;
    }

    // Costruisci l'HTML della lista prodotti
    let html = '<div class="list-group list-group-flush w-100 px-3 py-2">';
    for (const [id, item] of Object.entries(carrello)) {
        let parziale = (item.qta * item.prezzo).toFixed(2); // Subtotale per riga
        let nomeSafe = item.nome.replace(/'/g, "\\'"); // Escape per onclick
        // Se ci sono note, mostra un'icona con il testo
        let noteHtml = item.note ? `<div class="small text-muted fst-italic"><i class="fas fa-comment-alt me-1"></i>${item.note}</div>` : '';

        html += `
            <div class="cart-item list-group-item d-flex align-items-center border-0 mb-3 px-0" style="background: transparent;">
                
                <div style="flex: 1; min-width: 0;">
                    <h5 class="m-0 fw-bold text-truncate">${item.nome}</h5>
                    ${noteHtml}
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
 * Svuota il carrello dopo che l'ordine è stato inviato con successo.
 * Resetta tutte le variabili globali, i contatori e i display delle quantità.
 */
function resettaOrdineDopoInvio() {
    // Reset dello stato globale
    carrello = {};
    totaleSoldi = 0;
    totalePezzi = 0;

    // Reset dei contatori nell'header
    document.getElementById('soldi-header').innerText = '0.00';
    document.getElementById('pezzi-header').innerText = '0';

    // Resetta tutte le quantità visualizzate nelle card dei prodotti
    document.querySelectorAll('[id^="q-"]').forEach(el => el.innerText = '0');

    // Disabilita il tasto "INVIA ORDINE"
    const btnInvia = document.getElementById('btn-invia-ordine');
    if (btnInvia) {
        btnInvia.setAttribute('disabled', 'true');
        btnInvia.classList.replace('btn-dark', 'btn-secondary');
    }
}

// =============================================
// GESTIONE INVIO ORDINE
// =============================================

/**
 * Passo 1: Click su "INVIA ORDINE" nel carrello → Apre il modale di conferma.
 * Il modale chiede all'utente se è sicuro di voler inviare l'ordine.
 */
const btnInviaOrdine = document.getElementById('btn-invia-ordine');
if (btnInviaOrdine) {
    btnInviaOrdine.addEventListener('click', function () {
        // Chiudi il modale carrello
        const modalCarrelloEl = document.getElementById('modalCarrello');
        const modalCarrello = bootstrap.Modal.getInstance(modalCarrelloEl);
        if (modalCarrello) modalCarrello.hide();

        // Apri il modale di conferma ("Sei pronto? Sì, Ordina!")
        const modalConfermaEl = document.getElementById('modalConfermaOrdine');
        const modalConferma = new bootstrap.Modal(modalConfermaEl);
        modalConferma.show();
    });
}

/**
 * Passo 2: Click su "SÌ, ORDINA!" nel modale di conferma → Invia l'ordine alla cucina.
 * Crea un JSON con la lista dei prodotti e lo invia all'API invia_ordine.php.
 */
const btnConfirmSend = document.getElementById('confirm-send-btn');
if (btnConfirmSend) {
    btnConfirmSend.onclick = function () {
        const btn = this;
        // Disabilita il bottone e mostra un loader per evitare doppi click
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Invio...';

        // Prepara la lista dei prodotti dal carrello
        // Formato: [{ id: 5, qta: 2, note: "Senza cipolla" }, ...]
        const listaProdotti = Object.values(carrello).map(item => ({
            id: item.id,
            qta: item.qta,
            note: item.note
        }));

        // Invia l'ordine al server tramite fetch POST
        fetch('../api/tavolo/invia_ordine.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prodotti: listaProdotti })
        })
            .then(res => res.text())
            .then(text => {
                console.log("Risposta server:", text);

                // Nasconde il modale di conferma
                const modalConfermaEl = document.getElementById('modalConfermaOrdine');
                const modalConferma = bootstrap.Modal.getInstance(modalConfermaEl);
                if (modalConferma) modalConferma.hide();

                // Mostra l'animazione di successo (check verde)
                const modalSuccesso = new bootstrap.Modal(document.getElementById('modalSuccesso'));
                modalSuccesso.show();

                // Svuota il carrello (UI e logica)
                resettaOrdineDopoInvio();

                // Chiude automaticamente il modale di successo dopo 2 secondi
                setTimeout(() => {
                    modalSuccesso.hide();
                    btn.disabled = false;
                    btn.innerHTML = 'SÌ, ORDINA!';
                }, 2000);
            })
            .catch(err => {
                console.error("Errore invio:", err);
                // In caso di errore di rete, chiudi modale e avvisa l'utente
                const modalConfermaEl = document.getElementById('modalConfermaOrdine');
                const modalConferma = bootstrap.Modal.getInstance(modalConfermaEl);
                if (modalConferma) modalConferma.hide();

                btn.disabled = false;
                btn.innerHTML = 'SÌ, ORDINA!';
                alert("Errore di connessione. Riprova.");
            });
    };
}

// =============================================
// GESTIONE STORICO ORDINI
// =============================================

/**
 * Apre il modale "Ordini" mostrando lo storico degli ordini inviati dal tavolo.
 * Chiama l'API leggi_ordini_tavolo.php e renderizza ogni ordine come una card
 * con stato (badge colorato), ora, lista piatti e totale.
 */
function apriStorico() {
    const container = document.getElementById('corpo-ordini');
    const totaleSpan = document.getElementById('totale-storico');
    // Mostra un loader mentre carica i dati
    container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';

    fetch('../api/tavolo/leggi_ordini_tavolo.php')
        .then(res => res.json())
        .then(ordini => {
            // Caso: nessun ordine inviato
            if (!ordini || ordini.length === 0) {
                container.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="display-1 mb-3" style="opacity:0.3">📋</div>
                        <h5 class="fw-bold text-muted">Nessun ordine inviato</h5>
                        <p class="text-muted small">I tuoi ordini appariranno qui</p>
                    </div>`;
                totaleSpan.innerText = '0.00';
                new bootstrap.Modal(document.getElementById('modalOrdini')).show();
                return;
            }

            // Mappa degli stati: colore badge e icona in base allo stato dell'ordine
            const statusMap = {
                'in_attesa': { label: 'In Attesa', class: 'bg-warning text-dark', icon: 'fa-clock' },
                'in_preparazione': { label: 'In Preparazione', class: 'bg-primary text-white', icon: 'fa-fire-burner' },
                'pronto': { label: 'Pronto', class: 'bg-success text-white', icon: 'fa-check-circle' }
            };

            let html = '';
            let grandTotal = 0; // Totale complessivo di tutti gli ordini

            // Genera l'HTML per ogni ordine
            ordini.forEach((ordine, idx) => {
                const st = statusMap[ordine.stato] || statusMap['in_attesa'];
                grandTotal += parseFloat(ordine.totale);

                // Genera la lista dei piatti per questo ordine
                let piattiHtml = ordine.piatti.map(p => {
                    let noteHtml = p.note ? `<div class="small text-muted fst-italic"><i class="fas fa-comment-alt me-1"></i>${p.note}</div>` : '';
                    return `
                        <div class="d-flex justify-content-between align-items-start py-2 ${p !== ordine.piatti[ordine.piatti.length - 1] ? 'border-bottom' : ''}">
                            <div style="flex:1; min-width:0;">
                                <span class="fw-semibold">${p.nome}</span>
                                ${noteHtml}
                            </div>
                            <div class="text-end ms-3 flex-shrink-0">
                                <span class="text-muted small">x${p.qta}</span>
                                <span class="fw-bold ms-2">${(p.qta * parseFloat(p.prezzo)).toFixed(2)}€</span>
                            </div>
                        </div>`;
                }).join('');

                // Card dell'ordine con header (ora + badge stato), lista piatti e totale
                html += `
                    <div class="ordine-card mb-3 p-3 rounded-4 border" style="background: var(--card-bg, #fff);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-muted small"><i class="fas fa-clock me-1"></i>${ordine.ora}</span>
                                <span class="text-muted small">${ordine.data}</span>
                            </div>
                            <span class="badge ${st.class} rounded-pill px-3 py-2">
                                <i class="fas ${st.icon} me-1"></i>${st.label}
                            </span>
                        </div>
                        <div class="px-1">
                            ${piattiHtml}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="text-muted small fw-bold">Ordine #${ordine.id_ordine}</span>
                            <span class="fw-bold fs-5" style="color: var(--primary);">${ordine.totale}€</span>
                        </div>
                    </div>`;
            });

            // Inserisce l'HTML nel modale e aggiorna il totale complessivo
            container.innerHTML = html;
            totaleSpan.innerText = grandTotal.toFixed(2);
            new bootstrap.Modal(document.getElementById('modalOrdini')).show();
        })
        .catch(err => {
            // Gestione errore di rete/server
            console.error('Errore caricamento ordini:', err);
            container.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="display-1 mb-3" style="opacity:0.3">⚠️</div>
                    <h5 class="fw-bold text-muted">Errore di caricamento</h5>
                    <p class="text-muted small">Riprova tra qualche istante</p>
                </div>`;
            new bootstrap.Modal(document.getElementById('modalOrdini')).show();
        });
}

// =============================================
// GESTIONE ZOOM PRODOTTO (MODALE DETTAGLIO)
// =============================================

/**
 * Apre il modale di dettaglio di un piatto (zoom).
 * Mostra immagine, descrizione, allergeni, note e selettore quantità.
 * Non si apre se il click proviene dai bottoni +/- della card.
 * 
 * @param {Event} e - Evento click (usato per verificare l'origine del click)
 * @param {HTMLElement} card - L'elemento card cliccato (contiene i data-attributes)
 */
function apriZoom(e, card) {
    // Non aprire il modale se il click proviene dai bottoni +/- della card
    if (e && e.target && e.target.closest('.btn-card-qty, .qty-capsule-card')) return;

    // Recupera i dati del piatto dai data-attributes della card
    const d = card.dataset;
    document.getElementById('zoom-nome').innerText = d.nome;
    document.getElementById('zoom-desc').innerText = d.desc;
    document.getElementById('zoom-prezzo-unitario').innerText = d.prezzo;
    document.getElementById('zoom-img').src = d.img;

    // Renderizza i badge degli allergeni
    const divAlg = document.getElementById('zoom-allergeni');
    divAlg.innerHTML = d.allergeni ? d.allergeni.split(',').map(a => `<span class="badge-alg">${a.trim()}</span>`).join('') : '<small>Nessuno</small>';

    // Pre-popola le note se il prodotto è già nel carrello
    let currentNote = '';
    if (carrello[d.id]) {
        currentNote = carrello[d.id].note || '';
    }

    document.getElementById('zoom-note').value = currentNote;

    // Inizializza lo stato dello zoom con quantità 1
    zoomState = { id: d.id, nome: d.nome, prezzo: parseFloat(d.prezzo), qtyAttuale: 1, note: currentNote };
    refreshZoomUI();

    // Apri il modale
    new bootstrap.Modal(document.getElementById('modalZoom')).show();
}

/**
 * Aggiorna la quantità nel modale zoom (bottoni +/−).
 * La quantità minima è sempre 1.
 * @param {number} delta - +1 o -1
 */
function updateZoomQty(delta) {
    zoomState.qtyAttuale = Math.max(1, zoomState.qtyAttuale + delta);
    refreshZoomUI();
}

/**
 * Aggiorna l'interfaccia del modale zoom:
 * - Il numero di quantità visualizzato
 * - Il totale parziale nel bottone "Aggiungi al carrello"
 */
function refreshZoomUI() {
    document.getElementById('zoom-qty-display').innerText = zoomState.qtyAttuale;

    // Calcolo totale parziale (quantità × prezzo unitario)
    let tot = (zoomState.qtyAttuale * zoomState.prezzo).toFixed(2);
    document.getElementById('zoom-btn-totale').innerText = tot + '€';
}

/**
 * Conferma l'aggiunta dal modale zoom.
 * Aggiunge il prodotto al carrello con le note specificate,
 * mostra un toast di conferma e chiude il modale.
 */
function confermaZoom() {
    // Recupera le note dal textarea
    const note = document.getElementById('zoom-note').value;
    // Aggiunge al carrello con la quantità selezionata
    gestisciCarrello(zoomState.id, zoomState.qtyAttuale, zoomState.prezzo, zoomState.nome, note);

    // Mostra il toast di conferma ("Carbonara aggiunto!")
    const toastEl = document.getElementById('liveToast');
    document.getElementById('toast-msg').innerText = `${zoomState.nome} aggiunto!`;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    // Chiudi il modale zoom
    const modalZoom = bootstrap.Modal.getInstance(document.getElementById('modalZoom'));
    modalZoom.hide();
}

// =============================================
// GESTIONE BOTTONI CARD (+/−)
// =============================================

/**
 * Gestisce il click sui bottoni +/− direttamente sulla card del prodotto.
 * Blocca la propagazione dell'evento per non aprire il modale zoom.
 * 
 * @param {Event} event - Evento click
 * @param {number} id - ID del prodotto
 * @param {number} delta - +1 o -1
 * @param {number} prezzo - Prezzo unitario
 * @param {string} nome - Nome del prodotto
 */
function btnCardQty(event, id, delta, prezzo, nome) {
    event.stopPropagation(); // Evita che il click apra il modale zoom
    gestisciCarrello(id, delta, prezzo, nome, null);
}

/**
 * Aggiorna il display della quantità sulla card del prodotto.
 * @param {number} id - ID del prodotto
 * @param {number} val - Nuova quantità da visualizzare
 */
function updateCardQtyUI(id, val) {
    const el = document.getElementById('q-' + id);
    if (el) el.innerText = val;
}