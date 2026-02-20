/**
 * =========================================
 * FILE: js/manager.js
 * =========================================
 * Logica per la Dashboard Manager:
 * - Navigazione sidebar tra pagine
 * - Gestione tavoli (CRUD + cambio stato)
 * - Gestione piatti (modifica modale)
 * - Toggle tema chiaro/scuro
 */

// =============================================
// DATI E STATO
// =============================================
let tavoli = [];
let filtroCorrente = 'tutti';

// =============================================
// GESTIONE TEMA (DARK / LIGHT MODE)
// =============================================
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';

    body.setAttribute('data-theme', isDark ? 'light' : 'dark');
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
}

// =============================================
// NAVIGAZIONE PAGINE (SIDEBAR)
// =============================================
function switchPage(page, btn) {
    // Nascondi tutte le pagine
    document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');
    // Disattiva tutti i bottoni sidebar
    document.querySelectorAll('.btn-sidebar').forEach(b => b.classList.remove('active'));
    // Disattiva tutti i bottoni mobile
    document.querySelectorAll('.mobile-nav-btn').forEach(b => b.classList.remove('active'));

    // Mostra la pagina selezionata
    document.getElementById('page-' + page).style.display = 'block';

    // Attiva il bottone cliccato
    if (btn) btn.classList.add('active');

    // Sincronizza sidebar e mobile nav
    const idx = page === 'tavoli' ? 0 : 1;
    document.querySelectorAll('.btn-sidebar')[idx]?.classList.add('active');
    document.querySelectorAll('.mobile-nav-btn')[idx]?.classList.add('active');

    // Se pagina tavoli, ricarica
    if (page === 'tavoli') caricaTavoli();
}

// =============================================
// CARICAMENTO TAVOLI
// =============================================
function caricaTavoli() {
    fetch('../api/get_tavoli.php')
        .then(r => r.json())
        .then(data => {
            tavoli = data;
            aggiornaConta();
            renderTavoli();
        })
        .catch(() => {
            document.getElementById('tavoli-grid').innerHTML =
                '<div class="tavoli-empty"><i class="fas fa-exclamation-triangle"></i><h4>Errore</h4><p>Impossibile caricare i tavoli</p></div>';
        });
}

function aggiornaConta() {
    const tutti = tavoli.length;
    const liberi = tavoli.filter(t => t.stato === 'libero').length;
    const occupati = tavoli.filter(t => t.stato === 'occupato').length;
    const riservati = tavoli.filter(t => t.stato === 'riservato').length;

    document.getElementById('count-tutti').textContent = tutti;
    document.getElementById('count-libero').textContent = liberi;
    document.getElementById('count-occupato').textContent = occupati;
    document.getElementById('count-riservato').textContent = riservati;
}

