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
    document.getElementById('theme-icon')?.classList.replace('fa-moon', 'fa-sun');
}

let audioActive = false;
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playSound() {
    if (!audioActive) return;
    if (audioCtx.state === 'suspended') audioCtx.resume();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();

    osc.connect(gain);
    gain.connect(audioCtx.destination);

    osc.type = "sine";
    osc.frequency.setValueAtTime(500, audioCtx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(1000, audioCtx.currentTime + 0.1);

    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);

    osc.start();
    osc.stop(audioCtx.currentTime + 0.3);
}

function toggleAudio() {
    audioActive = !audioActive;
    const btn = document.getElementById('btn-audio');
    const icon = btn.querySelector('i');

    if (audioActive) {
        icon.className = 'fas fa-volume-up';
        btn.style.borderColor = 'var(--primary)';
        btn.style.color = 'var(--primary)';
        playSound();
    } else {
        icon.className = 'fas fa-volume-mute';
        btn.style.borderColor = 'var(--border-color)';
        btn.style.color = 'var(--text-main)';
    }
}





document.addEventListener("DOMContentLoaded", () => {
    caricaOrdini();
    setInterval(caricaOrdini, 3000);
});

let lastJsonData = "";
let lastCount = 0;

/**
 * Fetch degli ordini dall'API e aggiornamento interfaccia
 */
function caricaOrdini() {
    fetch('../api/cucina/leggi_ordini_cucina.php')
        .then(res => res.json())
        .then(data => {
            const currentJsonString = JSON.stringify(data);
            if (currentJsonString === lastJsonData) return;

            lastJsonData = currentJsonString; // Aggiorna cache

            const colNew = document.getElementById('col-new');
            const colPrep = document.getElementById('col-prep');

            let htmlNew = '';
            let htmlPrep = '';
            let cNew = 0;
            let cPrep = 0;

            data.forEach(ordine => {
                // CALCOLO TEMPO TRASCORSO (Timer)
                // Converte l'orario server in oggetto Date locale per calcolare il delta
                const oraOrdine = new Date();
                const [h, m] = ordine.ora.split(':');
                oraOrdine.setHours(h, m, 0);

                // Calcolo differenza in minuti
                const diffMs = new Date() - oraOrdine;
                let diffMin = Math.floor(diffMs / 60000);
                if (diffMin < 0) diffMin = 0; // Fallback per disallineamenti orari client/server

                // Logica visuale: Rosso se attesa > 15 minuti
                let timerColor = diffMin > 15 ? '#ff6b6b' : 'var(--text-muted)';

                // Generazione HTML lista piatti
                let piattiHtml = '';
                ordine.piatti.forEach(p => {
                    piattiHtml += `
                    <div class="dish-row">
                        <div class="qty-capsule">${p.qta}</div>
                        <div style="padding-top:5px;">
                            ${p.nome}
                            ${p.note ? `<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> ${p.note}</small>` : ''}
                        </div>
                    </div>`;
                });

                // Generazione HTML Card Ordine
                const card = `
                <div class="order-card animate__animated animate__fadeIn">
                    <div class="card-top">
                        <div class="table-badge">${ordine.tavolo}</div>
                        <div class="time-badge" style="color:${timerColor}">
                            <i class="far fa-clock"></i> ${ordine.ora} 
                            <small class="ms-1">(${diffMin} min)</small>
                        </div>
                    </div>
                    <div class="mb-3">${piattiHtml}</div>
                    ${getButton(ordine.id_ordine, ordine.stato)}
                </div>`;

                // Smistamento nelle colonne corrette
                if (ordine.stato === 'in_attesa') {
                    htmlNew += card;
                    cNew++;
                } else if (ordine.stato === 'in_preparazione') {
                    htmlPrep += card;
                    cPrep++;
                }
            });
            // Inserimento HTML dell'ordine all'interno delle colonne
            colNew.innerHTML = htmlNew;
            colPrep.innerHTML = htmlPrep;

            // Aggiornamento contatori header
            document.getElementById('count-new').innerText = cNew;
            document.getElementById('count-prep').innerText = cPrep;
        })
        .catch(err => console.error("Errore fetch ordini:", err));
}

/**
 * Restituisce il pulsante d'azione corretto in base allo stato attuale dell'ordine.
 * @param {number} id - ID dell'ordine
 * @param {string} stato - Stato attuale ('in_attesa' | 'in_preparazione')
 */
function getButton(id, stato) {
    if (stato === 'in_attesa') {
        return `<button class="btn-action btn-start" onclick="cambiaStato(${id}, 'in_preparazione')">
                    INIZIA COTTURA <i class="fas fa-arrow-right ms-2"></i>
                </button>`;
    } else {
        return `<button class="btn-action btn-done" onclick="cambiaStato(${id}, 'pronto')">
                    <i class="fas fa-check me-2"></i> ORDINE PRONTO
                </button>`;
    }
}

/**
 * Invia richiesta API per avanzamento di stato dell'ordine.
 * @param {number} id - ID dell'ordine da aggiornare
 * @param {string} nuovoStato - Il nuovo stato target
 */
function cambiaStato(id, nuovoStato) {
    // Reset cache per forzare il refresh immediato della UI alla prossima chiamata
    lastJsonData = "";

    fetch('../api/cucina/cambia_stato_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ordine: id, nuovo_stato: nuovoStato })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                caricaOrdini(); // Ricarica immediata dati
            } else {
                alert("Errore API: " + data.message);
            }
        })
        .catch(err => alert("Errore di connessione al server"));
}