// File JavaScript dedicato al Pannello di Controllo (Manager Dashboard)

document.addEventListener('DOMContentLoaded', function () {
    // Al caricamento della pagina, facciamo la prima chiamata per ottenere i tavoli
    caricaTavoli();
    // Impostiamo un timer che aggiorna la situazione tavoli ogni 10 secondi in background
    setInterval(caricaTavoli, 10000);

    // Ripristino del tema visivo prescelto dal Local Storage
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('[id="theme-icon"]').forEach(icon => {
            icon.classList.replace('fa-moon', 'fa-sun');
        });
    }

    // Nasconde in automatico l'avviso verde di successo dopo 3 secondi
    const alert = document.getElementById('success-alert');
    if (alert) setTimeout(() => alert.style.display = 'none', 3000);
});

// --- Navigazione tra le sezioni ---
// Nasconde tutti i div di classe 'page-section' e rende visibile solo quello richiesto.
function switchPage(page, el) {
    document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');
    document.getElementById('page-' + page).style.display = 'block';

    // Aggiorna la classe 'active' per far sembrare il bottone premuto
    document.querySelectorAll('.btn-sidebar, .mobile-nav-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    // Sincronizza lo stato attivo tra la navigazione desktop (sidebar) e mobile (bottom bar)
    const idx = page === 'tavoli' ? 0 : 1;
    document.querySelectorAll('.btn-sidebar')[idx]?.classList.add('active');
    document.querySelectorAll('.mobile-nav-btn')[idx]?.classList.add('active');
}

// --- Gestione Tavoli in Tempo Reale ---
let allTavoli = []; // Variabile globale usata per conservare l'ultimo stato inviato dal server

// Funzione AJAX per scaricare la lista dei tavoli dal database
function caricaTavoli() {
    fetch('../api/manager/get_tavoli.php')
        .then(r => r.json())
        .then(data => {
            allTavoli = data;
            aggiornaConteggi(data);
            renderTavoli(data);
        });
}

// Aggiorna i contatori dei badge colorati riassumendo lo stato dei tavoli
function aggiornaConteggi(data) {
    const counts = { libero: 0, occupato: 0, riservato: 0 };
    data.forEach(t => { if (counts[t.stato] !== undefined) counts[t.stato]++; });
    document.getElementById('count-tutti').textContent = data.length;
    document.getElementById('count-libero').textContent = counts.libero;
    document.getElementById('count-occupato').textContent = counts.occupato;
    document.getElementById('count-riservato').textContent = counts.riservato;
}

// Gestisce i filtri laterali ("Solo Liberi", "Solo Occupati")
function filtraTavoli(filtro, btn) {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // Genera un nuovo array filtrato in base alla condizione passata
    const filtered = filtro === 'tutti' ? allTavoli : allTavoli.filter(t => t.stato === filtro);
    renderTavoli(filtered); // Ridisegna la griglia con i tavoli filtrati
}

// Genera fisicamente l'HTML delle "Card Tavolo"
function renderTavoli(tavoli) {
    const grid = document.getElementById('tavoli-grid');
    if (!tavoli.length) {
        grid.innerHTML = `<div class="tavoli-empty"><i class="fas fa-chair"></i><h4>Nessun tavolo trovato</h4><p class="small">Aggiungi un tavolo per iniziare</p></div>`;
        return;
    }

    grid.innerHTML = tavoli.map(t => {
        const stato = t.stato || 'libero'; // Assicura che esista uno stato di default
        // Seleziona un'icona appropriata in base allo stato
        const icon = stato === 'libero' ? 'fa-check-circle' : stato === 'occupato' ? 'fa-users' : 'fa-clock';
        const label = stato.charAt(0).toUpperCase() + stato.slice(1);

        // Costruisce la card HTML per un singolo tavolo
        return `<div class="tavolo-card" data-stato="${stato}">
            <div class="tavolo-card-header">
                <div class="tavolo-icon ${stato}"><i class="fas ${icon}"></i></div>
                <div class="tavolo-name">${t.nome_tavolo}</div>
                <div class="tavolo-seats"><i class="fas fa-chair"></i> ${t.posti} posti</div>
            </div>
            <div class="tavolo-card-footer">
                <!-- Bottone rotondeggiante che permette di ciclare lo stato a mano (es. da Libero a Riservato) -->
                <div class="tavolo-status-badge badge-${stato}" onclick="ciclaNuovoStato(${t.id_tavolo}, '${stato}')">
                    <span class="status-dot dot-${stato}"></span> ${label}
                </div>
                <div class="tavolo-actions">
                    <!-- Se il tavolo è occupato mostra il bottone per resettare la sessione e scollegare gli smartphone attaccati -->
                    ${stato === 'occupato' ? `<button class="btn-act" title="Resetta" onclick="terminaSessione(${t.id_tavolo})"><i class="fas fa-redo-alt"></i></button>` : ''}
                    <button class="btn-act" title="Modifica" onclick="apriModifica(${t.id_tavolo},'${t.nome_tavolo}','${t.password}',${t.posti},'${stato}')"><i class="fas fa-pen"></i></button>
                    <button class="btn-act btn-delete-t" title="Elimina" onclick="eliminaTavolo(${t.id_tavolo}, '${t.nome_tavolo}')"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>`;
    }).join('');
}

// Cambia rapidamente lo stato del tavolo cliccando sul badge colorato (Libero -> Occupato -> Riservato)
function ciclaNuovoStato(id, statoAttuale) {
    const ordine = ['libero', 'occupato', 'riservato'];
    const nuovoStato = ordine[(ordine.indexOf(statoAttuale) + 1) % ordine.length];
    const fd = new FormData();
    fd.append('id_tavolo', id);
    fd.append('stato', nuovoStato);

    fetch('../api/manager/cambia_stato_tavolo.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) caricaTavoli(); else mostraToast('Errore: ' + data.error, true); });
}

// Interrompe la connessione di un Tavolo (rimuove il device_token) svuotando l'account per la sessione successiva
function terminaSessione(id) {
    if (!confirm('Terminare la sessione di questo tavolo?')) return;
    const fd = new FormData();
    fd.append('id_tavolo', id);

    fetch('../api/manager/termina_sessione.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) { mostraToast('Sessione terminata'); caricaTavoli(); } });
}

// --- Apertura delle Modali (Finestre in sovrimpressione) ---
function apriModalAggiungi() {
    new bootstrap.Modal(document.getElementById('modalAggiungiTavolo')).show();
}

// Funzione collegata al bottone dentro la modale per inviare la richiesta API e creare il tavolo
function aggiungiTavolo() {
    const fd = new FormData();
    fd.append('nome_tavolo', document.getElementById('nuovo_nome_tavolo').value);
    fd.append('password', document.getElementById('nuovo_password_tavolo').value);
    fd.append('posti', document.getElementById('nuovo_posti_tavolo').value);

    fetch('../api/manager/aggiungi_tavolo.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostraToast('Tavolo registrato!');
                // Chiude la modale con successo
                bootstrap.Modal.getInstance(document.getElementById('modalAggiungiTavolo')).hide();
                caricaTavoli(); // Ricarica la griglia per accogliere il nuovo venuto
            } else {
                mostraToast(data.error, true);
            }
        });
}

// Pre-imposta i parametri nei form input quando vuoi modificare un tavolo esistente
function apriModifica(id, nome, pass, posti, stato) {
    document.getElementById('mod_id_tavolo').value = id;
    document.getElementById('mod_nome_tavolo').value = nome;
    document.getElementById('mod_password').value = pass;
    document.getElementById('mod_posti').value = posti;
    document.getElementById('mod_stato').value = stato;
    new bootstrap.Modal(document.getElementById('modalModificaTavolo')).show();
}

// Salva e applica le modifiche estetiche/funzionali ad un tavolo tramite chiamata API
function modificaTavolo() {
    const fd = new FormData();
    fd.append('id_tavolo', document.getElementById('mod_id_tavolo').value);
    fd.append('nome_tavolo', document.getElementById('mod_nome_tavolo').value);
    fd.append('password', document.getElementById('mod_password').value);
    fd.append('posti', document.getElementById('mod_posti').value);
    fd.append('stato', document.getElementById('mod_stato').value);

    fetch('../api/manager/modifica_tavolo.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostraToast('Modifiche salvate!');
                bootstrap.Modal.getInstance(document.getElementById('modalModificaTavolo')).hide();
                caricaTavoli();
            } else {
                mostraToast('Errore: ' + data.error, true);
            }
        });
}

