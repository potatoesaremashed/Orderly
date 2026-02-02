/**
 * Gestione Carrello - Orderly
 * Sincronizza le azioni dell'utente con il Database tramite API.
 */

/**
 * Gestione Carrello - Frontend Controller
 * ---------------------------------------
 * Questo script gestisce l'intera logica client-side dell'ordine.
 * Funzionalità principali:
 * - gestisciCarrello(id, delta): Aggiunge/Rimuove piatti chiamando le API asincrone (fetch).
 * - aggiornaInterfacciaCarrello(): Scarica lo stato reale dal DB e ridisegna la modale e i contatori.
 * - Listener 'btn-invia-ordine': Gestisce il click di conferma, inviando l'ordine in cucina.
 */

document.addEventListener("DOMContentLoaded", function() {
    // Al caricamento della pagina, aggiorniamo subito il carrello 
    // per mostrare eventuali ordini precedenti salvati nel DB.
    aggiornaInterfacciaCarrello();
});

/**
 * Funzione principale chiamata dai pulsanti +/- nelle card dei prodotti.
 * @param {number} id - ID dell'alimento
 * @param {number} delta - +1 per aggiungere, -1 per rimuovere
 * @param {number} prezzo - Prezzo del prodotto (usato solo per UI immediata, il vero calcolo è backend)
 * @param {string} nome - Nome del prodotto
 */
function gestisciCarrello(id, delta, prezzo, nome) {
    const formData = new FormData();
    formData.append('id_alimento', id);

    // Determina quale API chiamare
    let url = '';
    if (delta > 0) {
        url = '../api/aggiungi_al_carrello.php';
        formData.append('quantita', 1); // Aggiungiamo sempre 1 alla volta col click
    } else {
        url = '../api/rimuovi_dal_carrello.php';
        // La rimozione solitamente non richiede quantità se rimuove un'unità o l'intero item,
        // dipende dall'implementazione PHP. Assumiamo che rimuova 1 unità.
    }

    // Feedback visivo immediato (opzionale: potresti mettere un loader qui)
    // console.log(`Richiesta inviata: ${nome} (ID: ${id})`);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Se il server conferma, aggiorniamo la vista leggendo i dati reali dal DB
            aggiornaInterfacciaCarrello();
            
            // Opzionale: Mostra un piccolo toast o notifica di successo
        } else {
            console.error("Errore API:", data.message);
            alert("Impossibile aggiornare il carrello: " + (data.message || "Errore sconosciuto"));
        }
    })
    .catch(error => {
        console.error('Errore di rete:', error);
    });
}

/**
 * Recupera lo stato attuale del carrello dal server e aggiorna l'HTML.
 */
