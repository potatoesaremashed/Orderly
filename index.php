<?php
    include "include/conn.php";
    include "include/header.php";

    if (isset($_POST['username'])) {
    
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // MANAGER
    $sql = "SELECT * FROM manager WHERE username='$user' AND password='$pass'";
    $risultato = $conn->query($sql);
    
    if ($risultato->num_rows > 0) {
        $_SESSION['ruolo'] = 'manager';
        header("Location: dashboards/manager.php");
        exit;
    }

    // CUOCHI
    $sql = "SELECT * FROM cuochi WHERE username='$user' AND password='$pass'";
    $risultato = $conn->query($sql);
    
    if ($risultato->num_rows > 0) {
        $_SESSION['ruolo'] = 'cuoco';
        header("Location: dashboards/cucina.php");
        exit;
    }

    // TAVOLI
    $sql = "SELECT * FROM tavoli WHERE nome_tavolo='$user' AND password='$pass'";
    $risultato = $conn->query($sql);
    
    if ($risultato->num_rows > 0) {
        $riga = $risultato->fetch_assoc(); 
        $_SESSION['ruolo'] = 'tavolo';
        $_SESSION['id_tavolo'] = $riga['id_tavolo'];
        header("Location: dashboards/tavolo.php");
        exit;
    }
    
    }
?>

<div class="container" style="max-width: 400px; margin-top: 50px;">
    <div style="text-align: center;">
        <img src="img/ordnobg.png" style="width: 120px; margin-bottom: 15px;">
        <h3>Login</h3>
    </div>

    <form method="post" action="">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
        <br> <label>Password</label>
        <input type="password" name="password" class="form-control" required>
        <br>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Accedi
        </button>
    </form>

</div>

<?php include "include/footer.php" ?>