<?php
session_start();
include "include/conn.php";

if (isset($_POST['username'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //check credenziali

    $sql = "SELECT * FROM manager WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        $_SESSION['ruolo'] = 'manager';
        $_SESSION['username'] = $user;
        header("Location: dashboards/manager.php");
        exit;
    }

    $sql = "SELECT * FROM cuochi WHERE username='$user' AND password='$pass'";
    if ($conn->query($sql)->num_rows > 0) {
        $_SESSION['ruolo'] = 'cuoco';
        $_SESSION['username'] = $user;
        header("Location: dashboards/cucina.php");
        exit;
    }

    $sql = "SELECT * FROM tavoli WHERE nome_tavolo='$user' AND password='$pass'";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['ruolo'] = 'tavolo';
        $_SESSION['id_tavolo'] = $row['id_tavolo'];
        $_SESSION['username'] = $user;
        header("Location: dashboards/tavolo.php?id=" . $row['id_tavolo']);
        exit;
    }
}

include "include/header.php";
?>

<div class="container" style="max-width: 400px; margin-top: 50px; text-align: center;">
    <img src="img/ordnobg.png" style="width: 120px; margin-bottom: 15px;">
    <h3>Login</h3>

    <form method="post">
        <div class="mb-3" style="text-align: left;">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3" style="text-align: left;">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Esegui Login</button>
    </form>
</div>

<?php include "include/footer.php"; ?>