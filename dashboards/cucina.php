<?php
/**
 * =========================================
 * DASHBOARD: Cucina (Kitchen)
 * =========================================
 * Questo file è il "cuore" operativo per lo staff in cucina.
 * Mostra in tempo reale gli ordini che arrivano dai tavoli e permette 
 * di gestire l'avanzamento della preparazione.
 * 
 * Ruoli autorizzati: Cuoco, Admin/Manager.
 */

session_start();
include '../include/conn.php';

/**
 * SICUREZZA
 * Se l'utente non è un cuoco o un admin, lo rispediamo al login.
 */
if (!isset($_SESSION['ruolo']) || ($_SESSION['ruolo'] != 'cuoco' && $_SESSION['ruolo'] != 'admin')) {
    header("Location: ../index.php");
    exit;
}

include '../include/header.php';
?>

<!-- Importiamo font e icone moderne per un look professionale -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/cucina.css">
<link rel="stylesheet" href="../css/common.css">

<!-- ========== HEADER STICKY ========== -->
<div class="sticky-header">
    <div class="d-flex align-items-center gap-3">
        <img src="../imgs/ordnobg.png" width="50">
        <div>
            <div class="brand-title">Cucina</div>
            <div class="brand-subtitle">Monitor degli ordini</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Pulsante per attivare/disattivare la Dark Mode -->
        <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema">
            <i class="fas fa-moon" id="theme-icon"></i>
        </div>

        <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Abbandona Cucina">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<!-- 
    KANBAN BOARD
    Utilizziamo una struttura a "colonne" (simile a Trello) per gestire gli ordini.
    Le colonne vengono popolate dinamicamente da JavaScript (cucina.js).
-->
<div class="kanban-board">

    <!-- COLONNA 1: Ordini appena arrivati dal tavolo -->
    <div class="k-column">
        <div class="k-header" style="color: var(--new-order-text);">
            <span><i class="fas fa-bell me-2"></i> IN ARRIVO</span>
            <!-- Contatore dei piatti per non perdersi nulla nei momenti di caos -->
            <span class="badge-count" id="count-new">0</span>
        </div>
        <!-- Il contenuto di questo div viene gestito da cucina.js -->
        <div class="k-body" id="col-new">
        </div>
    </div>

    <!-- COLONNA 2: Ordini attualmente in preparazione -->
    <div class="k-column">
        <div class="k-header" style="color: var(--prep-order-text);">
            <span><i class="fas fa-fire me-2"></i> IN PREPARAZIONE</span>
            <span class="badge-count" id="count-prep">0</span>
        </div>
        <!-- I piatti passano qui quando il cuoco clicca "Inizia" -->
        <div class="k-body" id="col-prep">
        </div>
    </div>

</div>

<!-- Il file JS contiene tutta la logica di polling (scarica dati ogni X secondi) -->
<script src="../js/cucina.js?v=<?php echo time(); ?>"></script>

<?php include '../include/footer.php'; ?>
