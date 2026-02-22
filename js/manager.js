/**
 * =========================================
 * FILE: js/manager.js
 * =========================================
 * Questo script gestisce la "Dashboard Manager" (Amministrazione).
 * Funge da SPA (Single Page Application) gestendo i passaggi tra:
 * 1. Gestione Tavoli (Griglia interattiva, CRUD)
 * 2. Gestione Menu (Modifica piatti e categorie)
 * 
 * JUNIOR TIP: Usiamo variabili globali come 'tavoli' per tenere in memoria
 * i dati scaricati dal server, evitando di ricaricare tutto ad ogni piccolo filtro.
 */

// =============================================
// DATI E STATO GLOBALE
// =============================================
let tavoli = []; // Array che conterrà tutti i tavoli scaricati dal DB
let filtroCorrente = 'tutti'; // Filtro UI attivo: tutti, libero, occupato, riservato

/**
 * GESTIONE TEMA (DARK / LIGHT MODE)
 */
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';

    body.setAttribute('data-theme', isDark ? 'light' : 'dark');
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

// Controllo iniziale del tema salvato
if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon')?.classList.replace('fa-moon', 'fa-sun');
}

// =============================================
// NAVIGAZIONE SIDEBAR (SPA LOGIC)
// =============================================

/**
 * Switcha la visualizzazione tra le diverse sezioni della dashboard.
 * @param {string} page - L'ID della pagina da mostrare (es. 'tavoli', 'menu')
 * @param {HTMLElement} btn - Il bottone cliccato per aggiungere la classe .active
 */
function switchPage(page, btn) {
    // Nascondiamo tutte le raggruppamenti di classe .page-section
    document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');

    // Puliamo la classe .active da tutti i controlli di navigazione
    document.querySelectorAll('.btn-sidebar, .mobile-nav-btn').forEach(b => b.classList.remove('active'));

    // Mostriamo la sezione target
    document.getElementById('page-' + page).style.display = 'block';

    // Evidenziamo il bottone attivo (sia sidebar che mobile)
    if (btn) btn.classList.add('active');

    // Sync tra sidebar Desktop e navbar Mobile
    const idx = (page === 'tavoli' ? 0 : 1);
    document.querySelectorAll('.btn-sidebar')[idx]?.classList.add('active');
    document.querySelectorAll('.mobile-nav-btn')[idx]?.classList.add('active');

    // Se entriamo in 'tavoli', aggiorniamo i dati dal server
    if (page === 'tavoli') caricaTavoli();
}

// =============================================
// GESTIONE TAVOLI (CRUD & RENDERING)
// =============================================

/**
 * Scarica la lista dei tavoli dal database tramite API.
 */
function caricaTavoli() {
    fetch('../api/manager/get_tavoli.php')
        .then(r => r.json())
        .then(data => {
            tavoli = data; // Salviamo i dati nello stato locale
            aggiornaConta(); // Aggiorna i badge numerici nell'header
            renderTavoli(); // Disegna le card nella griglia
        })
        .catch(() => {
            document.getElementById('tavoli-grid').innerHTML =
                '<div class="tavoli-empty text-danger"><i class="fas fa-exclamation-triangle"></i><h4>Errore</h4><p>Impossibile comunicare con il server</p></div>';
        });
}

/**
 * Conta quanti tavoli ci sono per ogni stato e aggiorna i contatori in UI.
 */
function aggiornaConta() {
    document.getElementById('count-tutti').textContent = tavoli.length;
    document.getElementById('count-libero').textContent = tavoli.filter(t => t.stato === 'libero').length;
    document.getElementById('count-occupato').textContent = tavoli.filter(t => t.stato === 'occupato').length;
    document.getElementById('count-riservato').textContent = tavoli.filter(t => t.stato === 'riservato').length;
}

/**
 * Disegna fisicamente le card dei tavoli nell'elemento #tavoli-grid.
 * Applica anche il filtro corrente (es. solo i Liberi).
 */
