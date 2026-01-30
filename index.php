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
}

include "include/header.php"; // Load the top part of the website design
?>

<!-- This is the box where the user types their login info -->
<div class="container" style="max-width: 400px; margin-top: 50px; text-align: center;">
    <img src="imgs/ordnobg.png" style="width: 120px; margin-bottom: 15px;">
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