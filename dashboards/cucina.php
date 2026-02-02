<?php
session_start();
include '../include/conn.php';


if (!isset($_SESSION['ruolo']) || ($_SESSION['ruolo'] != 'cuoco' && $_SESSION['ruolo'] != 'admin')) {
    header("Location: ../index.php");
    exit;
}
include '../include/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* --- STESSE VARIABILI DEL TAVOLO --- */
    :root {
        --primary: #ff9f43;
        --dark: #2d3436;
        --bg-color: #f8f9fa;
        --surface-color: #ffffff;
        --text-main: #2d3436;
        --text-muted: #636e72;
        --border-color: rgba(0,0,0,0.05);
        --card-radius: 20px;
        --shadow-color: rgba(0,0,0,0.05);
        
        /* Colori Specifici Cucina (Pastello/Moderni) */
        --new-order-bg: #fff0f0;
        --new-order-text: #ff6b6b;
        --prep-order-bg: #f0f8ff;
        --prep-order-text: #00a8ff;
    }

    [data-theme="dark"] {
        --bg-color: #1e1e1e;
        --surface-color: #2d2d2d;
        --text-main: #ecf0f1;
        --text-muted: #b2bec3;
        --border-color: rgba(255,255,255,0.1);
        --shadow-color: rgba(0,0,0,0.3);
        --new-order-bg: #4a2b2b;
        --prep-order-bg: #2b3a4a;
    }

    body { 
        background-color: var(--bg-color); 
        font-family: 'Poppins', sans-serif; 
        color: var(--text-main);
        overflow: hidden; 
        height: 100vh;
        transition: background 0.3s, color 0.3s;
    }

    /* --- HEADER (Uguale al tavolo) --- */
    .sticky-header {
        background: var(--surface-color);
        padding: 1rem 2rem;
        display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid var(--border-color);
        height: 80px;
    }

    .brand-title { font-weight: 700; font-size: 1.5rem; color: var(--text-main); }
    .brand-subtitle { font-size: 0.85rem; color: var(--text-muted); font-weight: 400; }

    /* --- KANBAN LAYOUT --- */
    .kanban-board {
        display: flex;
        height: calc(100vh - 80px);
        padding: 1.5rem;
        gap: 2rem;
    }

    .k-column {
        flex: 1;
        background: rgba(0,0,0,0.02); /* Leggerissimo sfondo colonna */
        border-radius: 30px;
        display: flex; flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .k-header {
        padding: 1.5rem;
        font-weight: 700; font-size: 1.1rem;
        display: flex; justify-content: space-between; align-items: center;
        background: var(--surface-color);
        border-bottom: 1px solid var(--border-color);
    }

    .badge-count {
        background: var(--text-main); color: var(--surface-color);
        padding: 5px 12px; border-radius: 20px; font-size: 0.9rem;
    }

    .k-body {
        flex: 1; overflow-y: auto; padding: 1.5rem;
    }

    /* --- CARD ORDINE (Stile Tavolo "Galleggiante") --- */
    .order-card {
        background: var(--surface-color);
        border-radius: var(--card-radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px var(--shadow-color);
        border: 1px solid var(--border-color);
        transition: transform 0.2s;
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }
    
    .order-card:hover { transform: translateY(-5px); }

    /* Header Card */
    .card-top { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 1rem; padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table-badge {
        background: var(--primary); color: white;
        padding: 6px 15px; border-radius: 12px;
        font-weight: 700; font-size: 1.1rem;
        box-shadow: 0 4px 10px rgba(255, 159, 67, 0.3);
    }

    .time-badge { color: var(--text-muted); font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }

    /* Lista Piatti */
    .dish-row {
        display: flex; align-items: flex-start;
        margin-bottom: 12px; font-size: 1rem; color: var(--text-main);
    }
    
    .qty-capsule {
        background: var(--bg-color);
        color: var(--text-main);
        font-weight: 700;
        min-width: 35px; height: 35px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px; margin-right: 12px;
        border: 1px solid var(--border-color);
    }

    /* Bottoni Azione (Pillole grandi) */
    .btn-action {
        width: 100%; border: none; padding: 12px;
        border-radius: 50px; font-weight: 700; font-size: 0.9rem;
        cursor: pointer; margin-top: 1rem;
        transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px;
    }

    /* Varianti Bottoni */
    .btn-start { background: var(--new-order-bg); color: var(--new-order-text); }
    .btn-start:hover { background: var(--new-order-text); color: white; }

    .btn-done { background: var(--prep-order-bg); color: var(--prep-order-text); }
    .btn-done:hover { background: var(--prep-order-text); color: white; }

    /* Utility */
    .theme-toggle {
        cursor: pointer; width: 40px; height: 40px;
        border-radius: 50%; background: var(--bg-color);
        display: flex; align-items: center; justify-content: center;
        color: var(--text-main); border: 1px solid var(--border-color);
    }
    
    @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    
    /* Scrollbar */
    .k-body::-webkit-scrollbar { width: 6px; }
    .k-body::-webkit-scrollbar-thumb { background-color: var(--border-color); border-radius: 10px; }
</style>

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
// --- GESTIONE TEMA ---
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    
    if (body.getAttribute('data-theme') === 'dark') {
        body.setAttribute('data-theme', 'light');
        icon.classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem('theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark');
        icon.classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem('theme', 'dark');
    }
}
if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
}

// --- GESTIONE AUDIO ---
let audioActive = false;
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playSound() {
    if(!audioActive) return;
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
    const icon = document.querySelector('#btn-audio i');
    const btn = document.getElementById('btn-audio');
    
    if(audioActive) {
        icon.className = 'fas fa-volume-up';
        btn.style.borderColor = 'var(--primary)';
        btn.style.color = 'var(--primary)';
        if(audioCtx.state === 'suspended') audioCtx.resume();
        playSound();
    } else {
        icon.className = 'fas fa-volume-mute';
        btn.style.borderColor = 'var(--border-color)';
        btn.style.color = 'var(--text-main)';
    }
}

// --- LOGICA ORDINI ---
document.addEventListener("DOMContentLoaded", () => {
    caricaOrdini();
    setInterval(caricaOrdini, 5000);
});

let lastCount = 0;

function caricaOrdini() {
    fetch('../api/leggi_ordini_cucina.php')
    .then(res => res.json())
    .then(data => {
        const colNew = document.getElementById('col-new');
        const colPrep = document.getElementById('col-prep');
        
        let htmlNew = '';
        let htmlPrep = '';
        let cNew = 0;
        let cPrep = 0;

        data.forEach(ordine => {
            // Lista Piatti
            let piattiHtml = '';
            ordine.piatti.forEach(p => {
                piattiHtml += `
                    <div class="dish-row">
                        <div class="qty-capsule">${p.qta}</div>
                        <div style="padding-top:5px;">${p.nome}</div>
                    </div>`;
            });

            // Card HTML (Stile Tavolo)
            const card = `
                <div class="order-card">
                    <div class="card-top">
                        <div class="table-badge">Tavolo ${ordine.tavolo}</div>
                        <div class="time-badge"><i class="far fa-clock"></i> ${ordine.ora}</div>
                    </div>
                    <div>${piattiHtml}</div>
                    ${getButton(ordine.id_ordine, ordine.stato)}
                </div>`;

            if(ordine.stato === 'in_coda') {
                htmlNew += card;
                cNew++;
            } else if (ordine.stato === 'in_preparazione') {
                htmlPrep += card;
                cPrep++;
            }
        });

        // Audio Notifica
        if (cNew > lastCount) playSound();
        lastCount = cNew;

        // Empty States belli
        const emptyState = (icon, text) => `
            <div class="text-center py-5" style="opacity:0.3;">
                <i class="fas ${icon} fa-3x mb-3" style="color:var(--text-muted)"></i>
                <h5 style="color:var(--text-muted)">${text}</h5>
            </div>`;

        colNew.innerHTML = cNew > 0 ? htmlNew : emptyState('fa-bell-slash', 'Nessun nuovo ordine..');
        colPrep.innerHTML = cPrep > 0 ? htmlPrep : emptyState('fa-fire-alt', 'Nessun ordine in preparazione..');

        document.getElementById('count-new').innerText = cNew;
        document.getElementById('count-prep').innerText = cPrep;
    })
    .catch(err => console.error("Error:", err));
}

function getButton(id, stato) {
    if(stato === 'in_coda') {
        return `<button class="btn-action btn-start" onclick="cambiaStato(${id}, 'in_preparazione')">
                    INIZIA COTTURA <i class="fas fa-arrow-right"></i>
                </button>`;
    } else {
        return `<button class="btn-action btn-done" onclick="cambiaStato(${id}, 'pronto')">
                    <i class="fas fa-check"></i> ORDINE PRONTO
                </button>`;
    }
}

function cambiaStato(id, nuovoStato) {
    fetch('../api/cambia_stato_ordine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_ordine: id, nuovo_stato: nuovoStato })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) caricaOrdini();
        else alert("Errore: " + data.message);
    });
}
</script>

<?php include '../include/footer.php'; ?>