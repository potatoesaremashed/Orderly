# Documentazione File JavaScript: `cucina.js`
**Posizione**: `js/cucina.js`  
**Destinazione/Utilizzo**: Integrato all'interno della pagina `dashboards/cucina.php`.

## 1. Architettura Generale e Inizializzazione
All'avvio della pagina (`DOMContentLoaded`):
1. Avvia istantaneamente un primo `caricaOrdini()` per popolare due colonne (Attesa e Preparazione).
2. Instaura un `setInterval()` massicico ogni **5 secondi**. Rispetto al Manager (10s), qui il polling è aggressivo in quanto la cucina ha urgenza immediata allo sparare della comanda dal tablet dei clienti e non può sprecare tempo.
3. Analizza il Local Storage forzando le icone Luna/Sole per evitare di accecare il locale cucina scuro.

## 2. Variabili Globali Principali (State Tracking)
- **`lastOrderCount` (Number):** Memoria volatile che tiene traccia puramente del numero (Es: 3 tickets "Nuovi"). Serve unicamente al campanello acustico.
- **`audio` (Classe Audio HTML5):** Precarica pesantemente in memoria nel browser lo snippet mp3 (`../audio/notifica_cucina.mp3`) cosicché il navigatore non debba perdere frazioni di secondo a scaricarlo su esecuzione della sirena sonora da parte dei poll requests.

## 3. Gestione Asincrona Orchestrata (Ticket Kanban)
- **Funzione `caricaOrdini()`**
  * **Comportamento:** Effettua uno stream JSON passivo dall'endpoint PHP `../api/cucina/leggi_ordini_cucina.php`. Ne ottiene un enorme array di oggetti complessi misti ai piatti fusi di MySQL (`.piatti` raggruppati).  
  * **Pipeline:** 
    1. Usa l'alta-funzione array filter (`data.filter`) di JavaScript per scremare in due sub-array puliti `inAttesa` e `inPrep`. Così facendo disconnette per sempre ticket sbrigati e confidenziari ("Pronti" non esistono qui; vengono rimossi dallo schermo da questa condizione mancante!).
    2. Modifica dinamicamente (`textContent`) il numero counter delle pillole contatore intestazione colorata ("In Arrivo (2)").
    3. Invoca un rendering ciclico per ogni array unendolo in pure stringhe DOM (`.join('')`) da dare in pasto alla griglia colonna appropriata (`col-new`, `col-prep`) richiamando il metodo fabbrica descritto sotto (`renderCard`). 
  * **Tracciatura Accustica:** Controlla brutalmente: ci sono più oggetti test-testata 'Nuovi' in attesa dell'ultimo check che ho salvato la volta scorsa all'ultima interrogazione db? Se SI! (E i nuovi sono maggiori a 0), suona allarme e poi ricambia la variabile in base al numero attuale intercettato così non risuona tra 5 secondi a vuoto di nuovo, ma ha registrato a mente questa transazione aggiuntiva uditiva!

- **Funzione `renderCard(o, tipo)`** (Fabbrica HTML di Bigliettini)
  * Prende `o` (L'oggettone comanda aggregata) e `tipo` (flag puramente per diretti e stile "new" / "prep").
  * **Intersezione Testi/Variazioni:** Variabili ternari gestiscono lo status del bottone in basso ("Inizia -> Segna come Pronto") ed emulano il colore CSS ("Rosso -> Verde"), preparando la stesura dell'argomento PHP successivo ("in_preparazione -> pronto") .
  * **Sbobbinamento Piatti interni:** In uno scontrino possono esserci infinite righe Piatto, iterate tramite un .map() extra sui cibi che ripercuote un `<div class="dish-row">`. Contempla anche render condizionali con i "Ternary operator JS" ( `p.note ? stampaleHTML : nullo` ) nel caso in cui un cliente apponga annotazioni al cuoco (es: "Ben cotta!").
  * Conclude restituendo l'obelisco gigante letterale HTML (La div `order-card` box grigio e ombreggiata con tutto dentro che atterrererà sul display in cucina touch screeen del pad).

## 4. Cambi di Flusso Logici sul Processo Aziendale
- **Funzione `cambiaStato(id, stato)`**
  * **Evento d'innesco**: Il cuoco (con le dita sporche magare, visto che i bottoni son larghi e tondi in UI css cucina) tocca compulsivamente la zona bassa della carta in colonna (`btn-action`).
  * **Passaggio Parametri**: A differenza delle solite chiamate, qui passiamo un pacchetto solido serializzato (`JSON.stringify`) inviandolo rigorosamente in `POST` via header "application/json" non FormData come il manager. Invia all'endpoint `cambia_stato_ordine.php` i campi identificativi Ticket ("Id 30" -> Passa a "In lavorazione").
  * **Post-Elaborazione**: L'asincronia aspetta che PHP dichiari esito "success". Solo allora auto-esegue (bypassando la noia dei 5 secondi del clock loop) un altro `caricaOrdini()` istantaneo bruciando l'istante per offrire la traslazione visiva sulla grigile animata della carta da colonna 1 a colonna 2 in tempo reale senza caricamenti di pagina visibili ad occhio dal cuoco! Un vero sistema reattivo.
