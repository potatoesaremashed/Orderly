// File JavaScript dedicato al Terminale dello Chef (Kitchen Dashboard)

document.addEventListener('DOMContentLoaded', function () {
    // Non appena la pagina è caricata, interroga l'API per l'elenco immediato.
    caricaOrdini();

    // Heartbeat asincrono: continua a interrogare il database ogni 5 secondi,
    // mantenendo aggiornata la Kanban Board con i nuovi arrivi.
    setInterval(caricaOrdini, 5000);

    // Controlla il local storage per il ripristino istantaneo del tema scuro
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('[id="theme-icon"]').forEach(icon => {
            icon.classList.replace('fa-moon', 'fa-sun');
        });
    }
});

// Tracciamento usato per identificare quando arriva uno squillo in cucina.
let lastOrderCount = 0;
// Caricamento in memoria del suond di campanella per attirare l'attenzione dello chef.
const audio = new Audio('../audio/notifica_cucina.mp3');

// Funzione base che contatta il server PHP per recuperare tutti gli ordini non chiusi
function caricaOrdini() {
    fetch('../api/cucina/leggi_ordini_cucina.php')
        .then(r => r.json())
        .then(data => {
            // Smista gli ordini nei tre macrogruppi per gestirne le tre colonne.
            const inAttesa = data.filter(o => o.stato === 'in_attesa');
            const inPrep = data.filter(o => o.stato === 'in_preparazione');

            // Setta i numerini sulle testate rosa e blu
            document.getElementById('count-new').textContent = inAttesa.length;
            document.getElementById('count-prep').textContent = inPrep.length;

            // Avvia la rigenerazione completa del blocco HTML che compone i Ticket delle portate
            document.getElementById('col-new').innerHTML = inAttesa.map(o => renderCard(o, 'new')).join('');
            document.getElementById('col-prep').innerHTML = inPrep.map(o => renderCard(o, 'prep')).join('');

            // Segnale acustico speciale: se il numero di piatti in attesa è aumentato rispetto l'ultimo check,
            // suona l'allarme musicale a patto che inAttesa in se sia maggiore di 0.
            if (inAttesa.length > lastOrderCount && lastOrderCount > 0) {
                audio.play().catch(() => { }); // Fallback silenzioso se la riproduzione è bloccata dalle policy del browser
            }

            // Salviamo lo statino in attesa del prossimo polling
            lastOrderCount = inAttesa.length;
        });
}

// "Fabbrica HTML" - Crea il ticket kanban per la comanda, formattato a seconda dello Stato
function renderCard(o, tipo) {
    // Dinamizza logiche testuali del bottone a seconda della colonna in cui questo ticket nascerà
    const btnLabel = tipo === 'new' ? 'Inizia Preparazione' : 'Segna come Pronto ✓';
    const nextState = tipo === 'new' ? 'in_preparazione' : 'pronto';
    const btnClass = tipo === 'new' ? 'btn-start' : 'btn-green-custom';

    // Disassembla ciascun piatto richiesto nel blocco e disegna gli aggregati X quantità con eventuali note allegate dal cliente
    const piatti = o.piatti.map(p =>
        `<div class="dish-row"><div class="qty-capsule">${p.qta}x</div>
         <div><strong>${p.nome}</strong>${p.note ? `<br><small class="text-muted"><i class="fas fa-sticky-note me-1"></i>${p.note}</small>` : ''}</div></div>`
    ).join('');

    // Restituisce il Ticket formattato (div order-card)
    return `<div class="order-card">
        <div class="card-top">
            <div class="table-badge">${o.tavolo}</div>
            <div class="time-badge"><i class="fas fa-clock"></i> ${o.ora}</div>
        </div>
        ${piatti}
        
        <!-- Bottone multifunzione per avanzare ticket verso chiusura -->
        <button class="btn-action ${btnClass}" onclick="cambiaStato(${o.id_ordine}, '${nextState}')">
            <i class="fas ${tipo === 'new' ? 'fa-fire' : 'fa-check'}"></i> ${btnLabel}
        </button>
    </div>`;
}

// Invia l'input dello chef al DB di variare lo status su quell'Ordine univoco.
function cambiaStato(id, stato) {
    fetch('../api/cucina/cambia_stato_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ordine: id, nuovo_stato: stato }) // Serializzato a mano
    })
        .then(r => r.json())
        .then(data => {
            // Aggiorna istantaneamente lo schermo bypassando i 5 secondi limite in modo da dare feedback veloce e fluido.
            if (data.success) caricaOrdini();
        });
}
