let carrello = {}; 
let totaleSoldi = 0;
let totalePezzi = 0;

//filtro categoria
function filtraCategoria(idCat, elemento) {
    // highlight della categoria
    document.querySelectorAll('.link-categoria').forEach(el => el.classList.remove('active'));
    elemento.classList.add('active');

    // visualizza piatti della categoria
    let piatti = document.querySelectorAll('.item-prodotto');
    piatti.forEach(piatto => {
        if (idCat === 'all' || piatto.getAttribute('data-cat') == idCat) {
            piatto.style.display = 'block';
        } else {
            piatto.style.display = 'none';
        }
    });
}

// gestione add/remove dell'oggetto
function gestisciCarrello(id, delta, prezzo, nome) {
    let input = document.getElementById('q-' + id);
    let valAttuale = parseInt(input.value);
    let valNuovo = valAttuale + delta;

    if (valNuovo >= 0) {
        input.value = valNuovo;
        totaleSoldi += (delta * prezzo);
        totalePezzi += delta;
        
        document.getElementById('soldi-header').innerText = totaleSoldi.toFixed(2);
        document.getElementById('pezzi-header').innerText = totalePezzi;

        //aggiornamento dell'oggetto carrello
        if(!carrello[id]) {
            carrello[id] = { nome: nome, qta: 0, prezzo: prezzo };
        }
        carrello[id].qta = valNuovo;
        
        if(carrello[id].qta === 0) delete carrello[id];
    }
}

function aggiornaModale() {
    let container = document.getElementById('corpo-carrello');
    let html = '<ul class="list-group list-group-flush">';
    let haElementi = false;

    for (const [id, item] of Object.entries(carrello)) {
        if(item.qta > 0) {
            haElementi = true;
            let parziale = (item.qta * item.prezzo).toFixed(2);
            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${item.nome}</span> 
                        <span class="badge bg-secondary rounded-pill ms-2">x${item.qta}</span>
                    </div>
                    <span>${parziale}€</span>
                </li>
            `;
        }
    }
    html += '</ul>';

    if(!haElementi) {
        container.innerHTML = '<div class="text-center py-4"><h3 class="text-muted"></h3><p>Non hai ancora scelto nulla!</p></div>';
    } else {
        container.innerHTML = html;
    }
    
    document.getElementById('totale-modale').innerText = totaleSoldi.toFixed(2);
}
