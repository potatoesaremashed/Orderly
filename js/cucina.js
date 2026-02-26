// Kitchen Dashboard JS

document.addEventListener('DOMContentLoaded', function () {
    caricaOrdini();
    setInterval(caricaOrdini, 5000);

    // Init theme icon
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('[id="theme-icon"]').forEach(icon => {
            icon.classList.replace('fa-moon', 'fa-sun');
        });
    }
});

let lastOrderCount = 0;
const audio = new Audio('../audio/notifica_cucina.mp3');

function caricaOrdini() {
    fetch('../api/cucina/leggi_ordini_cucina.php')
        .then(r => r.json())
        .then(data => {
            const inAttesa = data.filter(o => o.stato === 'in_attesa');
            const inPrep = data.filter(o => o.stato === 'in_preparazione');

            document.getElementById('count-new').textContent = inAttesa.length;
            document.getElementById('count-prep').textContent = inPrep.length;

            document.getElementById('col-new').innerHTML = inAttesa.map(o => renderCard(o, 'new')).join('');
            document.getElementById('col-prep').innerHTML = inPrep.map(o => renderCard(o, 'prep')).join('');

            // Audio notification for new orders
            if (inAttesa.length > lastOrderCount && lastOrderCount > 0) {
                audio.play().catch(() => { });
            }
            lastOrderCount = inAttesa.length;
        });
}

function renderCard(o, tipo) {
    const btnLabel = tipo === 'new' ? 'Inizia Preparazione' : 'Segna come Pronto ✓';
    const nextState = tipo === 'new' ? 'in_preparazione' : 'pronto';
    const btnClass = tipo === 'new' ? 'btn-start' : 'btn-green-custom';

    const piatti = o.piatti.map(p =>
        `<div class="dish-row"><div class="qty-capsule">${p.qta}x</div>
         <div><strong>${p.nome}</strong>${p.note ? `<br><small class="text-muted"><i class="fas fa-sticky-note me-1"></i>${p.note}</small>` : ''}</div></div>`
    ).join('');

    return `<div class="order-card">
        <div class="card-top">
            <div class="table-badge">${o.tavolo}</div>
            <div class="time-badge"><i class="fas fa-clock"></i> ${o.ora}</div>
        </div>
        ${piatti}
        <button class="btn-action ${btnClass}" onclick="cambiaStato(${o.id_ordine}, '${nextState}')">
            <i class="fas ${tipo === 'new' ? 'fa-fire' : 'fa-check'}"></i> ${btnLabel}
        </button>
    </div>`;
}

function cambiaStato(id, stato) {
    fetch('../api/cucina/cambia_stato_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ordine: id, nuovo_stato: stato })
    })
        .then(r => r.json())
        .then(data => { if (data.success) caricaOrdini(); });
}
