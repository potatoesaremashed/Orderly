<?php
// Avvia la sessione
session_start();
include '../include/conn.php';

// Verifica che l'utente sia abilitato a vedere la cucina (cuoco o admin/manager)
if (!isset($_SESSION['ruolo']) || ($_SESSION['ruolo'] != 'cuoco' && $_SESSION['ruolo'] != 'admin' && $_SESSION['ruolo'] != 'manager')) {
    header("Location: ../index.php");
    exit;
}
include '../include/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Fogli di stile custom: cucina.css per la Kanban board, common.css condiviso -->
<link rel="stylesheet" href="../css/cucina.css">
<link rel="stylesheet" href="../css/common.css">

<!-- HEADER CUCINA -->
<div class="sticky-header">
    <div class="d-flex align-items-center gap-3">
        <img src="../imgs/ordnobg.png" width="50">
        <div>
            <div class="brand-title">Cucina</div>
            <div class="brand-subtitle">Monitor degli ordini in tempo reale</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Controlli per switchare tema scuro e terminare il lavoro (logout) -->
        <div class="theme-toggle" onclick="toggleTheme()" title="Cambia Tema">
            <i class="fas fa-moon" id="theme-icon"></i>
        </div>
        <a href="../logout.php" class="theme-toggle-sidebar text-danger" title="Esci">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<!-- KANBAN BOARD (Gestione status degli ordini su colonne drag/click based) -->
<div class="kanban-board">

    <!-- COLONNA: ORDINI IN ARRIVO (Tutti quelli appena inseriti dai tavoli) -->
    <div class="k-column">
        <div class="k-header" style="color: var(--new-order-text);">
            <span><i class="fas fa-bell me-2"></i> IN ARRIVO</span>
            <!-- Bubbble con il conteggio degli ordini in attesa (popolato via JS) -->
            <span class="badge-count" id="count-new">0</span>
        </div>
        <!-- Contenitore in cui inietteremo le card HTML via AJAX -->
        <div class="k-body" id="col-new"></div>
    </div>

    <!-- COLONNA: ORDINI IN PREPARAZIONE (Presi in carico dallo chef) -->
    <div class="k-column">
        <div class="k-header" style="color: var(--prep-order-text);">
            <span><i class="fas fa-fire me-2"></i> IN PREPARAZIONE</span>
            <span class="badge-count" id="count-prep">0</span>
        </div>
        <div class="k-body" id="col-prep"></div>
    </div>

    <!-- NOTA BENE: Gli ordini che passano allo stato "Pronto" non vengono mostrati qui per pulizia visiva. -->

</div>

<!-- Script adibito al long-polling o recupero cadenzato degli ordini e costruzione della UI kanban -->
<script src="../js/cucina.js?v=<?php echo time(); ?>"></script>
<?php include '../include/footer.php'; ?>