function renderTavoli() {
    const grid = document.getElementById('tavoli-grid');
    let filtered = tavoli;

    if (filtroCorrente !== 'tutti') {
        filtered = tavoli.filter(t => t.stato === filtroCorrente);
    }

    if (filtered.length === 0) {
        grid.innerHTML = '<div class="tavoli-empty"><i class="fas fa-chair"></i><h4>Nessun tavolo</h4><p>Non ci sono tavoli da mostrare</p></div>';
        return;
    }

    grid.innerHTML = filtered.map(t => {
        const stato = t.stato || 'libero';
        const statoLabel = stato.charAt(0).toUpperCase() + stato.slice(1);
        const posti = t.posti || 4;
        const iconClass = getIconForStatus(stato);
        const nextStato = getNextStato(stato);

        return `
            <div class="tavolo-card" data-id="${t.id_tavolo}" data-stato="${stato}">
                <div class="tavolo-card-header">
                    <div class="tavolo-icon ${stato}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="tavolo-name">${t.nome_tavolo}</div>
                    <div class="tavolo-seats">
                        <i class="fas fa-users"></i> ${posti} posti
                    </div>
                </div>
                <div class="tavolo-card-footer">
                    <div class="tavolo-status-badge badge-${stato}" 
                         onclick="event.stopPropagation(); cambiaStatoTavolo(${t.id_tavolo}, '${nextStato}')"
                         title="Clicca per cambiare stato">
                        <span class="status-dot dot-${stato}"></span>
                        ${statoLabel}
                    </div>
                    <div class="tavolo-actions">
                        <button class="btn-act" onclick="event.stopPropagation(); apriModalModificaTavolo(${t.id_tavolo})" title="Modifica">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-act btn-delete-t" onclick="event.stopPropagation(); eliminaTavolo(${t.id_tavolo}, '${t.nome_tavolo}')" title="Elimina">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getIconForStatus(stato) {
    switch (stato) {
        case 'libero': return 'fa-check-circle';
        case 'occupato': return 'fa-utensils';
        case 'riservato': return 'fa-clock';
        default: return 'fa-chair';
    }
}

function getNextStato(stato) {
    switch (stato) {
        case 'libero': return 'occupato';
        case 'occupato': return 'riservato';
        case 'riservato': return 'libero';
        default: return 'libero';
    }
}

// =============================================
// FILTRO TAVOLI
// =============================================
function filtraTavoli(filtro, btn) {
    filtroCorrente = filtro;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderTavoli();
}

// =============================================
// CAMBIO STATO TAVOLO
// =============================================
function cambiaStatoTavolo(id, nuovoStato) {
    const formData = new FormData();
    formData.append('id_tavolo', id);
    formData.append('stato', nuovoStato);

    fetch('../api/cambia_stato_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Aggiorna localmente
                const tavolo = tavoli.find(t => t.id_tavolo == id);
                if (tavolo) tavolo.stato = nuovoStato;
                aggiornaConta();
                renderTavoli();
                mostraToast('Stato aggiornato: ' + nuovoStato);
            } else {
                mostraToast(data.error || 'Errore', true);
            }
        });
}

// =============================================
// AGGIUNGI TAVOLO
// =============================================
function apriModalAggiungi() {
    document.getElementById('nuovo_nome_tavolo').value = '';
    document.getElementById('nuovo_password_tavolo').value = '';
    document.getElementById('nuovo_posti_tavolo').value = '4';
    new bootstrap.Modal(document.getElementById('modalAggiungiTavolo')).show();
}

function aggiungiTavolo() {
    const nome = document.getElementById('nuovo_nome_tavolo').value.trim();
    const password = document.getElementById('nuovo_password_tavolo').value.trim();
    const posti = document.getElementById('nuovo_posti_tavolo').value;

    if (!nome || !password) {
        mostraToast('Compila tutti i campi', true);
        return;
    }

    const formData = new FormData();
    formData.append('nome_tavolo', nome);
    formData.append('password', password);
    formData.append('posti', posti);

    fetch('../api/aggiungi_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAggiungiTavolo')).hide();
                mostraToast('Tavolo "' + nome + '" creato!');
                caricaTavoli();
            } else {
                mostraToast(data.error || 'Errore', true);
            }
        });
}

// =============================================
// MODIFICA TAVOLO
// =============================================
function apriModalModificaTavolo(id) {
    const t = tavoli.find(x => x.id_tavolo == id);
    if (!t) return;

    document.getElementById('mod_id_tavolo').value = t.id_tavolo;
    document.getElementById('mod_nome_tavolo').value = t.nome_tavolo;
    document.getElementById('mod_password_tavolo').value = t.password;
    document.getElementById('mod_posti_tavolo').value = t.posti || 4;

    new bootstrap.Modal(document.getElementById('modalModificaTavolo')).show();
}

function modificaTavolo() {
    const id = document.getElementById('mod_id_tavolo').value;
    const nome = document.getElementById('mod_nome_tavolo').value.trim();
    const password = document.getElementById('mod_password_tavolo').value.trim();
    const posti = document.getElementById('mod_posti_tavolo').value;

    if (!nome || !password) {
        mostraToast('Compila tutti i campi', true);
        return;
    }

    const formData = new FormData();
    formData.append('id_tavolo', id);
    formData.append('nome_tavolo', nome);
    formData.append('password', password);
    formData.append('posti', posti);

    fetch('../api/modifica_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalModificaTavolo')).hide();
                mostraToast('Tavolo aggiornato!');
                caricaTavoli();
            } else {
                mostraToast(data.error || 'Errore', true);
            }
        });
}

// =============================================
// ELIMINA TAVOLO
// =============================================
function eliminaTavolo(id, nome) {
    if (!confirm('Eliminare il tavolo "' + nome + '"? Tutti gli ordini associati verranno eliminati.')) return;

    const formData = new FormData();
    formData.append('id_tavolo', id);

    fetch('../api/elimina_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostraToast('Tavolo "' + nome + '" eliminato');
                caricaTavoli();
            } else {
                mostraToast(data.error || 'Errore', true);
            }
        });
}

// =============================================
// TOAST
// =============================================
function mostraToast(messaggio, isError = false) {
    const toastEl = document.getElementById('managerToast');
    const msgSpan = document.getElementById('toast-msg-manager');

    msgSpan.textContent = messaggio;
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' +
        (isError ? 'bg-danger' : 'bg-success');

    const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
    toast.show();
}

// =============================================
// GESTIONE MODALE MODIFICA PIATTO (existing)
// =============================================
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

    const preview = document.getElementById('preview_img');
    if (img) {
        preview.src = "../imgs/prodotti/" + img;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    const checkboxes = document.querySelectorAll('.mod-allergeni');
    checkboxes.forEach(cb => cb.checked = false);

    if (allergeniString && allergeniString.trim() !== "") {
        const allergeniArray = allergeniString.split(',').map(s => s.trim());
        checkboxes.forEach(cb => {
            if (allergeniArray.includes(cb.value)) {
                cb.checked = true;
            }
        });
    }

    const modalElement = document.getElementById('modalModifica');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// =============================================
// INIZIALIZZAZIONE
// =============================================
document.addEventListener('DOMContentLoaded', function () {
    caricaTavoli();
});
