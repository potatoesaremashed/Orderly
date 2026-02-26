let tavoli = [];
let filtroCorrente = 'tutti';

// Auto-refresh: aggiorna lo stato dei tavoli ogni 5 secondi
setInterval(() => { caricaTavoli(); }, 5000);

function toggleTheme() {
    const isDark = document.body.getAttribute('data-theme') === 'dark';
    document.body.setAttribute('data-theme', isDark ? 'light' : 'dark');
    document.getElementById('theme-icon')?.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon')?.classList.replace('fa-moon', 'fa-sun');
}

function switchPage(page, btn) {
    document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.btn-sidebar, .mobile-nav-btn').forEach(b => b.classList.remove('active'));

    document.getElementById(`page-${page}`).style.display = 'block';
    if (btn) btn.classList.add('active');

    const idx = page === 'tavoli' ? 0 : 1;
    document.querySelectorAll('.btn-sidebar')[idx]?.classList.add('active');
    document.querySelectorAll('.mobile-nav-btn')[idx]?.classList.add('active');

    if (page === 'tavoli') caricaTavoli();
}

function caricaTavoli() {
    fetch('../api/manager/get_tavoli.php')
        .then(r => r.json())
        .then(data => {
            tavoli = data;
            aggiornaConta();
            renderTavoli();
        })
        .catch(() => {
            document.getElementById('tavoli-grid').innerHTML = '<div class="tavoli-empty text-danger"><h4>Errore Server</h4></div>';
        });
}

function aggiornaConta() {
    document.getElementById('count-tutti').textContent = tavoli.length;
    ['libero', 'occupato', 'riservato'].forEach(stato => {
        document.getElementById(`count-${stato}`).textContent = tavoli.filter(t => t.stato === stato).length;
    });
}

function renderTavoli() {
    const grid = document.getElementById('tavoli-grid');
    const filtrati = filtroCorrente === 'tutti' ? tavoli : tavoli.filter(t => t.stato === filtroCorrente);

    if (!filtrati.length) {
        grid.innerHTML = '<div class="tavoli-empty"><h4>Nessun tavolo</h4></div>';
        return;
    }

    grid.innerHTML = filtrati.map(t => {
        const stato = t.stato || 'libero';
        const icona = stato === 'libero' ? 'fa-check-circle' : (stato === 'occupato' ? 'fa-utensils' : 'fa-clock');
        const nextStato = { 'libero': 'riservato', 'riservato': 'libero' }[stato];

        return `
            <div class="tavolo-card" data-id="${t.id_tavolo}">
                <div class="tavolo-card-header">
                    <div class="tavolo-icon ${stato}"><i class="fas ${icona}"></i></div>
                    <div class="tavolo-name">${t.nome_tavolo}</div>
                    <div class="tavolo-seats"><i class="fas fa-users"></i> ${t.posti || 4} posti</div>
                </div>
                <div class="tavolo-card-footer">
                    <div class="tavolo-status-badge badge-${stato}" ${stato !== 'occupato' ? `onclick="cambiaStatoTavolo(${t.id_tavolo}, '${nextStato}')"` : ''} ${stato === 'occupato' ? 'style="cursor: default; opacity: 0.8;"' : ''}>
                        <span class="status-dot dot-${stato}"></span> ${stato.toUpperCase()}
                    </div>
                    <div class="tavolo-actions">
                        <button class="btn-act" onclick="apriModalModificaTavolo(${t.id_tavolo})"><i class="fas fa-pen"></i></button>
                        <button class="btn-act btn-delete-t" onclick="terminaSessione(${t.id_tavolo})" title="Termina sessione"><i class="fas fa-ban"></i></button>
                        <button class="btn-act btn-delete-t" onclick="eliminaTavolo(${t.id_tavolo}, '${t.nome_tavolo}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>`;
    }).join('');
}

function filtraTavoli(filtro, btn) {
    filtroCorrente = filtro;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderTavoli();
}

function cambiaStatoTavolo(id, nuovoStato) {
    const formData = new FormData();
    formData.append('id_tavolo', id);
    formData.append('stato', nuovoStato);

    fetch('../api/manager/cambia_stato_tavolo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const t = tavoli.find(x => x.id_tavolo == id);
                if (t) t.stato = nuovoStato;
                aggiornaConta();
                renderTavoli();
                mostraToast(`Tavolo: ${nuovoStato}`);
            }
        });
}

function apriModalAggiungi() {
    document.querySelector('#modalAggiungiTavolo form')?.reset();
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

    ['id_tavolo', 'nome_tavolo', 'password', 'posti', 'stato'].forEach(k => {
        document.getElementById(`mod_${k}`).value = t[k] || (k === 'posti' ? 4 : 'libero');
    });

    new bootstrap.Modal(document.getElementById('modalModificaTavolo')).show();
}

