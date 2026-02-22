/**
 * =========================================
 * FILE: js/gestioneCarrello.js
 * =========================================
 * Questo script gestisce la logica del carrello per il cliente (tavolo).
 * Sincronizza le azioni dell'utente (aggiungi/rimuovi piatto) con il 
 * Database tramite chiamate API asincrone (Fetch).
 * 
 * JUNIOR TIP: In un'app moderna, l'interfaccia non deve ricaricarsi.
 * Usiamo 'fetch' per parlare con il server "dietro le quinte" e poi
 * aggiorniamo solo i pezzi di HTML necessari.
 * 
 * NOTE: Questo file gestisce la persistenza del carrello nel DB,
 * mentre tavolo.js gestisce la visualizzazione dinamica del menu.
 */

document.addEventListener("DOMContentLoaded", function () {
    // Al caricamento della pagina, aggiorniamo subito il carrello 
    // per mostrare eventuali piatti già aggiunti in precedenza.
    aggiornaInterfacciaCarrello();

    // Gestione dell'invio dell'ordine alla cucina
    const btnInvia = document.getElementById('btn-invia-ordine');
    if (btnInvia) {
        btnInvia.addEventListener('click', function () {
            // Chiediamo conferma all'utente prima di inviare
            if (!confirm("Vuoi confermare l'ordine e inviarlo in cucina?")) return;

            // Feedback visivo: disabilita il bottone e mostra uno spinner
            btnInvia.disabled = true;
            btnInvia.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Invio...';

            // Chiamata all'API per finalizzare l'ordine
            fetch('../api/tavolo/invia_ordine.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ordine inviato con successo! La cucina inizierà la preparazione.");

                        // Chiudiamo la modale del carrello (usando l'istanza Bootstrap 5)
                        const modalEl = document.getElementById('cartModal');
                        if (modalEl) {
                            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modalInstance.hide();
                        }

                        // Svuotiamo visivamente il carrello chiamando l'update
                        aggiornaInterfacciaCarrello();
                    } else {
                        alert("Errore: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Errore invio ordine:', error);
                    alert("Errore di comunicazione con il server.");
                })
                .finally(() => {
                    // Ripristiniamo il bottone
                    btnInvia.disabled = false;
                    btnInvia.innerText = 'Invia Ordine';
                });
        });
    }
});

/**
 * Funzione principale per aggiungere o rimuovere quantità di un piatto.
 * @param {number} id - ID dell'alimento
 * @param {number} delta - +1 (aggiungi) o -1 (rimuovi)
 * @param {number} prezzo - Prezzo del prodotto (solo per logica UI veloce)
 * @param {string} nome - Nome del prodotto
 */
function gestisciCarrello(id, delta, prezzo, nome) {
    const formData = new FormData();
    formData.append('id_alimento', id);

    // Scegliamo l'endpoint API in base all'azione (+/-)
    let url = (delta > 0) ? '../api/tavolo/aggiungi_al_carrello.php' : '../api/tavolo/rimuovi_dal_carrello.php';
    if (delta > 0) formData.append('quantita', 1);

    fetch(url, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Se il DB è aggiornato, rinfreschiamo TUTTA l'interfaccia del carrello
                aggiornaInterfacciaCarrello();
            } else {
                alert("Errore: " + data.message);
            }
        })
        .catch(error => console.error('Errore rete carrello:', error));
}

/**
 * La funzione "cuore": scarica lo stato attuale del carrello dal DB
 * e ricostruisce l'HTML della modale e i contatori nella pagina.
 */
function aggiornaInterfacciaCarrello() {
    fetch('../api/tavolo/get_carrello.php')
        .then(res => res.json())
        .then(data => {
            let totaleSoldi = 0;
            let totalePezzi = 0;
            let carrelloHtml = '<ul class="list-group list-group-flush">';
            let haElementi = false;

            // 1. Reset di tutti i contatori quantità nella lista piatti a 0
            document.querySelectorAll("input[id^='q-']").forEach(el => el.value = 0);

            // 2. Costruzione dinamica della lista piatti nel carrello
            data.forEach(item => {
                haElementi = true;
                let qta = parseInt(item.quantita);
                let prezzo = parseFloat(item.prezzo);
                let parziale = qta * prezzo;

                totaleSoldi += parziale;
                totalePezzi += qta;

                // Aggiorniamo il numeretto visibile sulla card del piatto
                let inputGrid = document.getElementById('q-' + item.id_alimento);
                if (inputGrid) inputGrid.value = qta;

                // Generiamo la riga per la modale carrello
                carrelloHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">${item.nome_piatto}</div>
                        <small class="text-muted">${prezzo.toFixed(2)}€ cad.</small>
                    </div>
                    <span class="badge bg-primary rounded-pill me-3">x${qta}</span>
                    <span class="fw-bold">${parziale.toFixed(2)}€</span>
                    
                    <div class="btn-group btn-group-sm ms-3">
                        <button class="btn btn-outline-secondary" onclick="gestisciCarrello(${item.id_alimento}, -1, ${prezzo}, '${item.nome_piatto}')">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="gestisciCarrello(${item.id_alimento}, 1, ${prezzo}, '${item.nome_piatto}')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </li>`;
            });

            carrelloHtml += '</ul>';

            // 3. Gestione UI carrello vuoto vs pieno
            const container = document.getElementById('corpo-carrello');
            const btnInvia = document.getElementById('btn-invia-ordine');

            if (!haElementi) {
                container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                    <p>Il carrello è vuoto. Scegli qualcosa di buono!</p>
                </div>`;
                if (btnInvia) btnInvia.disabled = true;
            } else {
                container.innerHTML = carrelloHtml;
                if (btnInvia) btnInvia.disabled = false;
            }

            // 4. Aggiornamento dei totali (Header e Modale)
            document.getElementById('soldi-header') && (document.getElementById('soldi-header').innerText = totaleSoldi.toFixed(2));
            document.getElementById('pezzi-header') && (document.getElementById('pezzi-header').innerText = totalePezzi);
            document.getElementById('totale-modale') && (document.getElementById('totale-modale').innerText = totaleSoldi.toFixed(2));
        })
        .catch(err => console.error("Errore sync carrello:", err));
}
