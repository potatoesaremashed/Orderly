# Documentazione File JavaScript: `manager.js`
**Posizione**: `js/manager.js`  
**Destinazione/Utilizzo**: Integrato unicamente all'interno della pagina `dashboards/manager.php`.

## 1. Architettura Generale e Ciclo di Vita (Lifecycle)
All'avvio della pagina (`DOMContentLoaded`):
1. Esegue istantaneamente la funzione `caricaTavoli()`.
2. Lancia un timer con `setInterval()` che ogni **10 secondi** re-innesca in segreto (background) `caricaTavoli()`. Serve a tenere il cruscotto sempre sincrono senza refresh.
3. Verifica se il tema locale salvato dal bottoncino (in `common.css`/`header.php`) fosse dark per allineare l'icona lunare/solare.
4. Programma uno svuotamento per eventuali alert verdi transitori (es: URL param `?msg=success` provenienti dai PHP che ricaricano del tutto la pagina web per la gestione menù prodotti, al contrario della gestione logica asincrona dei tavoli).

## 2. Navigazione Cilent-Side
- **Funzione `switchPage(page, el)`**
  * **Relazioni:** Viene innescata cliccando i tasti della Sidebar "Gestione Tavoli" o "Gestione Menu" (Sia versione Desktop/Sidebar che Mobile/Bottom navbar).
  * **Comportamento:** Funziona come una Single Page Application (SPA). L'HTML di manager.php ospita in realtà più sezioni `<div id="page-tavoli">` e `<div id="page-menu">`. Questa funzione cicla e imposta a `display: none` tutte le sezioni tranne quella in oggetto all'argomento stringa `page`. Sincronizza visivamente le classi `.active` sui bottoni cliccati così l'utente sa in quale "finestra virtuale" si sta muovendo.

## 3. Variabili Globali
- **`allTavoli` (Array)**
  * **Che cos'è?** Questa lista di oggetti è il "cervello a cache" del front-end. Mantiene una copia 1:1 dei record scaricati dal DataBase (Tavolo, Stato, Password, Posti, Ordini in corso).
  * **A cosa serve?** Serve a prelevare istantaneamente dati durante i filtri senza richedere interrogazioni di Rete per ogni tap per le sorting tabs ("Tutti", "Liberi", "Riservati").

## 4. Gestione Tavoli in Tempo Reale
- **Funzione `caricaTavoli()`**
  * **Come funziona:** Effettua una richiesta API `GET fetch` su `../api/manager/get_tavoli.php`.
  * **Effetto Successivo:** Sovrascrive la cache locale `allTavoli`, poi passa il testimone (cascata) a `aggiornaConteggi(data)` e, infine `renderTavoli(data)` che disegnano in HTML le carte.

- **Funzione `aggiornaConteggi(data)`**
  * Accetta in pasto l'array esteso. Usando un piccolo dizionario JS riassuntivo (libero = 0, occupato = 0...) cicla e conta. Subito dopo si premunisce di settare in tempo reale (DOM injection via `textContent`) i badge numerici che trovate sui tasti filtro a schede in alto nella dashboard che presentano i totali operativi al gestore ("Tutti (10)", "Occupati (4)", ecc..).

- **Funzione `renderTavoli(tavoli)`**
  * Genera codice stringato massivo tramite array `.map()` in formato backticks ES6 (Template literals `{{ }}`). Costruisce letteralmente la mattonella fisica Div di ogni sala. Setta le icone dinamiche (es:`fa-check-circle`, `fa-users`) usando come discrimine testuale lo "stato". Si chiude iniettando tale listone enorme dentro l'elemento contenitore grid (`innerHTML` su `#tavoli-grid`).

- **Funzione `filtraTavoli(filtro, btn)`**
  * Eseguita premendo i Tabs sopra ai tavoli (Tutti, Libreri, Occupati). Invece di chiedere al DB via PHP, filtra al volo su RAM (`allTavoli.filter(...)`) scartando i disallineati e inviando i sopravvissuti a un refresh UI di `renderTavoli`.

## 5. Operazioni sul DB API Proxy
Queste funzioni inviano variazioni fisiche al server (Create, Update, Delete) per poi riaggiornare la schermata del manager subito dopo (con conseguente caduta d'effetto fino agli schermi dei monitor cuoco/pad separati, dato l'interconnessione del DB unificato PHP).
- **Funzione `ciclaNuovoStato(id, statoAttuale)`**
  * Costringe il tavolo a shiftare da "libero", ad "occupato", a "riservato", senza dialoghi o form. Utilissima in emergenze live all'arrivo dei clienti ("Spingili dentro e chiudilo").  Chiama Endpoint: `cambia_stato_tavolo.php`.
- **Funzione `terminaSessione(id)`**
  * Chiama Endpoint: `termina_sessione.php`. Fondamentale: unisce l'incasso, annienta il `device_token` (buttando fuori lo schermo cliente sul tavolo) e ripristina a "libero" colorando in verde.
- **Funzione `aggiungiTavolo()`** e **`modificaTavolo()`** e **`eliminaTavolo(id, nome)`**
  * Prelevano banalmente le stringhe testuali lette dagli input form del Modal (`nuovo_nome_tavolo`, `nuovo_password...` o quelli prefixed con `mod_`). Le iniettano su una classe FormData instanziata in Javascript simulando una ricezione `POST Form Multipart` e le sparano alle tre API corrispondenti in cartella back-end chiamando infine il riaggiornamento griglia su successo e nascondendo i pop-ups frontali.

## 6. Lator GESTIONE MENÙ Prodotti
- **Funzione `apriModalModifica(btn)`**
  * Il Menù, diversamente dalla asincronia dei Tavoli, viene re-instanziato al momento ricaricando manager.php alla fine del salvataggio. Questa singola funzione ha lo scopo di preparare la Finestra "Fisica" sovrascritta (Modale) riempiendo i campi testi (Prezzo, Descrizione, Checkbox Allergeni) con i `data-attribute HTML` (es `data-nome="Pizza"`, `data-prezzo="10.50"`) presenti di natura sul bottone a matita che un amministratore clicca sul tavolo Menù Generale prima di far esplodere la form a schermo. Essendo che il Form Menù Modello è uno unico integrato alla radice nel Footer (ma i piatti sono decine), lo Javascript inietta al volo in tale Modale Vuota tutti i parametri del piatto corretto appena premuto con l'id corrente prima di mostrare all'essere umano la schermata di Modifica a comparsa.

## 7. Utility
- **Funzione `mostraToast(msg, isError = false)`**
  * Governa le micro-notifiche Bootstrap fluttuanti centrali di 3 sec. Decide dinamicamente in base ad `isError` se inserire la classe stile rosso per un crash oppure verde se tutto è filato liscio prima di eseguirvi metodo .show() e svanirle poco dopo.
