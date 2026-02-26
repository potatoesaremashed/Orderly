<?php
// Inizia la sessione utente per salvare i dati di login
session_start();
include "include/conn.php";

// Quando viene premuto il pulsante di "Accedi" e si invia il form...
if (isset($_POST['username'])) {
    // Si prelevano i dati inseriti nei campi username e password
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // --- 1. Controllo del ruolo "MANAGER" ---
    $sql = "SELECT * FROM manager WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        $_SESSION['ruolo'] = 'manager';
        $_SESSION['username'] = $user;
        header("Location: dashboards/manager.php"); // Rimanda al pannello amministratore
        exit;
    }

    // --- 2. Controllo del ruolo "CUOCO" (Staff in Cucina) ---
    $sql = "SELECT * FROM cuochi WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        $_SESSION['ruolo'] = 'cuoco';
        $_SESSION['username'] = $user;
        header("Location: dashboards/cucina.php"); // Rimanda alla Kanban Board cucina
        exit;
    }

    // --- 3. Controllo del ruolo "TAVOLO" (Cliente finale) ---
    $sql = "SELECT * FROM tavoli WHERE nome_tavolo='$user' AND password='$pass'";
    $res = $conn->query($sql);
    
    // Se trova una corrispondenza nel DB dei tavoli...
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        
        // Cerca di capire se il tavolo è stato già "occupato" da un altro dispositivo
        // controllando se il token del browser attuale matcha con quello nel DB
        $tokenCookie = $_COOKIE['device_token_' . $row['id_tavolo']] ?? '';
        $tokenDB = $row['device_token'] ?? '';

        if ($row['stato'] === 'occupato' && !empty($tokenDB) && $tokenCookie !== $tokenDB) {
            // Se lo stato è già occupato ma il token non coincide, blocca l'accesso
            // (evita che due smartphone inseriscano ordini dallo stesso tavolo)
            $error = "Questo tavolo è già in uso da un altro dispositivo.";
        } else {
            // Genera un token numerico univoco e imposta i dati in sessione
            $nuovoToken = bin2hex(random_bytes(16));
            $_SESSION['ruolo'] = 'tavolo';
            $_SESSION['id_tavolo'] = $row['id_tavolo'];
            $_SESSION['username'] = $user;
            $_SESSION['login_time'] = date('Y-m-d H:i:s');

            // Imposta lo stato del tavolo in 'occupato' e gli lega quel token nel database
            $stmt = $conn->prepare("UPDATE tavoli SET stato='occupato', device_token=? WHERE id_tavolo=?");
            $stmt->bind_param("si", $nuovoToken, $row['id_tavolo']);
            $stmt->execute();
            
            // Imposta lo stesso cookie sul browser limitandone la durata a un giorno (86400 secondi)
            setcookie('device_token_' . $row['id_tavolo'], $nuovoToken, time() + 86400, '/');

            // Tutto ok, si entra nella dashboard del menu cliente
            header("Location: dashboards/tavolo.php?id=" . $row['id_tavolo']);
            exit;
        }
    } else {
        // Nessun match tra manager, cuochi o tavoli: si entra nell'errore standard
        $error = "Nome utente o password errati. Riprova.";
    }
}

// Stampa la prima parte del codice HTML della pagina (doctype, meta, head script)
include "include/header.php";
?>

<!-- Importazione dei fogli di stile esterni necessari alla schermata di Login -->
<link href="css/common.css" rel="stylesheet">
<link href="css/login.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="login-container">
    <div class="card-login">
        
        <!-- Bottone in linea che permette all'utente di passare in Dark Mode prima ancora del Login -->
        <div class="theme-toggle-pos">
            <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></div>
        </div>

        <img src="imgs/ordnobg.png" class="brand-logo" alt="Orderly Logo">
        <h3>Login</h3>
        <p class="subtitle">Inserisci le tue credenziali per accedere</p>

        <!-- Stampa visivamente l'avviso di errore (se le credenziali erano sbagliate) -->
        <?php if (isset($error)): ?>
            <div class="alert alert-custom mb-4" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Form HTML standard puntato su post, che ricarica questa stessa pagina PHP per far fare ad essa il check -->
        <form method="post">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="position-relative">
                    <input type="text" name="username" class="form-control-custom" placeholder="Es: tavolo1" required>
                    <i class="fas fa-user position-absolute text-muted" style="right: 18px; top: 18px;"></i>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
                    <i class="fas fa-lock position-absolute text-muted" style="right: 18px; top: 18px;"></i>
                </div>
            </div>
            <button type="submit" class="btn-main-lg">Accedi</button>
        </form>

        <div class="footer-text">&copy; <?php echo date("Y"); ?> Orderly</div>
    </div>
</div>

<script>
    // Questo trucco serve a far adattare dinamicamente l'icona del file 
    // Javascript toggleTheme nel caso il sistema carichi in automatico su 'dark'
    if (localStorage.getItem('theme') === 'dark') {
        const icon = document.getElementById('theme-icon');
        if (icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun'); }
    }
</script>

<div style="display:none;">
    <!-- Lo chiamo nascosto giacché mi serve solo il plugin bootstrap bundle inserito in header.php -->
    <?php include "include/footer.php"; ?>
</div>