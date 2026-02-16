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
    document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
}

function apriModalModifica(btn) {
    const id = btn.getAttribute('data-id');
    const nome = btn.getAttribute('data-nome');
    const desc = btn.getAttribute('data-desc');
    const prezzo = btn.getAttribute('data-prezzo');
    const cat = btn.getAttribute('data-cat');
    const img = btn.getAttribute('data-img');
    const allergeniString = btn.getAttribute('data-allergeni'); 

    document.getElementById('mod_id').value = id;
    document.getElementById('mod_nome').value = nome;
    document.getElementById('mod_desc').value = desc;
    document.getElementById('mod_prezzo').value = prezzo;
    document.getElementById('mod_cat').value = cat;

    const preview = document.getElementById('preview_img');
    if(img) {
        preview.src = "../imgs/prodotti/" + img;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    const checkboxes = document.querySelectorAll('.mod-allergeni');
    checkboxes.forEach(cb => cb.checked = false);

    // Se ci sono allergeni salvati, spunta quelli giusti
    if (allergeniString && allergeniString.trim() !== "") {
        // Divide la stringa in array e pulisce gli spazi
        const allergeniArray = allergeniString.split(',').map(s => s.trim());
        
        checkboxes.forEach(cb => {
            if (allergeniArray.includes(cb.value)) {
                cb.checked = true;
            }
        });
    }
    const modalElement = document.getElementById('modalModifica');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
