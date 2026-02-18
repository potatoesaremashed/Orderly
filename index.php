<?php
session_start(); // Starts the "memory" of the website so we know who is logged in
include "include/conn.php"; // Connects to the database (the place where data is stored)

// Check if the user clicked the login button (sent a username)
if (isset($_POST['username'])) {
    $user = $_POST['username']; // Get the name typed in the box
    $pass = $_POST['password']; // Get the password typed in the box

    // --- Check if the user is a Manager ---
    // We ask the database: "Is there a manager with this name and password?"
    $sql = "SELECT * FROM manager WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        // Yes! Remember that this person is a manager
        $_SESSION['ruolo'] = 'manager';
        $_SESSION['username'] = $user;
        // Send them to the Manager's page
        header("Location: dashboards/manager.php");
        exit; // Stop the code here
    }

    // --- Check if the user is a Cook ---
    // We ask the database: "Is there a cook with this name and password?"
    $sql = "SELECT * FROM cuochi WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        // Yes! Remember that this person is a cook
        $_SESSION['ruolo'] = 'cuoco';
        $_SESSION['username'] = $user;
        // Send them to the Kitchen page
        header("Location: dashboards/cucina.php");
        exit; // Stop the code here
    }

    // --- Check if the user is a Table (Customer) ---
    // We ask the database: "Is there a table with this name and password?"
    $sql = "SELECT * FROM tavoli WHERE nome_tavolo='$user' AND password='$pass'";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        // Yes! Get the table's information
        $row = $res->fetch_assoc();
        // Remember that this is a table and save its ID number
        $_SESSION['ruolo'] = 'tavolo';
        $_SESSION['id_tavolo'] = $row['id_tavolo'];
        $_SESSION['username'] = $user;
        // Send them to the Menu page for this specific table
        header("Location: dashboards/tavolo.php?id=" . $row['id_tavolo']);
        exit; // Stop the code here
    }
    
    // If we reach here, no match was found (Username right/Pass wrong OR Username wrong)
    $error = "Inserire il username o password corretto";
}

include "include/header.php"; // Load the top part of the website design
?>

<link href="css/common.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        background-color: var(--bg-body);
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        transition: background 0.3s, color 0.3s;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        padding: 20px;
    }

    .card-login {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        text-align: center;
        position: relative;
    }

    .theme-toggle-pos {
        position: absolute;
        top: 20px;
        right: 20px;
    }

    .brand-logo {
        width: 90px;
        height: auto;
        margin-bottom: 25px;
    }

    h3 {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 5px;
        font-size: 1.75rem;
    }
    
    p.subtitle {
        color: var(--text-muted);
        margin-bottom: 30px;
        font-size: 0.95rem;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom {
        width: 100%;
        background-color: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-main);
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control-custom:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(255, 71, 87, 0.15);
        background-color: var(--input-bg);
    }
    
    .form-control-custom::placeholder {
        color: var(--text-muted);
        opacity: 0.7;
    }

    .btn-main-lg {
        background-color: var(--primary);
        color: white;
        border: none;
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: 0.2s;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
    }

    .btn-main-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 71, 87, 0.4);
        color: white;
    }

    .alert-custom {
        border-radius: 12px;
        font-size: 0.9rem;
        background-color: var(--danger-soft);
        color: var(--danger);
        border: 1px solid rgba(255, 71, 87, 0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
    }

    .footer-text {
        margin-top: 30px;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
</style>

<div class="login-container">
    <div class="card-login">
        
        <div class="theme-toggle-pos">
            <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></div>
        </div>

        <img src="imgs/ordnobg.png" class="brand-logo" alt="Orderly Logo">
        <h3>Login</h3>
        <p class="subtitle">Inserisci le tue credenziali per accedere</p>

        <?php 
        // Display the error if it exists
        if (isset($error)) {
            echo '<div class="alert alert-custom mb-4" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>' . $error . '</span>
                  </div>';
        }
        ?>

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

        <div class="footer-text">
            &copy; <?php echo date("Y"); ?> Orderly
        </div>
    </div>
</div>

<script>
    function toggleTheme() {
        const body = document.body;
        const icon = document.getElementById('theme-icon');
        // Check current theme
        const isDark = body.getAttribute('data-theme') === 'dark';
            
        // Toggle
        const newTheme = isDark ? 'light' : 'dark';
        body.setAttribute('data-theme', newTheme);
        
        // Update icon
        if(newTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
        
        // Save preference
        localStorage.setItem('theme', newTheme);
    }

    // Initialize theme on load
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            const icon = document.getElementById('theme-icon');
            if(icon) {
                 icon.classList.remove('fa-moon');
                 icon.classList.add('fa-sun');
            }
        }
    })();
</script>

<!-- Make sure footer doesn't break layout by being empty if possible -->
<div style="display:none;">
<?php include "include/footer.php"; ?>