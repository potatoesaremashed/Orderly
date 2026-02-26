# Documentazione API (PHP): Terminale Kanban Cucina
**Posizione**: `api/cucina/`

Questo gruppo api si compone di sole due arterie interponibili tra lo schermo monitor touch del Cuoco (in frontend asincrono Javascript) e i tavoli del Ristorante. Impiegano restrizioni server-side per i ruoli `cuoco`, `manager` o `admin`.

## 1. Carica Pipeline Ristorazione
* **`leggi_ordini_cucina.php`**
  - **Cosa Fa:** Fornisce l'array globale dell'intera operatività della giornata che non è ancora stata completata. 
  - **Meccanica:** Esegue una Maxi-Query SQL innestando le 4 tabelle mastodontiche del database (`ordini`, `tavoli`, `dettagli_ordini`, `alimenti`).
  - **Particolarità (LEFT JOIN):** Viene forzato appositamente l'uso di un blocco refernziale "Left" sui tavoli afferrandoli tramite `$row['nome_tavolo'] ? $row['nome_tavolo'] : "Tavolo ".$row['id_tavolo']` per non inceppare la UI in cucina qualora il manager escludesse un tavolo mentre l'ordine dei mangiatori era già sceso in magazzino.
  - **Esclusione "Pronto":** Evita di spedire in the-wire network tutti gli ordini già messi in lavorazione nel passato con `WHERE o.stato IN ('in_attesa', 'in_preparazione')` alleggerendo la banda.

## 2. Inoltro Azioni Rapide Cucina
* **`cambia_stato_ordine.php`**
  - **Cosa fa:** Tramuta il colore della card ordine e fa avanzare nel tunnel il ticket in questione.
  - **Variabili (Lettura payload applicaton/JSON):** Siccome la dashboard cucina scarica da React/JS un JSON puro e solido, il server PHP è forzato ha codificarselo con `json_decode(file_get_contents('php://input'), true)` a differenza di `$_POST` massivo HTML standard. 
  - **Protezioni interne e Variazioni:** Si cautela che inneschi e script alterati (es In_prep, ProntoSbagliato, Hackerato) non distruggano la riga di codice castando un array di ammissione (`$stati_validi`). Infine aggiorna la colonna `status` della comanda padrona a cascata, offrendo una replica rapida JSON "success: true".
