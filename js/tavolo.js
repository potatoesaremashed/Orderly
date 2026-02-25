let carrello = {}, totaleSoldi = 0, totalePezzi = 0;
let zoomState = { id: null, nome: '', prezzo: 0, qtyAttuale: 1, note: '' };
let filtri = { categoria: 'all', allergeni: [] };

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

function filtraCategoria(idCat, el) {
    document.querySelectorAll('.btn-categoria, .mobile-cat-btn').forEach(b => b.classList.remove('active'));
    if (el) el.classList.add('active');
    filtri.categoria = idCat;
    renderProdotti();
}

function applicaFiltriAllergeni() {
    filtri.allergeni = Array.from(document.querySelectorAll('#modalFiltri input[type="checkbox"]:checked')).map(cb => cb.value);
    renderProdotti();
}

function resettaFiltriAllergeni() {
    document.querySelectorAll('#modalFiltri input[type="checkbox"]').forEach(cb => cb.checked = false);
    filtri.allergeni = [];
    renderProdotti();
}

function renderProdotti() {
    const search = (document.getElementById('search-bar')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.item-prodotto').forEach(piatto => {
        const d = piatto.querySelector('.card-prodotto')?.dataset || {};
        const allergeniPiatto = (d.allergeni || '').toLowerCase().split(',').map(s => s.trim());
        const nomePiatto = (d.nome || '').toLowerCase();

        let mostra = (filtri.categoria === 'all' || piatto.dataset.cat == filtri.categoria)
            && (!filtri.allergeni.length || !filtri.allergeni.some(a => allergeniPiatto.includes(a.toLowerCase())))
            && (!search || nomePiatto.includes(search));

        piatto.style.display = mostra ? 'block' : 'none';
    });
}

function gestisciCarrello(id, delta, prezzo, nome, note = null) {
    const input = document.getElementById(`q-${id}`);
    if (!input) return;

    let qta = (parseInt(input.innerText) || 0) + delta;
    if (qta >= 0) {
        input.innerText = qta;
        totaleSoldi += (delta * prezzo);
        totalePezzi += delta;

        const soldiHeader = document.getElementById('soldi-header');
        const pezziHeader = document.getElementById('pezzi-header');
        if (soldiHeader) soldiHeader.innerText = Math.max(0, totaleSoldi).toFixed(2);
        if (pezziHeader) pezziHeader.innerText = Math.max(0, totalePezzi);

        if (!carrello[id]) carrello[id] = { id, nome, qta: 0, prezzo, note: '' };
        carrello[id].qta = qta;
        if (note !== null) carrello[id].note = note;

        updateCardQtyUI(id, qta);
        if (qta === 0) delete carrello[id];

        const btnInvia = document.getElementById('btn-invia-ordine');
        if (btnInvia) {
            btnInvia.disabled = totalePezzi === 0;
            btnInvia.classList.toggle('btn-dark', totalePezzi > 0);
            btnInvia.classList.toggle('btn-secondary', totalePezzi === 0);
        }

        if (document.getElementById('modalCarrello')?.classList.contains('show')) aggiornaModale();
    }
}

