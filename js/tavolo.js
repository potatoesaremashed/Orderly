// File JavaScript principale dedicato all'interfaccia utente Table / Cliente finale navigante nel menù.

// Variabili di Stato Globali che persistono a schermo fintanto che l'utente non aggiorna
let carrello = {};           // Dizionario strutturato degli elementi da spedire
let filtriAllergeni = [];    // Vettore per inibire i piatti intolleranti
let categoriaAttiva = 'all'; // Segnaposto del sorting attuale (id es. 'all' = Misto)

document.addEventListener('DOMContentLoaded', function () {
    // Operazioni di inizializzazione per il Tema
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('[id="theme-icon"]').forEach(icon => {
            icon.classList.replace('fa-moon', 'fa-sun');
        });
    }

    // Carica eventuali carrelli parcheggiati dal backend se presente un abort precedente
    sincronizzaCarrello();

    // Inizia ad interrogare le variabili per ripulire lo schermo
    renderProdotti();

    // Routine di sicurezza: ogni 5 secondi contatta il server per verificare se il proprietario 
    // del ristorante ha forzatamente resettato il tavolo mentre eravamo sbadatamente collegati ad esso
    setInterval(verificaSessione, 5000);

    // Gestori eventi base
    document.getElementById('btn-invia-ordine').addEventListener('click', () => {
        new bootstrap.Modal(document.getElementById('modalConfermaOrdine')).show();
    });
    document.getElementById('confirm-send-btn').addEventListener('click', inviaOrdine);
});

// --- Sincronizzazione del Carrello ---
// Aggiorna la memoria frontend (JS) prelevando il carrello fantasma temporaneo dal DB
function sincronizzaCarrello() {
    fetch('../api/tavolo/get_carrello.php')
        .then(r => r.json())
        .then(data => {
            carrello = {};
            // Popolamento del dictionary per facilitare gli accessi e l'incremento di qta
            data.forEach(item => {
                carrello[item.id_alimento] = {
                    nome: item.nome_piatto,
                    qta: parseInt(item.quantita),
                    prezzo: parseFloat(item.prezzo)
                };
            });
            aggiornaUI();
        });
}

// --- Motore di Rendering Prodotti & Motore Filitri ---
// Questa è la bestia nera della schermata: si accoppia alle funzioni sottostanti per mostrare e nascondere al volo (in JS) 
// le cards piatti se incappano in un limite introdotto (ricerca semantica testuale o allergia).
function renderProdotti() {
    const search = document.getElementById('search-bar').value.toLowerCase();

    // Itera tutti i container
    document.querySelectorAll('.item-prodotto').forEach(item => {
        const card = item.querySelector('.card-prodotto');
        const nome = card.dataset.nome.toLowerCase();
        const desc = card.dataset.desc.toLowerCase();
        const cat = card.dataset.cat;

        // Traccia e scompone gli allergeni
        const allergeniPiatto = card.dataset.allergeni.split(',').map(a => a.trim().toLowerCase());

        // Verifica dei tre test di sopravvivenza visiva (Search test, Categoria match test, Safety test)
        const matchSearch = nome.includes(search) || desc.includes(search);
        const matchCat = categoriaAttiva === 'all' || cat == categoriaAttiva;
        const matchAllergeni = filtriAllergeni.length === 0 || !filtriAllergeni.some(f => allergeniPiatto.includes(f.toLowerCase()));

        // Mostra il piatto, solo se è immune a tutti e 3 i check simultaneamente! 
        item.style.display = (matchSearch && matchCat && matchAllergeni) ? '' : 'none';
    });
}

