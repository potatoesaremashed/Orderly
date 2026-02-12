<?php
/**
 * CUCINA
 * -------------------------------------------------------------------------
 * Gestisce l'interfaccia visuale per il personale di cucina.
 *
 * Ruoli autorizzati: Cuoco, Admin/Manager.
 */

session_start();
include '../include/conn.php';

// Reindirizza al login se la sessione non è valida o il ruolo è insufficiente.
if (!isset($_SESSION['ruolo']) || ($_SESSION['ruolo'] != 'cuoco' && $_SESSION['ruolo'] != 'admin')) {
    header("Location: ../index.php");
    exit;
}

include '../include/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/cucina.css">
<link rel="stylesheet" href="../css/common.css">



<div class="sticky-header">
    <div class="d-flex align-items-center gap-3">
        <img src="../imgs/ordnobg.png" width="50">
        <div>
            <div class="brand-title">Cucina</div>
            <div class="brand-subtitle">Monitoraggio Ordini</div>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <div class="theme-toggle" id="btn-audio" onclick="toggleAudio()" title="Suoni">
            <i class="fas fa-volume-mute"></i>
        </div>
    
        <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema">
            <i class="fas fa-moon" id="theme-icon"></i>
        </div>

        <a href="../logout.php" class="theme-toggle text-decoration-none text-danger border-danger" title="Esci">
            <i class="fas fa-power-off"></i>
        </a>
        
        <div class="badge bg-success rounded-pill px-3 py-2 ms-2 d-flex align-items-center gap-2">
            <div style="width:8px; height:8px; background:white; border-radius:50%; animation: blink 1s infinite;"></div>
            LIVE
        </div>
    </div>
</div>

<div class="kanban-board">
    
    <div class="k-column">
        <div class="k-header" style="color: var(--new-order-text);">
            <span><i class="fas fa-bell me-2"></i> IN ARRIVO</span>
            <span class="badge-count" id="count-new">0</span>
        </div>
        <div class="k-body" id="col-new">
            </div>
    </div>

    <div class="k-column">
        <div class="k-header" style="color: var(--prep-order-text);">
            <span><i class="fas fa-fire me-2"></i> IN PREPARAZIONE</span>
            <span class="badge-count" id="count-prep">0</span>
        </div>
        <div class="k-body" id="col-prep">
             </div>
    </div>

</div>

<script>
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
    if(!audioActive) return;
    if(audioCtx.state === 'suspended') audioCtx.resume();
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
    
    if(audioActive) {
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
    fetch('../api/leggi_ordini_cucina.php')
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
            if(diffMin < 0) diffMin = 0; // Fallback per disallineamenti orari client/server
            
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
            if(ordine.stato === 'in_attesa') {
                htmlNew += card;
                cNew++;
            } else if (ordine.stato === 'in_preparazione') {
                htmlPrep += card;
                cPrep++;
            }
        });

        // Trigger Audio
        if (cNew > lastCount) playSound();
        lastCount = cNew;

        // Template per stato vuoto (Empty State)
        const emptyState = (icon, text) => `
            <div class="text-center py-5 mt-5" style="opacity:0.4;">
                <i class="fas ${icon} fa-3x mb-3"></i>
                <h5>${text}</h5>
            </div>`;
        colNew.innerHTML = cNew > 0 ? htmlNew : emptyState('fa-utensils', 'Tutto tranquillo');
        colPrep.innerHTML = cPrep > 0 ? htmlPrep : emptyState('fa-fire-alt', 'Nessun ordine in cottura');

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
    if(stato === 'in_attesa') {
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
    
    fetch('../api/cambia_stato_ordine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_ordine: id, nuovo_stato: nuovoStato })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            caricaOrdini(); // Ricarica immediata dati
        } else {
            alert("Errore API: " + data.message);
        }
    })
    .catch(err => alert("Errore di connessione al server"));
}
</script>

<?php include '../include/footer.php'; ?>
