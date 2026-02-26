# Documentazione API (PHP): Gestione Manager
**Posizione**: `api/manager/`

Questi file ricevono le richieste asincrone inviate dalla Dashboard del Manager, interagiscono con il database MySQL (`$conn`), e restituiscono le riposte tipicamente in formato JSON o effettuano redirect. Includono sempre all'inizio `manager_auth.php` per assicurarsi che i clienti/cuochi non hackerino questo ramo chiamate esoterico.

## 1. Funzioni CRUD - Tavoli della Sala
* **`get_tavoli.php`**
  - **Scopo:** Scarica l'elenco dei tavoli attuali e vi abbina delle query complesse correlate.
  - **Relazione:** Invocato costantemente dalla funzione timer e start up in JavaScript di `manager.js`.
  - **DB Variazioni:** Effettua una sub-query per carpire anche il numero delle comande `ordini` del rispettivo target id_tavolo (`COUNT(*)`) che sono differenti  da 'pronto'  e riversa questi conteggi live insieme a i payload massivi d'anagrafica.

* **`aggiungi_tavolo.php`**
  - **Variabili Input (POST):** `$nome` (Nome palese stringa), `$password` (Pin d'accesso), `$posti` (Intero Integer a tetto).
  - **Relazioni:** Disinnescata a ricezione payload e instanziazione Fetch Post dalla GUI del bottone Aggiungi Modal Tavolo. Previene i sdoppiamenti escludendo nomi fotocopia già esistenti. Rilascia al termine gli output binari json formattati `success: true`.

* **`modifica_tavolo.php`**
  - **Scopo:** Cambia password, posti coperti, nome e "Stato" in base alla manipolazione su tasto blu "Modificia" eseguito premendo il tasto o modale in `manager.js`. Sovrascrive istantaneamente tutti i vincoli (`UPDATE tavoli SET..`)

* **`elimina_tavolo.php`**
  - **Scopo:** Distruzione definitiva dal ristorante del punto raccolta comande (Id).
  - **Criticità (Meccanismo anti-FK):** È uno dei pochi script a procedere in modo distruttivo retroattivo. Data la "Integrità Referenziale / Chiavi Esterne" di MySQL le dipendenze in un DB causerebbero l'anomalia di negarne la cancellazione (Non puoi distruggere un tavolo che avesse scontrini pre-effettuati a cronistoria nel mese passato, esploderebbe l'architettura MySQL relazionale). Esegue l'Inner Join di pulizia scontrini e azzera le foreign columns antecedenti all'omicidio logico di `$id` in `tavoli` . 

* **`cambia_stato_tavolo.php`** / **`termina_sessione.php`**
  - Modificatori diretti dello status cromatico logico (Da Occupato a Libero ecc). Terminare la sessione porta a NULL (`NULL`) il certificato segreto (`device_token`) del browser che governava il tavillare, causandone lo sgorgheggio a vista fuori dal sistema e registrando a libidine col server timestamp server `NOW()` il reboot sessione al fresco per  tavolo in SQL.

## 2. Funzioni CRUD - Menù e Piatto 
Operano prettamente in "Sincrono". Spesso le riposte esitano in redirect forzosi d'Header Page Reloads (`header("Location: ../../dashboards/manager.php?msg...")`) col risultato visivo su url, diversamente dall'amministrazione locale a sfondo Json della Sala, a fronte del fatto che queste varianti trasmettono file BINARI e pesanti foto LongBlop via Multipart Forms, ostici per un semplice object fetch . 

* **`aggiungi_piatto.php`** e **`modifica_piatto.php`**
  - **Variabili Input Comuni (POST/FILES):** `$nomePiatto`, `$prezzo`, `$descrizione`, `$idCategoria`, `$allergeni` (Questo subiva `implode()` JS frontend prima del traghettare per trasformare in CSV Array base MySQL "Granchio,Soia,Uovo").
  - **Trattamento Foto File (`$_FILES["immagine"]`)**: Cattura l'immagine fisica scivolata per parametro binario tmp e ne estrapola i RAW Datas con `file_get_contents`. Li inietta via BLOB diretti legandoli ai preparedStatements param di String (`"ssdiss"`). In "modifica", la fork decisionale  evita intelligentemente rimpiazzi SQL del bind se il manager disattende modifiche caricamenti fotografici.
  
* **`aggiungi_categoria.php`** e **`elimina_categoria.php`**
  - Costruiscono il raccoglitore genitore (es 'Primi'). In Eliminazione (`POST id_categoria`) innalza eccezioni se ci fossero figli dipendenti ad essa bloccando le dipartite del contenitore per via dei FOREIGN KEYS `try-catch`, sbattendo al muro l'amministratore e impendendo stalli nel CMS che bloccherebbero a display i piatti senza mamma al reload menù in Dashboard Clienti. 

* **`elimina_piatto.php`**
  - Prettamentè un banale `DELETE FROM alimenti WHERE id_alimento=?`, non comporta ramificazioni con gli Ordini vecchi già effettuati nel locale se il cameriere avesse emesso ordini perchè al momento Ordini emessi copiano referenziali staccati indipendenti a dispetto puramente d'id referenziali in `dettagli`. Chiama poi il redirect d'avviso positivo eliminato. 
