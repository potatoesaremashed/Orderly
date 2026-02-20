# Orderly - Guida al Progetto per Project Manager

Orderly è un'applicazione web basata su PHP, MySQL, JavaScript e CSS per la gestione di ordini all'interno di un ristorante tramite tre componenti principali: **Menu Digitale (Tavolo)**, **Pannello Cucina** e **Pannello Manager**.

## Architettura del Progetto
Il progetto segue un'architettura applicativa tradizionale con rendering server-side iniziale (PHP) e asincronia (AJAX/Fetch) per la dinamicità lato client senza ricaricamento pagina.

### 1. Database e Base
- **`include/conn.php`**: gestisce la connessione con il db MySQL `ristorante_db`. Tutte le dipendenze passano da qui.
- **`index.php`**: il cancello d'ingresso (Login). Riconosce il ruolo inserito nei campi e assegna i permessi di sessione (manager, cuoco, tavolo).
- **`logout.php`**: distrugge in totale sicurezza i token temporanei della sessione.
- **`include/header.php` / `footer.php`**: la struttura "scatola" HTML, in cui passano l'inclusione di Bootstrap e i meta-tag responsivi.

### 2. Dashboard Web
Le viste principali che compongono il fronte utente e amministratore.
- **`dashboards/tavolo.php`**: il menu per il cliente. Tramite questa dashboard, il cliente guarda le categorie, le foto e prezza i prodotti (compresi dettagli allergeni e varianti cucina). È affiancato e animato in tempo reale da `js/tavolo.js` per il carrello virtuale lato client. 
- **`dashboards/cucina.php`**: la vista operativa ad uso della brigata di cucina, è una **Kanban board** reale che si auto-aggiorna senza refresh tramite ping asincroni in AJAX/Fetch eseguiti dal suo script `js/cucina.js`. 
- **`dashboards/manager.php`**: la dashboard direzionale (Backoffice), dove si possono manipolare (aggiungere/modificare/rimuovere) i prodotti, descrizioni, foto, categorie. 

### 3. API Interne (Backend Processors)
Tutte le dinamiche asíncrone passano fluidamente tramite servizi web racchiusi dentro `api/`:
- **Workflow Carrello Cliente**: `aggiungi_al_carrello.php`, `rimuovi_dal_carrello.php` e `get_carrello.php` mantengono sicuro lo stato del carrello in sessioni MySQL, proteggendolo da perdita dati in caso di refresh fortuito del dispositivo del cliente.
- **Workflow Ordine e Cucina**: `invia_ordine.php` innesca una transazione "tutto-o-niente" (transazione sicura in DB). `leggi_ordini_cucina.php` (richiamata a ciclo continuo ogni x secondi) e `cambia_stato_ordine.php` aggiornano la lavagna visibile alla mensa come In Attesa -> in Preparazione -> Pronto.
- **Workflow Manageriali**: file CRUD standard per aggiungere e modificare piatti (anche caricarne file fisici-immagine tramite `aggiungi_piatto.php` e `modifica_piatto.php`).

### 4. JavaScript e UI
Nella cartella `js/` risiede l'intero polmone asincrono. 
- `gestioneCarrello.js`: espone le istruzioni di interfacciamento rapido col database per click, carrello e conferme.
- Ogni dashboard possiede assieme al suo file JS omonimo anche un foglio stile CSS in `css/` (es: `tavolo.css`, `cucina.css`, `manager.css`) che gestiscono la responsività, micro-animazioni e dark mode estesa.

## Manutenibilità e Sicurezza
1. **Controllo di Sessione (`$_SESSION['ruolo']`)**: Nessuna dashboard è visibile se forzata via URL. Tutte dispongono del fallback auto-redirigente verso index in mancanza del ruolo corretto.
2. **Prepared Statements in MySQL**: I dati passati ai database, in particolare per categorie e stati, passano spesso tramite statements sicuri per mitigare minacce di SQLInjection.

---
*Progetto analizzato e documentato in data odierna.*