function aggiornaInterfacciaCarrello() {
    fetch('../api/get_carrello.php')
    .then(res => res.json())
    .then(data => {
        let totaleSoldi = 0.00;
        let totalePezzi = 0;
        let carrelloHtml = '<ul class="list-group list-group-flush">';
        let haElementi = false;

        // 1. Reset visivo di tutti i contatori nella griglia prodotti (se presenti)
        // Cerca tutti gli input che iniziano con 'q-' e li mette a 0
        document.querySelectorAll("input[id^='q-']").forEach(el => el.value = 0);

        // 2. Itera sui dati ricevuti dal DB
        data.forEach(item => {
            haElementi = true;
            let qta = parseInt(item.quantita);
            let prezzo = parseFloat(item.prezzo);
            let parziale = qta * prezzo;
            
            totaleSoldi += parziale;
            totalePezzi += qta;

            // Aggiorna l'input specifico nella griglia prodotti (se l'utente lo sta vedendo)
            let inputGrid = document.getElementById('q-' + item.id_alimento);
            if(inputGrid) {
                inputGrid.value = qta;
            }

            // Costruisci l'elemento della lista per la Modale
            carrelloHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">${item.nome_piatto}</div>
                        <small class="text-muted">${prezzo.toFixed(2)}€ cad.</small>
                    </div>
                    <span class="badge bg-primary rounded-pill me-3">x${qta}</span>
                    <span class="fw-bold">${parziale.toFixed(2)}€</span>
                    
                    <button class="btn btn-sm btn-outline-danger ms-3" onclick="gestisciCarrello(${item.id_alimento}, -1, ${prezzo}, '${item.nome_piatto}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </li>
            `;
        });

        carrelloHtml += '</ul>';
        
        // 3. Gestione caso carrello vuoto
        let container = document.getElementById('corpo-carrello');
        if(!haElementi) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <p class="mt-3 text-muted">Il tuo carrello è vuoto.<br>Aggiungi qualcosa dal menu!</p>
                </div>
            `;
            // Disabilita il tasto "Invia Ordine" se vuoto
            let btnInvia = document.getElementById('btn-invia-ordine');
            if(btnInvia) btnInvia.disabled = true;

        } else {
            container.innerHTML = carrelloHtml;
            // Abilita il tasto "Invia Ordine"
            let btnInvia = document.getElementById('btn-invia-ordine');
            if(btnInvia) btnInvia.disabled = false;
        }

        // 4. Aggiorna i Totali (Header e Modale)
        // Header
        const headerSoldi = document.getElementById('soldi-header');
        const headerPezzi = document.getElementById('pezzi-header');
        if(headerSoldi) headerSoldi.innerText = totaleSoldi.toFixed(2);
        if(headerPezzi) headerPezzi.innerText = totalePezzi;

        // Modale
        const totaleModale = document.getElementById('totale-modale');
        if(totaleModale) totaleModale.innerText = totaleSoldi.toFixed(2);
    })
    .catch(err => console.error("Errore nel recupero carrello:", err));
}

// Aggiungi questo blocco dentro il document.addEventListener("DOMContentLoaded", ...) esistente
// subito dopo la chiamata a aggiornaInterfacciaCarrello();

    const btnInvia = document.getElementById('btn-invia-ordine');
    if (btnInvia) {
        btnInvia.addEventListener('click', function() {
            // Conferma semplice lato utente
            if(!confirm("Vuoi confermare l'ordine e inviarlo in cucina?")) {
                return;
            }

            // Disabilita il bottone per evitare doppi click
            btnInvia.disabled = true;
            btnInvia.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Invio...';

            fetch('../api/invia_ordine.php', {
                method: 'POST' // POST è più sicuro per azioni che modificano stati
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Ordine inviato con successo! La cucina inizierà la preparazione.");
                    
                    // Chiudi la modale (Bootstrap 5)
                    const modalEl = document.getElementById('cartModal'); // Assicurati che l'ID della modale sia corretto
                    if(modalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if(modalInstance) modalInstance.hide();
                    }
                    
                    // Aggiorna l'interfaccia (il carrello ora sarà vuoto perché l'ordine non è più 'in_attesa')
                    aggiornaInterfacciaCarrello();
                } else {
                    alert("Errore: " + data.message);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                alert("Errore di comunicazione con il server.");
            })
            .finally(() => {
                // Ripristina il bottone (se c'è stato errore o se la modale non si è chiusa)
                btnInvia.disabled = false;
                btnInvia.innerText = 'Invia Ordine';
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
    // ... (eventuale codice esistente) ...
    
    const btnInvia = document.getElementById('btn-invia-ordine');
    if(btnInvia){
        btnInvia.addEventListener('click', function() {
            if(!confirm("Confermi l'invio dell'ordine in cucina?")) return;

            // Feedback visivo: disabilita il bottone
            btnInvia.disabled = true;
            btnInvia.innerText = "Invio in corso...";

            fetch('../api/invia_ordine.php')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("Ordine inviato con successo!");
                    // Chiudi modale (se usi Bootstrap 5 vanilla)
                    var myModalEl = document.getElementById('cartModal');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    modal.hide();
                    
                    // Pulisci interfaccia
                    aggiornaInterfacciaCarrello();
                } else {
                    alert("Errore: " + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Errore di comunicazione.");
            })
            .finally(() => {
                // Ripristina bottone
                btnInvia.disabled = false;
                btnInvia.innerText = "CONFERMA E INVIA";
            });
        });
    }
});