function renderTavoli() {
    const grid = document.getElementById('tavoli-grid');
    let filtered = tavoli;

    // Applichiamo il filtro se diverso da 'tutti'
    if (filtroCorrente !== 'tutti') {
        filtered = tavoli.filter(t => t.stato === filtroCorrente);
    }

    // Se non ci sono tavoli che corrispondono al filtro
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="tavoli-empty"><i class="fas fa-chair"></i><h4>Nessun tavolo</h4><p>Nessun tavolo corrisponde a questo filtro.</p></div>';
        return;
    }

    // Generiamo l'HTML per ogni card
    grid.innerHTML = filtered.map(t => {
        const stato = t.stato || 'libero';
        const iconClass = getIconForStatus(stato);
        const nextStato = getNextStato(stato);

        return `
            <div class="tavolo-card" data-id="${t.id_tavolo}">
                <div class="tavolo-card-header">
                    <div class="tavolo-icon ${stato}"><i class="fas ${iconClass}"></i></div>
                    <div class="tavolo-name">${t.nome_tavolo}</div>
                    <div class="tavolo-seats"><i class="fas fa-users"></i> ${t.posti || 4} posti</div>
                </div>
                <div class="tavolo-card-footer">
                    <!-- Il badge dello stato è cliccabile per ciclare velocemente lo stato -->
                    <div class="tavolo-status-badge badge-${stato}" 
                         onclick="cambiaStatoTavolo(${t.id_tavolo}, '${nextStato}')">
                        <span class="status-dot dot-${stato}"></span> ${stato.toUpperCase()}
                    </div>
                    <div class="tavolo-actions">
                        <button class="btn-act" onclick="apriModalModificaTavolo(${t.id_tavolo})"><i class="fas fa-pen"></i></button>
                        <button class="btn-act btn-delete-t" onclick="eliminaTavolo(${t.id_tavolo}, '${t.nome_tavolo}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>`;
    }).join('');
}

// Funzioni utility per icone e rotazione stati
function getIconForStatus(s) {
    return s === 'libero' ? 'fa-check-circle' : (s === 'occupato' ? 'fa-utensils' : 'fa-clock');
}
function getNextStato(s) {
    const cicli = { 'libero': 'occupato', 'occupato': 'riservato', 'riservato': 'libero' };
    return cicli[s] || 'libero';
}

/**
 * Filtra i tavoli in base alla selezione (Tutti, Liberi, ecc.)
 */
function filtraTavoli(filtro, btn) {
    filtroCorrente = filtro;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderTavoli();
}

/**
 * Cambia lo stato di un tavolo (es. da Libero a Occupato).
 */
function cambiaStatoTavolo(id, nuovoStato) {
    const formData = new FormData();
    formData.append('id_tavolo', id);
    formData.append('stato', nuovoStato);

    fetch('../api/manager/cambia_stato_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Aggiorniamo i dati in locale senza rifare la fetch (più veloce!)
                const t = tavoli.find(x => x.id_tavolo == id);
                if (t) t.stato = nuovoStato;
                aggiornaConta();
                renderTavoli();
                mostraToast('Tavolo aggiornato: ' + nuovoStato);
            }
        });
}

// =============================================
// MODALI AGGIUNTA / MODIFICA TAVOLI
// =============================================

function apriModalAggiungi() {
    const form = document.querySelector('#modalAggiungiTavolo form');
    if (form) form.reset();
    new bootstrap.Modal(document.getElementById('modalAggiungiTavolo')).show();
}

function aggiungiTavolo() {
    const nome = document.getElementById('nuovo_nome_tavolo').value.trim();
    const password = document.getElementById('nuovo_password_tavolo').value.trim();
    if (!nome || !password) return mostraToast('Nome e Password obbligatori', true);

    const formData = new FormData();
    formData.append('nome_tavolo', nome);
    formData.append('password', password);
    formData.append('posti', document.getElementById('nuovo_posti_tavolo').value);

    fetch('../api/manager/aggiungi_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAggiungiTavolo')).hide();
                mostraToast('Tavolo aggiunto!');
                caricaTavoli();
            } else {
                mostraToast(data.error, true);
            }
        });
}

