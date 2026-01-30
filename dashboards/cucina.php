<?php
    //check login
    session_start();
    if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'cuoco') {
        header("Location: ../index.php");
        exit;
    }

    include "../include/conn.php";
    include "../include/header.php";
?>

<h1>Ordini</h1>

<?php include "include/footer.php" ?>