function aggiornaModale() {
    const container = document.getElementById('corpo-carrello');
    const totaleSpan = document.getElementById('totale-modale');

    if (!Object.keys(carrello).length) {
        container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
            <div class="display-1 mb-3" style="opacity:0.3">🍽️</div><h5 class="fw-bold text-muted">Il carrello è vuoto</h5></div>`;
        totaleSpan.innerText = '0.00';
        return;
    }

    let html = '<div class="list-group list-group-flush w-100 px-3 py-2">';
    Object.entries(carrello).forEach(([id, item]) => {
        let notaStr = item.note ? `<div class="small text-muted fst-italic"><i class="fas fa-comment-alt me-1"></i>${item.note}</div>` : '';
        let btnMinus = `<button class="btn-circle btn-minus" style="width: 32px; height: 32px;" onclick="gestisciCarrello(${id}, -1, ${item.prezzo}, '${item.nome.replace(/'/g, "\\'")}')"><i class="fas fa-minus small"></i></button>`;
        let btnPlus = `<button class="btn-circle btn-plus" style="width: 32px; height: 32px;" onclick="gestisciCarrello(${id}, 1, ${item.prezzo}, '${item.nome.replace(/'/g, "\\'")}')"><i class="fas fa-plus small"></i></button>`;

        html += `
            <div class="cart-item list-group-item d-flex align-items-center border-0 mb-3 px-0" style="background: transparent;">
                <div style="flex: 1; min-width: 0;">
                    <h5 class="m-0 fw-bold text-truncate">${item.nome}</h5>${notaStr}
                    <small class="text-muted">${item.prezzo}€ cad.</small>
                </div>
                <div class="qty-capsule d-flex align-items-center justify-content-center mx-2" style="background: var(--capsule-bg); border-radius: 50px; width: 110px; height: 45px; flex-shrink: 0;">
                    ${btnMinus}<span class="text-center fw-bold" style="width: 35px; font-size: 1.1rem;">${item.qta}</span>${btnPlus}
                </div>
                <div style="width: 80px; flex-shrink: 0;" class="text-end">
                    <span class="fw-bold fs-5 text-price" style="color: var(--primary);">${(item.qta * item.prezzo).toFixed(2)}€</span>
                </div>
            </div>`;
    });

    container.innerHTML = html + '</div>';
    totaleSpan.innerText = Math.max(0, totaleSoldi).toFixed(2);
}

function resettaOrdineDopoInvio() {
    carrello = {};
    totaleSoldi = 0;
    totalePezzi = 0;

    const soldiHeader = document.getElementById('soldi-header');
    const pezziHeader = document.getElementById('pezzi-header');
    if (soldiHeader) soldiHeader.innerText = '0.00';
    if (pezziHeader) pezziHeader.innerText = '0';

    document.querySelectorAll('[id^="q-"]').forEach(el => el.innerText = '0');

    const btnInvia = document.getElementById('btn-invia-ordine');
    if (btnInvia) {
        btnInvia.disabled = true;
        btnInvia.classList.replace('btn-dark', 'btn-secondary');
    }
}

document.getElementById('btn-invia-ordine')?.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('modalCarrello'))?.hide();
    new bootstrap.Modal(document.getElementById('modalConfermaOrdine')).show();
});

document.getElementById('confirm-send-btn') && (document.getElementById('confirm-send-btn').onclick = function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Invio...';

    const prodotti = Object.values(carrello).map(i => ({ id: i.id, qta: i.qta, note: i.note }));

    fetch('../api/tavolo/invia_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prodotti })
    }).then(r => r.json()).then(res => {
        bootstrap.Modal.getInstance(document.getElementById('modalConfermaOrdine'))?.hide();

        if (res.success) {
            const modalSuccesso = new bootstrap.Modal(document.getElementById('modalSuccesso'));
            modalSuccesso.show();
            resettaOrdineDopoInvio();
            setTimeout(() => {
                modalSuccesso.hide();
                btn.disabled = false;
                btn.innerHTML = 'SÌ, ORDINA!';
            }, 2000);
        } else {
            alert(res.message);
            btn.disabled = false;
            btn.innerHTML = 'SÌ, ORDINA!';
        }
    }).catch(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalConfermaOrdine'))?.hide();
        btn.disabled = false;
        btn.innerHTML = 'SÌ, ORDINA!';
        alert("Errore di connessione. Riprova.");
    });
});

