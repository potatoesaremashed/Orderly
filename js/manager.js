/**
 * =========================================
 * FILE: js/manager.js
 * =========================================
 * Logica lato cliente per la dashboard del Manager.
 * 
 * Funzionalità:
 * - Toggle tema chiaro/scuro con salvataggio in localStorage
 * - Apertura modale di modifica piatto con pre-compilazione dei campi
 */

// =============================================
// GESTIONE TEMA (DARK / LIGHT MODE)
// =============================================

/**
 * Alterna tra tema chiaro e scuro.
 * Aggiorna l'icona e salva la preferenza nel localStorage.
 */
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    const isDark = body.getAttribute('data-theme') === 'dark';

    // Applica il nuovo tema
    body.setAttribute('data-theme', isDark ? 'light' : 'dark');
    // Aggiorna l'icona: luna ↔ sole
    icon.classList.replace(isDark ? 'fa-sun' : 'fa-moon', isDark ? 'fa-moon' : 'fa-sun');
    // Salva la preferenza
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}

// Caricamento tema salvato: se l'utente aveva selezionato il tema scuro, lo riapplica
if (localStorage.getItem('theme') === 'dark') {
    document.body.setAttribute('data-theme', 'dark');
    document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
}

// =============================================
// GESTIONE MODALE MODIFICA PIATTO
// =============================================

/**
 * Apre il modale di modifica piatto e pre-compila tutti i campi
 * con i dati del piatto selezionato.
 * 
 * I dati vengono letti dai data-attributes del bottone "Modifica" cliccato.
 * 
 * @param {HTMLElement} btn - Il bottone "Modifica" cliccato (contiene i data-attributes)
 */
function apriModalModifica(btn) {
    // Recupera i dati del piatto dai data-attributes del bottone
    const id = btn.getAttribute('data-id');             // ID del piatto
    const nome = btn.getAttribute('data-nome');         // Nome del piatto
    const desc = btn.getAttribute('data-desc');         // Descrizione
    const prezzo = btn.getAttribute('data-prezzo');     // Prezzo
    const cat = btn.getAttribute('data-cat');           // ID della categoria
    const img = btn.getAttribute('data-img');           // Nome file immagine
    const allergeniString = btn.getAttribute('data-allergeni'); // Stringa allergeni (es: "Glutine,Uova")

    // Pre-compila i campi del form nel modale
    document.getElementById('mod_id').value = id;
    document.getElementById('mod_nome').value = nome;
    document.getElementById('mod_desc').value = desc;
    document.getElementById('mod_prezzo').value = prezzo;
    document.getElementById('mod_cat').value = cat;

    // Mostra l'anteprima dell'immagine attuale (se esiste)
    const preview = document.getElementById('preview_img');
    if (img) {
        preview.src = "../imgs/prodotti/" + img;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    // Gestione checkbox allergeni:
    // 1. Prima deseleziona tutte le checkbox
    const checkboxes = document.querySelectorAll('.mod-allergeni');
    checkboxes.forEach(cb => cb.checked = false);

    // 2. Se il piatto ha allergeni salvati, spunta quelli corrispondenti
    if (allergeniString && allergeniString.trim() !== "") {
        // Divide la stringa "Glutine,Uova" in un array ["Glutine", "Uova"]
        const allergeniArray = allergeniString.split(',').map(s => s.trim());

        // Per ogni checkbox, se il suo valore è nell'array, la spunta
        checkboxes.forEach(cb => {
            if (allergeniArray.includes(cb.value)) {
                cb.checked = true;
            }
        });
    }

    // Apri il modale Bootstrap
    const modalElement = document.getElementById('modalModifica');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
