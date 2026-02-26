# Documentazione PHP: Layout delle Dashboards 

Le "Dashboards" sono le fondamenta visive per i tre impieghi separati del ristorante. Contengono molta UI HTML intarsiata dal linguaggio server-side PHP.

## 1. `dashboards/manager.php` (Terminale d'Amministrazione)
**Posizione:** `/dashboards/manager.php`

* **Meccanica Navigazionale:** 
  Utilizza ID `<div id="page-tavoli" class="page-section">` vs `<div id="page-menu">`. Questa pagina possiede DUE facce interscambiabili al cliccare JavaScript la sidebar. Tuttavia scarica fisicamente ed espone all'utente manager il listino prezzi e i tasti dei tavoli al momento 0. Tutta farina di interrogazione ciclica SQL (Fetch e While loop su un `SELECT * dall'abito db alimenti`).

* **Componenti Essenziali e Relazioni PHP:**
  - **Griglia HTML Vuota (`#tavoli-grid`)**: Questo box all'avvio è vuotissimo! Viene lasciato alle grinfie dei Fetch di JS (`manager.js`) riempirlo tramite array. Nessun PHP elabora le liste Tavoli al momento dello startup della Dashboard a eccezion fatta di `include/modals/manager_modals.php` posizionati dormienti alla fine. 
  - **Formulaire d'Amministrazione (Piatto)**: Un enorme pezzo base form interseca query native e grezze `$conn->query("SELECT * FROM categorie")` disegnando le schede prodotto a tendine HTML Menu `<select>`, iniettando al volo opzioni stampate. L'id PHP passa ai bottoni la pre-carica (Via meta Custom-Data html, `data-img="xxx"`) agevolando la controparte Javascript di cui ne pesca i tratti e compila ai popup d'errore o successo.

## 2. `dashboards/cucina.php` (Board Kanban per gli Chef)
**Posizione:** `/dashboards/cucina.php`

* **Sintesi:** È la facciata più scarna del gruppo. Una board con 2 Divs Colonne grigie e un layout fluid flexbox che scompatta il lato visivo. 
* **Relazioni al JS:** La stesura si avvale unicamente di PHP limitato allo stretch di `conn.php` iniziale. Non ci son Queries interne al DOM. Tutti gli arrivi "Nuovi" (In Attesa, In Preparazione) sgorgano da interrogazioni JS massive sul file `api/cucina/...` che spareranno il map ciclico innerHTML riversando i ticket completi sul div `col-new` o `col-prep`. Contiene soltanto icone FontAwesome pre-renderizzate fisse alla base dei titoletti divisori colonna.

## 3. `dashboards/tavolo.php` (E-Menu e Ordinazioni Clienti)
**Posizione:** `/dashboards/tavolo.php`

* **Sintesi:** Il menù pubblico. A causa dell'incapacità dell'utente base si è scelti la strada SSR (Server Side Render). PHP legge TUTTO l'inverosimile (Categorie e Piatti DB) in fase di parsing paginone e lo vomita staticamente a scorrimento in una mastodontica Griglia CSS Responsive (`div class='item-prodotto'`).
* **Architettura del Display Array:**
  - Carica la tabella `$categorie`. Poi in un Loop infinito, per *Ogni Categoria* chiede al MySQL `$alimenti` corrispondenti per quella famiglia, e li incasella generando "Blocchetti Piastrella". 
  - Dentro alla costruzione della piastra PHP (`<div class="card-prodotto">`), il codice infonde informazioni tattiche dentro `data-*` (Es: `data-nome="<?= $a['nome_piatto'] ?>"`). Queste infusi di dati passano la torcia allo script frontend `.js` che secerne i filtri ricerca semantici nascondendo col flex la display box in assenza di incroci, eliminando sprechi di interpellazioni internet costose all'API backend per i sortings. 

## 4. `include/modals/...` (Popup e Sovrimpressioni Modali)
**Posizione:** `/include/modals/manager_modals.php` e `tavolo_modals.php`

* **Significato Strutturale:** Sono moduli separati passivamente inculdati (`include 'modals/...'`) al fondo schiena dei file padre delle root (Tavolo e Manager) prima di chiudere i Tag d'ombra dell'`html`.
* **Cosa Contengono:** 
  - *Manager:* Le finestre grigie che compaiono dal buio per "Nuovo Tavolo" o "Modifica Piatto", corredate di form e chiamate ajax al fondo via `onclick()`.
  - *Tavolo:* Finestre di vitale priorità tra cui `modalFiltri` (Scelta intolleranze nutrizionali generate da array PHP grezzo), `modalZoom` (esploso gigante descrittivo di una pasta), e `modalCarrello`, un "ghost box" i cui corpi ul/li risiedono vuoti pronti ad esser manipolati dal Carrello temporaneo volatile in ram di *tavolo.js* prima dell'immissione ultima in speda allo *Store Checkout MySQL*.
