<?php
/**
 * Dashboard Cucina
 * ----------------
 * Interfaccia dedicata allo staff di cucina.
 * - Visualizza due colonne: "In Arrivo" e "In Preparazione".
 * - Utilizza JS (setInterval) per aggiornare la vista ogni 5 secondi senza ricaricare la pagina.
 * - Permette di avanzare lo stato degli ordini tramite bottoni dinamici.
 */
session_start();
// Simulazione login cucina se non presente
if(!isset($_SESSION['ruolo'])) {
    $_SESSION['ruolo'] = 'cucina';
}
include '../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-fire"></i> Monitor Cucina</h2>
        <span class="badge bg-success" id="indicatore-live">LIVE</span>
    </div>

    <div class="row">
        <div class="col-md-6 border-end">
            <h4 class="text-danger mb-3"><i class="bi bi-bell-fill"></i> In Arrivo (Da Accettare)</h4>
            <div id="colonna-in-coda" class="row g-3">
                <div class="text-center text-muted mt-5">In attesa di ordini...</div>
            </div>
        </div>

        <div class="col-md-6 ps-md-4">
            <h4 class="text-primary mb-3"><i class="bi bi-stopwatch-fill"></i> In Preparazione</h4>
            <div id="colonna-in-preparazione" class="row g-3">
                </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    caricaOrdini();
    // Aggiorna ogni 5 secondi
    setInterval(caricaOrdini, 5000);
});

function caricaOrdini() {
    fetch('../api/leggi_ordini_cucina.php')
    .then(res => res.json())
    .then(data => {
        const colCoda = document.getElementById('colonna-in-coda');
        const colPrep = document.getElementById('colonna-in-preparazione');
        
        let htmlCoda = '';
        let htmlPrep = '';

        if (data.length === 0) {
            document.getElementById('indicatore-live').classList.remove('bg-success');
            document.getElementById('indicatore-live').classList.add('bg-secondary');
        } else {
            document.getElementById('indicatore-live').classList.add('bg-success');
            document.getElementById('indicatore-live').classList.remove('bg-secondary');
        }

        data.forEach(ordine => {
            // Costruisci la lista dei piatti
            let listaPiatti = '<ul class="list-unstyled mb-3">';
            ordine.piatti.forEach(p => {
                listaPiatti += `<li class="fs-5"><strong>${p.qta}x</strong> ${p.nome}</li>`;
            });
            listaPiatti += '</ul>';

            // Costruisci la Card HTML
            let cardHtml = `
                <div class="col-12">
                    <div class="card shadow-sm border-${ordine.stato === 'in_coda' ? 'danger' : 'primary'}">
                        <div class="card-header d-flex justify-content-between align-items-center ${ordine.stato === 'in_coda' ? 'bg-danger text-white' : 'bg-primary text-white'}">
                            <h5 class="m-0">Tavolo ${ordine.tavolo}</h5>
                            <span class="fs-6"><i class="bi bi-clock"></i> ${ordine.ora}</span>
                        </div>
                        <div class="card-body">
                            ${listaPiatti}
                            <div class="d-grid">
                                ${generareBottoni(ordine.id_ordine, ordine.stato)}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            if (ordine.stato === 'in_coda') {
                htmlCoda += cardHtml;
            } else if (ordine.stato === 'in_preparazione') {
                htmlPrep += cardHtml;
            }
        });

        // Aggiorna il DOM solo se è cambiato (per evitare flickering si potrebbe migliorare, ma per ora sovrascriviamo)
        colCoda.innerHTML = htmlCoda || '<div class="alert alert-secondary text-center">Nessun ordine in coda</div>';
        colPrep.innerHTML = htmlPrep || '<div class="alert alert-light text-center border">Niente ai fornelli</div>';
    })
    .catch(err => console.error("Errore polling:", err));
}

function generareBottoni(id, stato) {
    if (stato === 'in_coda') {
        return `<button class="btn btn-warning btn-lg fw-bold" onclick="cambiaStato(${id}, 'in_preparazione')">
                    <i class="bi bi-fire"></i> INIZIA A CUCINARE
                </button>`;
    } else if (stato === 'in_preparazione') {
        return `<button class="btn btn-success btn-lg fw-bold" onclick="cambiaStato(${id}, 'pronto')">
                    <i class="bi bi-check-lg"></i> ORDINE PRONTO
                </button>`;
    }
    return '';
}

function cambiaStato(id, nuovoStato) {
    fetch('../api/cambia_stato_ordine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_ordine: id, nuovo_stato: nuovoStato })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            caricaOrdini(); // Ricarica subito per vedere l'effetto
        } else {
            alert("Errore: " + data.message);
        }
    });
}
</script>

<?php include '../include/footer.php'; ?>