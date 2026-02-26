# Documentazione Core: Nucleo e Collegamenti
Questi file gestiscono i fondamenti dell'applicazione, dall'accesso al database allo smistamento dei login.

## 1. `index.php` (Pagina Iniziale e Routing di Login)
**Posizione:** `/index.php`
Questo file è la porta d'ingresso per tutti. Svolge il duplice ruolo di interfaccia grafica (HTML split-screen per il login) e di controllore (Logica PHP).

* **Variabili Globali e Locali:**
  * `$_SESSION`: Viene inizializzata (`session_start()`) per tracciare il ruolo autorizzato (Manager, Cuoco, o ID Tavolo).
  * `$ruolo_selezionato`: (Stringa "manager", "cuoco", o "tavolo") acquisito dal form tramite `$_POST['ruolo']` quando l'utente clicca una delle card d'accesso giganti sulla sinistra.
  * `$pin` o `$password_tavolo`: Estratti dai payload form.
  * `$errore`: Stringa stampata rosso se MySQL nega il Pin fornito dal form d'accesso all'eseguirsi delle Query in cascata.

* **Flusso e Architettura Logica (Login Routing):**
  1. Identifica il Ruolo richiesto. 
  2. Per i ruoli "Cucina" o "Manager", sbatte il Pin crittato contro utenti statici "hard-coded". Appena un match ha successo, spara un `header('Location')` per reindirizzare al cruscotto di quel ruolo.
  3. **Caso Complesso ─ Tavolo**: Il caso Tavolo cerca nel Database se esiste una stanza/pwd valida per quel punto tavola. 
      - _Sistema Anti-Sdoppiamento (Token)_: Se ha successo e se il token del browser matchha coll'infrastruttura `device_token_XYZ`, vieni rigettato o reindirizzato a seconda della disponibilità d'accesso. Un tavolo può esser presidiato da tanti cellulari, finché la chiave di sessione (generata all'apertura da parte del manager e fusa dentro a quel parametro in `tavoli` MySQL) corrisponderà per chiunque.

## 2. `include/conn.php` (Connettore Database)
**Posizione:** `/include/conn.php`
Il punto unico in cui Orderly "scopre" di poggiare su MySql.
- **Variabili Globali Esportate:** `$conn`. Un oggetto istanza `mysqli`. È importantissimo! Tutti i dashboard e le API includeranno all'inizio per primo (`require_once`) questo documento per ereditare e lanciare la connessione al database `ristorante_db`.
- **Rapporto Architetturale:** Ogni volta che si invoca `$stmt = $conn->prepare()`, quello stralcio di codice usa la porta MySQL aperta e dichiarata globalmente in `conn.php`.

## 3. `include/header.php` e `include/footer.php`
**Posizione:**`/include/header.php`
- I moduli base dell'HTML: includono a valle e a monte i font (Google Fonts `Poppins`), le librerie icone (FontAwesome) e caricamento del framework primario (`bootstrap.css`). Al loro interno incastonano anche logiche generiche come la variabile globale `document.addEventListener` per recuperare il "Tema scuro/Tema Chiaro" locale. Impilano `<script>` tags come chiusura per tutti gli scripts .js che ne susseguono, inclusivo il reattivo `bootstrap.bundle.min.js`.

## 4. `logout.php` (Distruttore Sessione)
**Posizione:** `/logout.php`
- **Flusso Logico:**
  1. Riprende le sessioni server (`session_start`)  per capire _chi_ sta cercando di allontanarsi.
  2. Fa match col Ruolo attivo. Se un **Tavolo** esce forzatamente o volontarimente, questo piccolo script si preocuperà di innescare l'API che libera la pedina a Database (Aggiornando `tavoli` settandolo a "libero" e spazzando la targa Token di controllo). Se il Manager uscirà l'applicativò svuoterà solo le logiche PHP col `session_destroy()`.
  3. Reindirizza verso la porta sicura (Form d'accesso alla base `index.php`).

## 5. Middleware Autenticazioni (`include/auth/manager_auth.php`, `tavolo_auth.php`)
**Posizione:** `/include/auth/...`
Questi due minuscoli file inibiscono, letteralmente bloccando in secchi (`exit()`), l'esecuzione di una singola riga di codice inferiore o pagina qualora un pirata cerchi di inserire nell'URL del path la risorsa (`http://localhost/dashboards/manager.php`). Controllano banalissimamente che `$_SESSION['ruolo']` coincida col flag nativo desiderato (Es "manager" o "admin"), facendoti rimbalzare brutalmente (`header(index)`) viceversa all'esterno. Usati spaventosamente su TUTTE le api in `.php` e su tutte le DashBoards prima del caricamento del dom HTML base per proteggere i circuiti di pagamento, cassa e backend MySQL.
