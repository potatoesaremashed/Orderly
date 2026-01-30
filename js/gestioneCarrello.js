let carrello = {}; // This is our shopping cart memory (a list of items)
let totaleSoldi = 0; // The total money to pay
let totalePezzi = 0; // The total number of items picked

// This function filters the menu (like showing only Pizza or only Drinks)
function filtraCategoria(idCat, elemento) {
    // Make the button you clicked look "active" (highlighted)
    document.querySelectorAll('.link-categoria').forEach(el => el.classList.remove('active'));
    elemento.classList.add('active');

    // Go through every dish on the page
    let piatti = document.querySelectorAll('.item-prodotto');
    piatti.forEach(piatto => {
        // If we want 'all' or if the dish matches the category, show it!
        if (idCat === 'all' || piatto.getAttribute('data-cat') == idCat) {
            piatto.style.display = 'block';
        } else {
            // Otherwise, hide it
            piatto.style.display = 'none';
        }
    });
}

// This function runs when you click + or - on a dish
function gestisciCarrello(id, delta, prezzo, nome) {
    let input = document.getElementById('q-' + id); // Find the box with the number
    let valAttuale = parseInt(input.value);
    let valNuovo = valAttuale + delta; // Calculate the new number

    // We can't have less than 0 items!
    if (valNuovo >= 0) {
        input.value = valNuovo; // Update the number on screen
        totaleSoldi += (delta * prezzo); // Update total money
        totalePezzi += delta; // Update total count
        
        // Show the new totals at the top of the page
        document.getElementById('soldi-header').innerText = totaleSoldi.toFixed(2);
        document.getElementById('pezzi-header').innerText = totalePezzi;

        // Update our shopping cart memory
        if(!carrello[id]) {
            carrello[id] = { nome: nome, qta: 0, prezzo: prezzo };
        }
        carrello[id].qta = valNuovo;
        
        // If quantity is 0, remove it from the list completely
        if(carrello[id].qta === 0) delete carrello[id];
    }
}

// This function draws the list inside the "Cart" popup window
function aggiornaModale() {
    let container = document.getElementById('corpo-carrello');
    let html = '<ul class="list-group list-group-flush">';
    let haElementi = false; // A flag to check if cart is empty

    // Loop through every item in our cart memory
    for (const [id, item] of Object.entries(carrello)) {
        if(item.qta > 0) {
            haElementi = true;
            // Calculate price for just this item (quantity * price)
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

    // If the cart is empty, show a message
    if(!haElementi) {
        container.innerHTML = '<div class="text-center py-4"><h3 class="text-muted"></h3><p>Non hai ancora scelto nulla!</p></div>';
    } else {
        // Otherwise, show the list we built
        container.innerHTML = html;
    }
    
    // Update the final total price in the popup
    document.getElementById('totale-modale').innerText = totaleSoldi.toFixed(2);
}