// Rimuove permanentemente un tavolo dai server 
function eliminaTavolo(id, nome) {
    if (!confirm('Eliminare il tavolo "' + nome + '"?')) return;
    const fd = new FormData();
    fd.append('id_tavolo', id);

    fetch('../api/manager/elimina_tavolo.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { mostraToast('Tavolo eliminato'); caricaTavoli(); }
            else mostraToast('Errore: ' + data.error, true);
        });
}

// --- Modifica Piatto del Menu ---
// Inietta i dati dal DB negli input forms in attesa dell'aggiornamento
function apriModalModifica(btn) {
    document.getElementById('mod_id').value = btn.dataset.id;
    document.getElementById('mod_nome').value = btn.dataset.nome;
    document.getElementById('mod_desc').value = btn.dataset.desc;
    document.getElementById('mod_prezzo').value = btn.dataset.prezzo;
    document.getElementById('mod_cat').value = btn.dataset.cat;

    // Mostra l'anteprima dell'immagine attuale associata
    document.getElementById('preview_img').src = btn.dataset.img || '';

    // Parsa gli allergeni presenti nella stringa per spuntare i box HTML corrispondenti 
    const list = btn.dataset.allergeni.split(',').map(a => a.trim().toLowerCase());
    document.querySelectorAll('.mod-allergeni').forEach(cb => {
        cb.checked = list.includes(cb.value.toLowerCase());
    });

    // Apre definitivamente la finestra
    new bootstrap.Modal(document.getElementById('modalModifica')).show();
}

// --- Funzioni Generiche (Toast & Notifiche) ---
function mostraToast(msg, isError = false) {
    const el = document.getElementById('managerToast');
    // Genera sfumature rosse se error è true, classiche verdi altrimenti
    el.className = `toast align-items-center text-white border-0 shadow-lg ${isError ? 'bg-danger' : 'bg-success'}`;
    document.getElementById('toast-msg-manager').textContent = msg;
    new bootstrap.Toast(el, { delay: 3000 }).show(); // Dura 3 secondi e scompare magicamente
}
