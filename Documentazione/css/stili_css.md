# Documentazione Fogli di Stile (CSS) dell'applicativo Orderly

Tutti i file CSS sono radicati nella cartella `/css/` e separati per logica e competenza, al fine di evitare stili contrastanti e mantenere ordine.

## 1. `common.css`
**Ruolo:** Foglio di stile globale e propedeutico. Contiene le fondamenta su cui poggiano gli altri CSS.
- **Variabili Globali (`:root`)**: Definisce i colori tematici principali del sistema (ad es. `--primary-color`, `--bg-light`, `--text-dark`). Il file intercetta anche la pseudo-classe `[data-theme="dark"]` variando le variabili per attuare il Tema Scuro automatico.
- **Resets**: Azzera margini e imposta font globali (Poppins) sui tag `html`, `body`.
- **Componenti Condivisi**:
  - `.sidebar-custom`: Regole fisiche, animazioni e z-index per il pannello di navigazione sinistro usato sia in Manager che in Cucina.
  - `.theme-toggle-sidebar`: Animazioni rotanti per il bottone del tema scuro posizionato nel footer.
  - Formattazione trasversale per `.toast-container` (i tip d'avviso verdi asincroni) e logiche scrollbar alleggerite.

## 2. `login.css`
**Ruolo:** Foglio di stile dedicato esclusivamente ed importato in `index.php`.
- **`.login-section`**: Crea lo split-screen (Schermo Est - Info/Loghi, Schermo Ovest - Form d'accesso).
- **`.role-btn` e `.role-card`**: Anima le carte enormi (Amministrazione, Cucina, Tavolo) cliccabili durante la scelta del ruolo. Applica hover effects sollevando la card (`transform: translateY(-5px)`).
- **`.form-control` & `.login-btn`**: Applica look "Glassmorphism" con bordi stondati morbidi (border-radius: 12px) e box-shadow soffuse per far risaltare gli input della password o del PIN.

## 3. `manager.css`
**Ruolo:** Disegno specifico per la Dashboard Gestionale, importato in `dashboards/manager.php`.
- **Card e Griglie Tavoli (`.tavolo-card`, `#tavoli-grid`)**: Definisce Responsive Grid system usando `grid-template-columns`. Le card dei tavoli cambiano bordo / colore a seconda dello stato (`badge-libero`, `badge-occupato`, `badge-riservato`).
- **Pulsanti Azione Rapida (`.btn-act`)**: Regole circoscritte per i mini-bottoncini tondi (Modifica, Elimina, Resetta Sessione) interni alla Card.
- **Tab Filter (`.filter-tab`)**: Bottoni stilizzati come "tabs" superiori per filtrare in tempo reale "Tutti, Liberi, Occupati, Riservati".
- **Modal (`.modal-content-custom`)**: Forza esteticamente i bordi dei pop-up e colora le Testate per ammorbidire l'HMI.

## 4. `cucina.css`
**Ruolo:** Look da "Kanban Board" per il monitor tablet dello chef, importato in `dashboards/cucina.php`.
- **Layout a Colonne**: Formatta le due grosse arterie dello schermo (`col-new` colonna rosa per "In Arrivo" e `col-prep` azzurra per "In Preparazione"). Usa altezza 100vh affinché occupino tutto lo schermo.
- **`.order-card`**: Ticket o scontrino di carta virtuale. Evidenzia la provenienza (`.table-badge`) e lo scoccare del tempo (`.time-badge`).  
- **Animazioni (`@keyframes pulse-new`)**: Dettami CSS per far "pulsare a mo di respiro" i nuovi ordini in modo da attirare l'occhio dal cuoco quando in lontananza affiorano nuovi arrivi.

## 5. `tavolo.css`
**Ruolo:** UI e UX fluida "Mobile-first" lato Cliente, importata in `dashboards/tavolo.php`.
- **Navbar & Filtri Inferiori (`.mobile-cat-nav`)**: Design ispirato alle moderne App di Delivery (Glovo/Uber). Una navbar staccata fluttuante agganciata a base schermo per i device mobili, ricca di scorciatoie per Pizze, Primi ed extra.
- **`.card-prodotto`**: La mattonella vera e propria che ospita la foto alta qualità (`img`), il costo sfumato e un bottone rotondo galleggiante d'aggiunta. Integra logiche di object-fit per mantenere i rapporti ratio delle foto senza distorcerle.
- **`#sticky-header-mobile`**: Elemento che sparisce e appare su scroll (imita il comportamento di WhatsApp) contenente il carrello rapido in alto a destra.
- **Zoom Modal Mobile-Tweaks**: Media queries (`@media (max-width: 991px)`) dedicate al Modalone Zoom. Abbassa l'altezza della foto al volo facendola diventare compatta su uno smartphone.