// Evento sparato cliccando sulle categorie nel menù laterale Desktop/Mobile 
function filtraCategoria(catId, btn) {
    categoriaAttiva = catId;
    document.querySelectorAll('.btn-categoria, .mobile-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderProdotti(); // Richiama il ricalcolo 
}

// Evento sparato chiudendo con successo la modale di blocco delle intolleranze 
function applicaFiltriAllergeni() {
    filtriAllergeni = [];
    document.querySelectorAll('#lista-allergeni-filtro input[type="checkbox"]:checked').forEach(cb => {
        filtriAllergeni.push(cb.value);
    });
    renderProdotti();
}

// Azzera i veti sanitari del cliente e rilascia tutti i check
function resettaFiltriAllergeni() {
    document.querySelectorAll('#lista-allergeni-filtro input[type="checkbox"]').forEach(cb => cb.checked = false);
    filtriAllergeni = [];
    renderProdotti();
}

// --- Gestore Quantità Base ---
// Gestisce logiche di plus e minimus eseguite direttamente sulle minuscole card di anteprima, prima del carrello finale. 
function btnCardQty(event, id, delta, prezzo, nome) {
    event.stopPropagation(); // Ferma la propagazione in modo da ignorare il click zoom sulla carta

    // Auto-genera il segnaposto vuoto nel dizionario carrello se questo piatto è un esordiente 
    if (!carrello[id]) carrello[id] = { nome, qta: 0, prezzo };

    // Nessun trucco per scendere sotto 0
    carrello[id].qta = Math.max(0, carrello[id].qta + delta);

    // Contatta l'API appropriata per persistere il record di bozza nel DB anche prima di ordinare 
    const endpoint = delta > 0 ? 'aggiungi_al_carrello.php' : 'rimuovi_dal_carrello.php';
    const fd = new FormData();
    fd.append('id_alimento', id);
    if (delta > 0) fd.append('quantita', 1);

    fetch('../api/tavolo/' + endpoint, { method: 'POST', body: fd });

    // Pulisce l'indice in ram che ormai non fa più trucco se l'utente ha svuotato il carrello rimettendo a zero un item
    if (carrello[id].qta <= 0) delete carrello[id];
    aggiornaUI();
}

// --- Finestra di Zoom e Approfondimento (Modale) ---
let zoomState = { id: 0, prezzo: 0, qta: 1, nome: '', note: '' };

// Evento di apertura (Quando il recensore preme la card vuota grande attratto dall'immagine) 
function apriZoom(event, card) {
    if (event.target.closest('.btn-card-qty')) return; // Esclude il click involontario sul form QTA

    // Inizializza temporaneamente le variabili per l'area di zoom prelevandole dai custom data html integrati
    zoomState = {
        id: parseInt(card.dataset.id),
        prezzo: parseFloat(card.dataset.prezzo),
        qta: 1,
        nome: card.dataset.nome,
        note: ''
    };

    // Sovra-scrive i vari header/bottoni interni della modale HTML fissa e vuota di Bootstrap
    document.getElementById('zoom-img').src = card.dataset.img;
    document.getElementById('zoom-nome').textContent = card.dataset.nome;
    document.getElementById('zoom-desc').textContent = card.dataset.desc;
    document.getElementById('zoom-prezzo-unitario').textContent = card.dataset.prezzo;
    document.getElementById('zoom-note').value = '';

    // Renderizza etichette per gli allergeni in modo pulito e sicuro
    const allergeni = card.dataset.allergeni.split(',').filter(a => a.trim());
    document.getElementById('zoom-allergeni').innerHTML = allergeni.length
        ? allergeni.map(a => `<span class="badge-alg">${a.trim()}</span>`).join('')
        : '<small class="text-muted">Nessun allergene dichiarato</small>';

    aggiornaZoomUI();
    new bootstrap.Modal(document.getElementById('modalZoom')).show();
}

// Ritocca le cifre interne in sovrimpressione dentro il popup (Es: passo a 3 pizze, aumenta anche il costo unitario) 
function updateZoomQty(delta) {
    zoomState.qta = Math.max(1, zoomState.qta + delta);
    aggiornaZoomUI();
}

// Dispiegamento fisico ai div HTML delle modifiche
function aggiornaZoomUI() {
    document.getElementById('zoom-qty-display').textContent = zoomState.qta;
    document.getElementById('zoom-btn-totale').textContent = (zoomState.prezzo * zoomState.qta).toFixed(2) + '€';
}

// Funzione finale scagionata dal premere il bottone gigante "Aggiungi" nella visuale Focus/Zoom 
function confermaZoom() {
    // Si accoda al dizionario generale come da copione 
    if (!carrello[zoomState.id]) carrello[zoomState.id] = { nome: zoomState.nome, qta: 0, prezzo: zoomState.prezzo };
    carrello[zoomState.id].qta += zoomState.qta;

    const fd = new FormData();
    fd.append('id_alimento', zoomState.id);
    fd.append('quantita', zoomState.qta);
    // E spedisce i parziali al buffer DB invisibile 
    fetch('../api/tavolo/aggiungi_al_carrello.php', { method: 'POST', body: fd });

    aggiornaUI();
    bootstrap.Modal.getInstance(document.getElementById('modalZoom')).hide();
    mostraToast(`${zoomState.nome} aggiunto!`); // Conferma effimera non invasiva 
}

// --- Funzionalità Aggiornamento Globale Interattivo ---
// Richiamata quasi ovunque: scansiona il DB carrello aggiornato ed effettua calcoli per l'estetica HUD frontale in realtime. 
function aggiornaUI() {
    let totale = 0, pezzi = 0;

    // Calcola il preventivo spesa 
    for (const id in carrello) {
        const item = carrello[id];
        totale += item.qta * item.prezzo;
        pezzi += item.qta;

        // Rintraccia il testo HTML nella Card di base fuori dal carrello per renderlo simmetrico e consono a quel che hai comprato
        const el = document.getElementById('q-' + id);
        if (el) el.textContent = item.qta;
    }

    // Passata per pulizia su form item esterni non inerenti o null-ificati:
    document.querySelectorAll('[id^="q-"]').forEach(el => {
        const id = el.id.replace('q-', '');
        if (!carrello[id]) el.textContent = '0';
    });

    document.getElementById('soldi-header').textContent = totale.toFixed(2);
    document.getElementById('pezzi-header').textContent = pezzi;
}

// --- Modalità Carrello Virtuale Prima Estrazione ---
// Confeziona la tabella riassuntiva HTML in cui sono rintracciabili le opzioni parziali pre-invio
function aggiornaModale() {
    const body = document.getElementById('corpo-carrello');
    const keys = Object.keys(carrello).filter(id => carrello[id].qta > 0);
    let totale = 0;

    // Caso vuoto 
    if (!keys.length) {
        body.innerHTML = `<div class="text-center py-5 text-muted">
            <i class="fas fa-shopping-bag fa-3x mb-3" style="opacity:.3"></i>
            <h5>Il carrello è vuoto</h5><p class="small">Aggiungi piatti per iniziare</p></div>`;
        document.getElementById('totale-modale').textContent = '0.00';
        document.getElementById('btn-invia-ordine').disabled = true;
        return;
    }

    // Costruisce la UI della lista riassuntiva dei piatti inseriti con relativi sub-totals (quantita x costo singolo piatto)
    body.innerHTML = '<ul class="list-group list-group-flush px-3">' + keys.map(id => {
        const item = carrello[id];
        const sub = (item.qta * item.prezzo).toFixed(2);
        totale += item.qta * item.prezzo;
        return `<li class="list-group-item d-flex justify-content-between align-items-center px-2 py-3">
            <div>
                <strong>${item.nome}</strong><br>
                <small class="text-muted">${item.prezzo.toFixed(2)}€ cad.</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="qty-capsule" style="width:120px;">
                    <button class="btn-circle btn-minus" onclick="modificaQtaModale(${id}, -1)"><i class="fas fa-minus"></i></button>
                    <span class="qty-input">${item.qta}</span>
                    <button class="btn-circle btn-plus" onclick="modificaQtaModale(${id}, 1)"><i class="fas fa-plus"></i></button>
                </div>
                <strong class="text-price">${sub}€</strong>
            </div>
        </li>`;
    }).join('') + '</ul>';

    document.getElementById('totale-modale').textContent = totale.toFixed(2);
    document.getElementById('btn-invia-ordine').disabled = false; // Sblocca il pulsante di conferma definitiva se ci stiamo.
}

// Bottone Modifica per i plus in linea di Carrello che riaffettano DB
function modificaQtaModale(id, delta) {
    if (!carrello[id]) return;
    carrello[id].qta = Math.max(0, carrello[id].qta + delta);

    const endpoint = delta > 0 ? 'aggiungi_al_carrello.php' : 'rimuovi_dal_carrello.php';
    const fd = new FormData();
    fd.append('id_alimento', id);
    if (delta > 0) fd.append('quantita', 1);
    fetch('../api/tavolo/' + endpoint, { method: 'POST', body: fd });

    if (carrello[id].qta <= 0) delete carrello[id];
    aggiornaUI();
    aggiornaModale(); // Ripulisce al volo auto-sobbassando il frame 
}

// --- Nucleo Finalità di Pagamento e Invìo ---
// Prende tutte il buffer e lo scarica dentro l'endpoint invia_ordine.php formalizzando lo stack vero per la cucina
function inviaOrdine() {
    const prodotti = Object.keys(carrello).map(id => ({
        id: parseInt(id),
        qta: carrello[id].qta,
        note: ''
    })).filter(p => p.qta > 0);

    if (!prodotti.length) return; // FailSafe per evitare null bytes se hackato con inspector elemento. 

    bootstrap.Modal.getInstance(document.getElementById('modalConfermaOrdine')).hide();

    fetch('../api/tavolo/invia_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prodotti })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Svuota con successo i flag RAM post esecuzione
                carrello = {};
                aggiornaUI();
                bootstrap.Modal.getInstance(document.getElementById('modalCarrello'))?.hide();
                // Mostra il badge di benvenuta e successo verde brillante
                new bootstrap.Modal(document.getElementById('modalSuccesso')).show();
            } else {
                mostraToast(data.message || 'Errore', true);
            }
        });
}

