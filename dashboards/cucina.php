<?php
/**
 * CUCINA
 * -------------------------------------------------------------------------
 * Gestisce l'interfaccia visuale per il personale di cucina.
 *
 * Ruoli autorizzati: Cuoco, Admin/Manager.
 */

session_start();
include '../include/conn.php';

// Controllo Sicurezza: Reindirizza al login se l'utente non è loggato o non ha il ruolo di Cuoco (o Admin).
if (!isset($_SESSION['ruolo']) || ($_SESSION['ruolo'] != 'cuoco' && $_SESSION['ruolo'] != 'admin')) {
    header("Location: ../index.php");
    exit;
}

include '../include/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/cucina.css">
<link rel="stylesheet" href="../css/common.css">



<div class="sticky-header">
    <div class="d-flex align-items-center gap-3">
        <img src="../imgs/ordnobg.png" width="50">
        <div>
            <div class="brand-title">Cucina</div>
            <div class="brand-subtitle">Monitor degli ordini</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema">
            <i class="fas fa-moon" id="theme-icon"></i>
        </div>

        <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Abbandona Cucina">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<!-- Contenitore principale della Kanban Board per la gestione visuale degli ordini -->
<div class="kanban-board">

    <!-- COLONNA 1: Ordini appena arrivati dal tavolo -->
    <div class="k-column">
        <div class="k-header" style="color: var(--new-order-text);">
            <span><i class="fas fa-bell me-2"></i> IN ARRIVO</span>
            <!-- Contatore del numero di piatti in arrivo -->
            <span class="badge-count" id="count-new">0</span>
        </div>
        <!-- Contenitore dinamico: i piatti in arrivo vengono caricati qui tramite JavaScript -->
        <div class="k-body" id="col-new">
        </div>
    </div>

    <!-- COLONNA 2: Ordini attualmente in preparazione dal cuoco -->
    <div class="k-column">
        <div class="k-header" style="color: var(--prep-order-text);">
            <span><i class="fas fa-fire me-2"></i> IN PREPARAZIONE</span>
            <!-- Contatore del numero di piatti in preparazione -->
            <span class="badge-count" id="count-prep">0</span>
        </div>
        <!-- Contenitore dinamico: i piatti in preparazione vengono caricati qui tramite JavaScript -->
        <div class="k-body" id="col-prep">
        </div>
    </div>

</div>

<script src="../js/cucina.js?v=<?php echo time(); ?>"></script>

<?php include '../include/footer.php'; ?>