# Documentazione API (PHP): Gestione Ordinazioni e Cruscotto Cliente (Tavolo)
**Posizione**: `api/tavolo/`

La cartella è dedicata eslusivamente a fungere da ponte per il Frontend del cliente finale (Web App Tavolo). Contiene protezioni di base (`tavolo_auth.php`) per evitare abusi esposti alla clientela.

## 1. Gestione Carrello Asincrona (Draft Orders)
A differenza del carrello nei classici e-commerce conservato in Cookie o HTML LocalStorage, il carrello di Orderly salva i Dati a crudo già sul Database in modo che, se il cellulare del cliente si spegne per batteria scarica, può riprendere l'ordinazione da un altro telefono preservando la bozza inserita per questo tavolo.

* **`aggiungi_al_carrello.php`**
  - **Funzionamento Intelligente:** Riconosce la Sessione del tavolo (`$idTavolo`). L'utente non invia il suo ID per sicurezza, ma passa `$idPiatto` e `$qta`.
  - **Relazioni SQL:** Sonda innanzitutto se su `ordini` esiste un record aperto con stato "in_attesa" per lui. In caso negativo glielo crea vergine. Ottenuto un Header d'Ordine fa UPDATE su `dettaglio_ordini` se il piatto era già stato premuto (aumentando la quantity) oppure lo immette fresco con un INSERT.

* **`rimuovi_dal_carrello.php`**
  - **Comportamento Limite:** Similare all'aggiunta ma fa da de-scalatore `-1`. Controlla la quantità prima di eseguire lo scomputo: se l'array `dettaglio_ordini` per il binomio Order/Dish segna che l'utente ha tolto l'ultima pizza, non aggiorna a zero, ma spara un `DELETE` violento rimuovendola per pulire il database dai fardelli zero-bytes.

* **`get_carrello.php`**
  - **Uso:** Spara un fetch JSON massivo estraendo il carrello pre-invio raggruppato unendo nomi piatti (`a.nome`) a prezzi (`a.prezzo`) dal DB in modo che il front-end JS possa formattare il DOM e i totali valuta.

## 2. Processo d'Invio Comanda al Cuoco
* **`invia_ordine.php`**
  - **Il File Più Importante dell'App.** Trasforma le draft del carrello JSON locale JS in un ticket reale in cucina.
  - **Transazioni:** Apre il pacchetto con `$conn->begin_transaction()`. Questo è un "lock" di garanzia. Traccia la genesi testata in `in_attesa` e spara a raffica tutti i piatti su un `foreach`. Se qualcosa andasse male salta in `catch` e fa un "Rollback", scordandosi le modifiche e salvando il DB da comande orfane che verrebbero pagate per intero ma in cucina arriverebbero dimezzate. 

## 3. Gestione e Tracking Utente (History / Polling)
* **`leggi_ordini_tavolo.php`**
  - Disegnatore dello "Storico Fiscale Mobile": Restituisce tutti gli ordini vecchi processati dalla cucina.
  - **Il Paradosso del Polling Precedente:** Non potendo scaricare "TUTTI gli ordini dal 1900 di questo Tavolo 4", sonda la data speciale `sessione_inizio` generata al momento dell'ammissione al tavolo dal Manager e legge SOLO i piatti battuti da quel preciso scoccare del tempo in avanti. Usa le manipolazioni classiche Array padri / Array figli di un ticket su PHP, castando tutto in currency formato € (`number_format`).

* **`verifica_sessione.php`**
  - **Anti-Sniffing and Kickout**: Pingato dal Tablet cliente ogni 5s. Legge lo stato e i `device_token`. Se il Manager a sistema schiaccia Termina Sessione Tavolo per chiudergli il conto, setterà su Sql a null il `device_token` rendendo fallace il test PHP e causandone la riposta falsa `['valida' => false]`, portando il JS frontend a disconnettere irrimediabilmente l'utilizzatore dall'App a scopo difensivo. 