// --- Schermata Storico ---
// Scarica gli ordini consolidati passati al macero e ormai entrati tra i ticket reali e li somma sul modal. 
function apriStorico() {
    fetch('../api/tavolo/leggi_ordini_tavolo.php')
        .then(r => r.json())
        .then(data => {
            const body = document.getElementById('corpo-ordini');
            let totaleSommato = 0;

            if (!data.length) {
                body.innerHTML = `<div class="text-center text-muted py-5">
                    <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                    <h5>Nessun ordine</h5><p class="small">Non hai ancora inviato ordini.</p></div>`;
            } else {
                // Incolla e spulcia ogni riga d'ordine (data e timestamp) e genera un mini tabulato a fisarmonica
                body.innerHTML = data.map(o => {
                    totaleSommato += parseFloat(o.totale);

                    const badgeClass = o.stato === 'in_attesa' ? 'bg-warning text-dark' : o.stato === 'in_preparazione' ? 'bg-info text-white' : 'bg-success';
                    const labels = { in_attesa: 'In attesa', in_preparazione: 'In preparazione', pronto: 'Pronto' };

                    return `<div class="border rounded-4 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge ${badgeClass} rounded-pill px-3 py-2">${labels[o.stato] || o.stato}</span>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>${o.ora} • ${o.data}</small>
                        </div>
                        ${o.piatti.map(p => `<div class="d-flex justify-content-between small py-1 border-bottom">
                            <span>${p.qta}x <strong>${p.nome}</strong></span>
                            <span class="text-muted">${p.prezzo}€</span>
                        </div>`).join('')}
                        <div class="text-end mt-2 fw-bold text-price">${o.totale}€</div>
                    </div>`;
                }).join('');
            }

            document.getElementById('totale-storico').textContent = totaleSommato.toFixed(2);
            new bootstrap.Modal(document.getElementById('modalOrdini')).show();
        });
}

// --- Funzioni Utility ---
// Notifiche verdi o rossastre che rassicurano su esito positivo via pop over asincrono UI
function mostraToast(msg, isError = false) {
    const el = document.getElementById('liveToast');
    el.className = `toast align-items-center text-white border-0 shadow-lg ${isError ? 'bg-danger' : 'bg-success'}`;
    document.getElementById('toast-msg').textContent = msg;
    new bootstrap.Toast(el, { delay: 3000 }).show();
}

// --- Session Check in Background Poller ---
// Viene lanciata ad ogni giro di lancetta o 5000 millisecondi di setInterval in alto 
// per accertarsi che il Manager Panel non abbia sloggato il terminale dal db segnandolo "Libero" e killando il device token.
function verificaSessione() {
    fetch('../api/tavolo/verifica_sessione.php')
        .then(r => r.json())
        .then(data => {
            if (!data.valida) {
                // Se non è pìù valida... Caccia l'utente alla schermata admin in index page.
                alert('La sessione è stata terminata dal gestore.');
                window.location.href = '../logout.php';
            }
        })
        .catch(() => { }); // Ignore network errors o timeouts
}