# Documentazione File JavaScript: `tavolo.js`
**Posizione**: `js/tavolo.js`  
**Destinazione/Utilizzo**: Il "Cuore Pulsante" Front-End del menù accessibile al pubblico tramite dispositivi clienti (Tablet al tavolo, Qr-Code Scan su proprio iOS/Android). Richiamo in `dashboards/tavolo.php`.

## 1. Architettura Data-Model (Stato UI) & Setup Iniziale
Dato che il cliente fa lunghe spulciate nei listini impazzendo sui piatti senza alcun refresh browser, memorizziamo tutto via JS su variabili costanti ma volatiti da non disperdere mai a livello locale cliente:
- **`carrello{}`**: È un "Dizionario/Oggetto JSON". La chiave numerica (`ID Piatto`) accede al value oggetto con proprietà (Nome, Prezzo per singolo scalare, e Quantità in attesa di esser sparati all'ordinazione finale). Svuotato solo a ordine andato a profitto server o riazzerato dal login manager!
- **`filtriAllergeni[]`**: Una cesta d'archiviazione di stringhe. Se l'utente spunta dal modal filtri "Sedano", inseriamo 'Sedano' lì. Sarà propedeutica al motore di rendering in fase d'esclusione piatti.
- **`categoriaAttiva`**: L'anchor della tendina a discesa e tabs laterali della pagina, parte pre-impostata a "Misto (`all`)".

**Flow Eventi DOM-Caronte (`DOMContentLoaded`)**:
1. Abilmente avvia Subito il metodo `sincronizzaCarrello()` prima ancora che un consumatore finisca di leggere la pagina. Recupera carrelli abbozzati da un browser refresh (Inviati dalla API server a memoria session passata prima). 
2. Avvia una call massiva su tutta la pagina DOM per forzare la renderizzazione visiva delle mattonelle fisiche "Prodotto" incrociando lo status pulito "all" base.
3. Instanzia il timer anti-tampering sicurezza: Ogngi **5 Secondi** chiama l'api che avvale il manager a rimuovere fisicamente il dispositivo cliente a causa malfidenze, buggerie, o orario scaduto del ristorante. È una ghigliottina fissa sempre on-going asincrona al cliente.
4. Associa event Listeners classici ai bottoni di Inoltro Ordine Finale. 

## 2. Sistemi e Motori (Cervelli dell'esperienza utente)

### Il Motore Modificatori Sincroni (`sincronizzaCarrello` e `btnCardQty`)
- **`sincronizzaCarrello()`**: Ricrea il Dizionario JS interno parlando con l'API PHP temporanea del database Carrello che gestisce i salvataggi a vuoto o da caduta di rete. Lo scarica in massa e aggiorna le UI colpite a raffica (`aggiornaUI()`).
- **`btnCardQty()`**: La logica su bottoni (+) e (-) delle card prodotto frontali nel listino. Evita clic involontario al pop out zoom (`event.stopPropagation`), auto inietta a zero sul dizionario JS in ram un nuovo identificativo se mancante. Spingi asincronamente ogni maledetto tap/pulsante in POST ai database PHP (`aggiungi_` o `rimuovi_dal_carrello`). Azzera dai dizionari javascript la voce orfana se quantità è sotto 1, invocando repaint del HUD (Heads-up display per i conti dei soldi).

### Il Motore d'Esclusione Piatti (`renderProdotti`)
Questa singola mastodontica funzione comanda l'opacità fisica delle carte esposte a schermo in loop in base a un "Test Di Sopravvivenza a 3 Vie" a cui ognunguna va incontro. 
Istruisce tutti (`querySelectorAll()`) gli snippet "Mattonella Card" iterando sopra:
- Rileva dati nativi incorporati originariamente in PHP (`dataset.nome`, `dataset.allergeni`, ecc).
- **Match 1 (Barra di Ricerca Testo)**: Se ciò che cerco includa (`.includes()`) parti sia nel titolo che bella descrizione base sfalsa.
- **Match 2 (Tag Categories)**: Compara l'ID stringa impostato per navigazione al data id statico PHP Categoria.
- **Match 3 (Killer Intolleranze)**: Incrocia l'array Allergeni in memory contro quello splittato della ricetta da banco (Verificando che NON convergano ad alcune stringhe uguali incrociate grazie a un `!Array.some()`).
- Esito di test:  Attua un `display: none` css sulla card estromettendola dalla vista se è caduta a uno qualunque dei test, ritornando invece un attributo pulito invisibile nel caso sia immune, esponendola. Pulisito, Rapido (non serve server-requests su MySQL a ogni digitazione nella barra!), Autonomo lato Client Device.

## 3. Gestori Elementi Frontend Ecosistema (Visuali UI)

### Le Modali Approfondimento (Popup "Zoom Piatto")
Trattasi di una logica "A specchio". L'HTML Modal base risiede una volta sola nella radice. Le card contengono foto striminzite e prezzi non editabili.. Quando l'utente innesca `apriZoom(card)` passano le consegne allo script che copia fisicamente stringhe testi immagini e foto dentro ai tag Modali HTML neutri ripopolandoli dal niente. Usa un object state virtuale (`zoomState`) a dispetto del carrello per trattenere l'informazione numerica isolata e scartata in caso di chiusura croce rossa finestra ignorata dal cliente, evitando d'infestare l'array Carrello finchè questo non si immette sull'ingranaggio vero con funzione verde `confermaZoom()` che, per l'appunto esegue i salvataggi DB. 

### Il Resoconto e la Gestione Ordine Spedibile (Carrello Laterale Pre Recesso e Invio Cucina)
- **`aggiornaModale()`** e **`aggiornaUI()`**: I ricalcolatori a pioggia. Rigenerano (calcolo prezzo cumulare X * Qty) i box con icona verde totali esposti in testata sticky del tablet e nell'angolo inesposto in basso nel popup modale del riassunto carichi. Se l'array di tasto JS globale fallisce una length keys minima (Array Vuoto Senza Cibi per colpa di ritorni carrello in bianco e cliccamenti su sottrazione), auto blidano e freezano a disabilitato flag  `disabled=true` il massivo Bottone "Spedisci a tavillo (Invio finale in Cucina)", impedendo flood di scontrini di 0 dollari al server!
- **`inviaOrdine()`**: La mossa finale dopo essersi interrogati sulla Modal Confermi S/N di Sicurezza. Spedizione aerea su server con conversione ad "application/JSON POST format stringify" puro. Ricevuto l'Ok "Success == T" da PHP, fa scattare l'ultimo pop up celebrativo Verde brillante su UI, e asfalta tutte le memorie dizionario e variabili di sessione locali preparandosi per ordinazione dolce/caffè su carrello pulito numero due a tavolo invariato. (E se non dovesse andar a buon fine il fetch per via di intermezzi? Espone Toast asincroni di debug rosso Errore). 

### Tracker Storico Sessionale Tavolo Corrente (La Comanda Consegnata e i Pagamenti Passato Remoto)
- **`apriStorico()`**: Quando l'utente è curioso e preme "Vedi Storico Ordini Piattaforma...". L'unico snodo JS del Tavolo dove le UI DOM (divs/html tags generizzati in codice inline!) vengono elaborizzati e stampati a video per renderizzare "Gruppi Ordine -> Figli piatti consumati e prezzetti al netto... => Divisori Data Temporali -> Costo complessivo globale". Sonda l'api in JSON e formatta elegantemente gli indici con tanto di color coding badges (Verdoni, Rossi ecc dipendemente dallo stato della fetcch, cioè a che punto è la cucina col servizio al cliente)!
