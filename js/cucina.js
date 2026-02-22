/**
 * =========================================
 * FILE: js/cucina.js
 * =========================================
 * Questo script gestisce la "Dashboard Cucina" (il Kanban Board).
 * Si occupa di caricare gli ordini in tempo reale e permettere ai cuochi
 * di avanzare lo stato (Inizia Cottura -> Pronto).
 * 
 * Usiamo il "polling" (setInterval) per simulare il tempo reale.
 * Ogni 3 secondi chiediamo al server: "Ci sono nuovi ordini?".
 */

/**
 * GESTIONE TEMA (DARK/LIGHT)
 * Cambia i colori della pagina e salva la scelta nel browser (localStorage).
 */
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';

    body.setAttribute('data-theme', isDark ? 'light' : 'dark');
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

// All'avvio, controlla se l'utente aveva già scelto il tema scuro
if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon')?.classList.replace('fa-moon', 'fa-sun');
}

/**
 * SISTEMA DI NOTIFICA SONORA
 * Utilizza la Web Audio API per generare un "beep" senza caricare file .mp3 esterni.
 */
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

// Attiva/Disattiva il suono delle notifiche
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

// =========== LOGICA CORE DASHBOARD ===========

document.addEventListener("DOMContentLoaded", () => {
    caricaOrdini(); // Primo carico immediato
    setInterval(caricaOrdini, 3000); // Poi ogni 3 secondi (Polling)
});

// Cache per evitare di ridisegnare la pagina se i dati non sono cambiati
let lastJsonData = "";

/**
 * Recupera gli ordini dall'API e aggiorna le colonne del Kanban.
 */
function caricaOrdini() {
    fetch('../api/cucina/leggi_ordini_cucina.php')
        .then(res => res.json())
        .then(data => {
            // Se i dati sono identici ai precedenti, non fare nulla (ottimizzazione)
            const currentJsonString = JSON.stringify(data);
            if (currentJsonString === lastJsonData) return;

            lastJsonData = currentJsonString;

            // Se ci sono nuovi ordini rispetto a prima, suona il beep!
            if (data.length > lastCount && lastCount !== 0) {
                playSound();
            }
            lastCount = data.length;

            const colNew = document.getElementById('col-new');
            const colPrep = document.getElementById('col-prep');

            let htmlNew = '';
            let htmlPrep = '';
            let cNew = 0;
            let cPrep = 0;

            data.forEach(ordine => {
                // CALCOLO TIMER: Vediamo da quanto tempo l'ordine è in attesa
                const oraOrdine = new Date();
                const [h, m] = ordine.ora.split(':');
                oraOrdine.setHours(h, m, 0);

                const diffMs = new Date() - oraOrdine;
                let diffMin = Math.floor(diffMs / 60000);
                if (diffMin < 0) diffMin = 0;

                // Estetica: se passano più di 15 minuti, il timer diventa rosso (URGENTE)
                let timerColor = diffMin > 15 ? '#ff6b6b' : 'var(--text-muted)';

                // Stringa HTML per i piatti dentro l'ordine
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

                // Costruzione della "Card" dell'ordine
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

                // Smistamento visivo tra le colonne "Nuovi" e "In Preparazione"
                if (ordine.stato === 'in_attesa') {
                    htmlNew += card;
                    cNew++;
                } else if (ordine.stato === 'in_preparazione') {
                    htmlPrep += card;
                    cPrep++;
                }
            });

            // Inseriamo l'HTML generato nelle rispettive colonne
            colNew.innerHTML = htmlNew || '<div class="text-center text-muted p-4">In attesa di ordini...</div>';
            colPrep.innerHTML = htmlPrep || '<div class="text-center text-muted p-4">Nessun piatto sul fuoco</div>';

            // Aggiorniamo i numeretti sopra le colonne
            document.getElementById('count-new').innerText = cNew;
            document.getElementById('count-prep').innerText = cPrep;
        })
        .catch(err => console.error("Errore nel caricamento ordini:", err));
}

let lastCount = 0; // Contatore globale per sapere se suonare o no

/**
 * Ritorna l'HTML del bottone d'azione corretto.
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
 * Chiama l'API per cambiare lo stato dell'ordine nel database.
 */
function cambiaStato(id, nuovoStato) {
    // Svuotiamo la cache così la pagina si aggiorna subito
    lastJsonData = "";

    fetch('../api/cucina/cambia_stato_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ordine: id, nuovo_stato: nuovoStato })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                caricaOrdini(); // Rinfresca subito i dati
            } else {
                alert("Errore: " + data.message);
            }
        })
        .catch(err => alert("Errore di connessione. Riprova."));
}