function apriModalModificaTavolo(id) {
    const t = tavoli.find(x => x.id_tavolo == id);
    if (!t) return;

    document.getElementById('mod_id_tavolo').value = t.id_tavolo;
    document.getElementById('mod_nome_tavolo').value = t.nome_tavolo;
    document.getElementById('mod_password_tavolo').value = t.password;
    document.getElementById('mod_posti_tavolo').value = t.posti || 4;
    document.getElementById('mod_stato_tavolo').value = t.stato || 'libero';

    new bootstrap.Modal(document.getElementById('modalModificaTavolo')).show();
}

function modificaTavolo() {
    const formData = new FormData();
    formData.append('id_tavolo', document.getElementById('mod_id_tavolo').value);
    formData.append('nome_tavolo', document.getElementById('mod_nome_tavolo').value);
    formData.append('password', document.getElementById('mod_password_tavolo').value);
    formData.append('posti', document.getElementById('mod_posti_tavolo').value);
    formData.append('stato', document.getElementById('mod_stato_tavolo').value);

    fetch('../api/manager/modifica_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalModificaTavolo')).hide();
                mostraToast('Tavolo modificato!');
                caricaTavoli();
            }
        });
}

function eliminaTavolo(id, nome) {
    if (!confirm('Vuoi davvero eliminare il tavolo ' + nome + '?')) return;

    const formData = new FormData();
    formData.append('id_tavolo', id);

    fetch('../api/manager/elimina_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostraToast('Tavolo rimosso');
                caricaTavoli();
            }
        });
}

// =============================================
// UTILITY: TOAST (NOTIFICHE)
// =============================================
function mostraToast(messaggio, isError = false) {
    const toastEl = document.getElementById('managerToast');
    document.getElementById('toast-msg-manager').textContent = messaggio;
    toastEl.classList.toggle('bg-success', !isError);
    toastEl.classList.toggle('bg-danger', isError);
    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
}

// =============================================
// GESTIONE PIATTI (MODIFICA PRODOTTO)
// =============================================

/**
 * Pre-popola il modale di modifica del menu con i dati estratti dai bottoni della tabella.
 * @param {HTMLElement} btn - Il bottone "Modifica" cliccato nel Menu
 */
function apriModalModifica(btn) {
    const id = btn.getAttribute('data-id');
    const nome = btn.getAttribute('data-nome');
    const desc = btn.getAttribute('data-desc');
    const prezzo = btn.getAttribute('data-prezzo');
    const cat = btn.getAttribute('data-cat');
    const img = btn.getAttribute('data-img');
    const allergeniString = btn.getAttribute('data-allergeni');

    document.getElementById('mod_id').value = id;
    document.getElementById('mod_nome').value = nome;
    document.getElementById('mod_desc').value = desc;
    document.getElementById('mod_prezzo').value = prezzo;
    document.getElementById('mod_cat').value = cat;

    // Preview immagine
    const preview = document.getElementById('preview_img');
    preview.src = img ? "../imgs/prodotti/" + img : '';
    preview.style.display = img ? 'block' : 'none';

    // Gestione checkbox Allergeni
    const checks = document.querySelectorAll('.mod-allergeni');
    checks.forEach(cb => cb.checked = false);
    if (allergeniString) {
        const algs = allergeniString.split(',').map(s => s.trim());
        checks.forEach(cb => { if (algs.includes(cb.value)) cb.checked = true; });
    }

    new bootstrap.Modal(document.getElementById('modalModifica')).show();
}

// =============================================
// INIT
// =============================================
document.addEventListener('DOMContentLoaded', () => {
    // Al caricamento, se siamo nella dashboard manager, carichiamo subito i tavoli
    if (document.getElementById('tavoli-grid')) caricaTavoli();
});