function modificaTavolo() {
    const formData = new FormData();
    ['id_tavolo', 'nome_tavolo', 'password', 'posti', 'stato'].forEach(k => {
        formData.append(k, document.getElementById(`mod_${k}`).value);
    });

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
    if (!confirm(`Vuoi davvero eliminare il tavolo ${nome}?`)) return;

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

function mostraToast(messaggio, isError = false) {
    const toastEl = document.getElementById('managerToast');
    document.getElementById('toast-msg-manager').textContent = messaggio;
    toastEl.classList.toggle('bg-success', !isError);
    toastEl.classList.toggle('bg-danger', isError);
    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
}

function terminaSessione(id) {
    if (!confirm('Vuoi terminare la sessione per questo tavolo? Lo storico ordini verrà resettato.')) return;

    const formData = new FormData();
    formData.append('id_tavolo', id);

    fetch('../api/manager/termina_sessione.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostraToast('Sessione terminata!');
                caricaTavoli();
            } else {
                mostraToast(data.error || 'Errore', true);
            }
        });
}

function apriModalModifica(btn) {
    const d = btn.dataset;
    ['id', 'nome', 'desc', 'prezzo', 'cat'].forEach(k => {
        document.getElementById(`mod_${k}`).value = d[k];
    });

    const preview = document.getElementById('preview_img');
    preview.src = d.img || '';
    preview.style.display = d.img ? 'block' : 'none';

    document.querySelectorAll('.mod-allergeni').forEach(cb => cb.checked = false);
    if (d.allergeni) {
        const algs = d.allergeni.split(',').map(s => s.trim());
        document.querySelectorAll('.mod-allergeni').forEach(cb => {
            if (algs.includes(cb.value)) cb.checked = true;
        });
    }

    new bootstrap.Modal(document.getElementById('modalModifica')).show();
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#menu') {
        const btnMenu = document.querySelectorAll('.btn-sidebar')[1];
        switchPage('menu', btnMenu || null);
    } else if (document.getElementById('tavoli-grid')) {
        caricaTavoli();
    }

    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease';
            successAlert.style.opacity = '0';
            setTimeout(() => {
                successAlert.style.display = 'none';
                const url = new URL(window.location);
                url.searchParams.delete('msg');
                window.history.replaceState({}, '', url);
            }, 500);
        }, 1000);
    }

    inizializzaDropzones();
});

// --- Drag & Drop Gestione CSV e Immagini ---
function inizializzaDropzones() {
    const csvZone = document.getElementById('dropzone-csv');
    const imgZone = document.getElementById('dropzone-img');
    const inputCsv = document.getElementById('input-csv');
    const inputImg = document.getElementById('input-img');

    if (!csvZone || !imgZone) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        csvZone.addEventListener(eventName, preventDefaults, false);
        imgZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        csvZone.addEventListener(eventName, () => csvZone.classList.add('dragover'), false);
        imgZone.addEventListener(eventName, () => imgZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        csvZone.addEventListener(eventName, () => csvZone.classList.remove('dragover'), false);
        imgZone.addEventListener(eventName, () => imgZone.classList.remove('dragover'), false);
    });

    csvZone.addEventListener('drop', (e) => handleDropCsv(e.dataTransfer.files), false);
    inputCsv.addEventListener('change', function () { handleDropCsv(this.files); });

    imgZone.addEventListener('drop', (e) => handleDropImg(e.dataTransfer.files), false);
    inputImg.addEventListener('change', function () { handleDropImg(this.files); });
}

function handleDropCsv(files) {
    if (!files || files.length === 0) return;
    const file = files[0];
    if (file.type !== "text/csv" && !file.name.endsWith('.csv')) {
        return mostraToast("Per favore carica un file CSV valido.", true);
    }

    const formData = new FormData();
    formData.append('file_csv', file);

    const zone = document.getElementById('dropzone-csv');
    zone.classList.add('loading');

    fetch('../api/manager/upload_csv.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            zone.classList.remove('loading');
            if (data.success) {
                mostraToast(data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                mostraToast("Errore: " + data.error, true);
            }
        })
        .catch(err => {
            zone.classList.remove('loading');
            mostraToast("Errore di rete durante il caricamento CSV", true);
        });
}

function handleDropImg(files) {
    if (!files || files.length === 0) return;

    const formData = new FormData();
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            formData.append('immagini[]', file);
        }
    });

    if (!formData.has('immagini[]')) {
        return mostraToast("Per favore carica solo file immagine.", true);
    }

    const zone = document.getElementById('dropzone-img');
    zone.classList.add('loading');

    fetch('../api/manager/upload_immagini.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            zone.classList.remove('loading');
            if (data.success) {
                const msg = data.message.replace(/\n/g, ' - ');
                mostraToast(msg);
                setTimeout(() => window.location.reload(), 2000);
            } else {
                mostraToast("Errore: " + data.error, true);
            }
        })
        .catch(err => {
            zone.classList.remove('loading');
            mostraToast("Errore di rete caricamento Immagini", true);
        });
}

