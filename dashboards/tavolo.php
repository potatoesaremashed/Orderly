<?php
    session_start();
    if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
        header("Location: ../index.php");
        exit;
    }
    
    include "../include/conn.php";
    include "../include/header.php";
?>

<h1>Menu</h1>

<?php include "include/footer.php" ?>