function apriStorico() {
    const container = document.getElementById('corpo-ordini');
    const totaleSpan = document.getElementById('totale-storico');
    if (totaleSpan) totaleSpan.innerText = '0.00';
    container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';

    fetch('../api/tavolo/leggi_ordini_tavolo.php')
        .then(res => res.json())
        .then(ordini => {
            if (!ordini?.length) {
                container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="display-1 mb-3" style="opacity:0.3">📋</div><h5 class="fw-bold text-muted">Nessun ordine</h5></div>`;
                new bootstrap.Modal(document.getElementById('modalOrdini')).show();
                return;
            }

            const statusMap = {
                'in_attesa': { str: 'In Attesa', cl: 'bg-warning text-dark', ic: 'fa-clock' },
                'in_preparazione': { str: 'In Preparazione', cl: 'bg-primary text-white', ic: 'fa-fire-burner' },
                'pronto': { str: 'Pronto', cl: 'bg-success text-white', ic: 'fa-check-circle' }
            };

            let html = '', grandTotal = 0;
            ordini.forEach(o => {
                const st = statusMap[o.stato] || statusMap['in_attesa'];
                grandTotal += parseFloat(o.totale);

                let piattiHtml = o.piatti.map((p, i) => `
                    <div class="d-flex justify-content-between align-items-start py-2 ${i < o.piatti.length - 1 ? 'border-bottom' : ''}">
                        <div style="flex:1; min-width:0;"><span class="fw-semibold">${p.nome}</span>
                        ${p.note ? `<div class="small text-muted fst-italic"><i class="fas fa-comment-alt me-1"></i>${p.note}</div>` : ''}</div>
                        <div class="text-end ms-3 flex-shrink-0"><span class="text-muted small">x${p.qta}</span>
                        <span class="fw-bold ms-2">${(parseFloat(p.prezzo)).toFixed(2)}€</span></div>
                    </div>`).join('');

                html += `
                    <div class="ordine-card mb-3 p-3 rounded-4 border bg-light-custom">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2"><span class="fw-bold text-muted small"><i class="fas fa-clock me-1"></i>${o.ora}</span></div>
                            <span class="badge ${st.cl} rounded-pill px-3 py-2"><i class="fas ${st.ic} me-1"></i>${st.str}</span>
                        </div>
                        <div class="px-1">${piattiHtml}</div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="text-muted small fw-bold">Ordine #${o.id_ordine}</span>
                            <span class="fw-bold fs-5" style="color: var(--primary);">${o.totale}€</span>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
            if (totaleSpan) totaleSpan.innerText = grandTotal.toFixed(2);
            new bootstrap.Modal(document.getElementById('modalOrdini')).show();
        }).catch(() => {
            container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center py-5">
                <div class="display-1 mb-3" style="opacity:0.3">⚠️</div><h5 class="fw-bold text-muted">Errore</h5></div>`;
            new bootstrap.Modal(document.getElementById('modalOrdini')).show();
        });
}

function apriZoom(e, card) {
    if (e?.target?.closest('.btn-card-qty, .qty-capsule-card')) return;

    const d = card.dataset;
    document.getElementById('zoom-nome').innerText = d.nome;
    document.getElementById('zoom-desc').innerText = d.desc;
    document.getElementById('zoom-prezzo-unitario').innerText = d.prezzo;
    document.getElementById('zoom-img').src = d.img || '';
    document.getElementById('zoom-allergeni').innerHTML = d.allergeni ? d.allergeni.split(',').map(a => `<span class="badge-alg">${a.trim()}</span>`).join('') : '<small>Nessuno</small>';
    document.getElementById('zoom-note').value = carrello[d.id]?.note || '';

    zoomState = { id: d.id, nome: d.nome, prezzo: parseFloat(d.prezzo), qtyAttuale: 1, note: document.getElementById('zoom-note').value };
    refreshZoomUI();
    new bootstrap.Modal(document.getElementById('modalZoom')).show();
}

function updateZoomQty(delta) {
    zoomState.qtyAttuale = Math.max(1, zoomState.qtyAttuale + delta);
    refreshZoomUI();
}

function refreshZoomUI() {
    document.getElementById('zoom-qty-display').innerText = zoomState.qtyAttuale;
    document.getElementById('zoom-btn-totale').innerText = (zoomState.qtyAttuale * zoomState.prezzo).toFixed(2) + '€';
}

function confermaZoom() {
    gestisciCarrello(zoomState.id, zoomState.qtyAttuale, zoomState.prezzo, zoomState.nome, document.getElementById('zoom-note').value);

    document.getElementById('toast-msg').innerText = `${zoomState.nome} aggiunto!`;
    new bootstrap.Toast(document.getElementById('liveToast')).show();
    bootstrap.Modal.getInstance(document.getElementById('modalZoom'))?.hide();
}

function btnCardQty(event, id, delta, prezzo, nome) {
    event.stopPropagation();
    gestisciCarrello(id, delta, prezzo, nome, null);
}

function updateCardQtyUI(id, val) {
    const el = document.getElementById(`q-${id}`);
    if (el) el.innerText = val;
}