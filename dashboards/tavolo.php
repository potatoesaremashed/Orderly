<?php
session_start();
include "../include/conn.php";

// Check Login
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'tavolo') {
    header("Location: ../index.php");
    exit;
}
include "../include/header.php";

$categorie = $conn->query("SELECT * FROM categorie");
$prodotti = $conn->query("SELECT * FROM alimenti");
?>

<style>
    body { background-color: #f8f9fa; }
    
    /* Stili Navigazione Categorie */
    .link-categoria {
        cursor: pointer;
        padding: 15px 20px;
        display: block;
        color: #555;
        font-weight: 500;
        text-decoration: none;
        border-radius: 10px;
        margin-bottom: 5px;
        transition: 0.2s;
    }
    .link-categoria:hover { background-color: #e9ecef; }
    .link-categoria.active {
        background-color: #ffc107; 
        color: black;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* CARD PRODOTTO (VISTA PICCOLA) */
    .img-prodotto { height: 160px; object-fit: cover; border-bottom: 1px solid #eee; }
    .card-prodotto { 
        border-radius: 15px; 
        border: none; 
        overflow: hidden; 
        transition: 0.3s; 
        cursor: pointer; /* Indica che è cliccabile */
    }
    .card-prodotto:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; 
    }
    
    /* TRONCAMENTO DESCRIZIONE NELLA CARD */
    .descrizione-troncata {
        display: -webkit-box;
        -webkit-line-clamp: 3; /* Mostra massimo 3 righe */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* MODALE "ZOOM" 80% */
    .modal-80 {
        max-width: 80%; /* Occupa l'80% della larghezza */
    }
    @media (max-width: 768px) {
        .modal-80 { max-width: 95%; } /* Su mobile quasi tutto schermo */
    }
    
    .img-zoom {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid mt-3">
    <div class="row">
        
        <div class="col-md-3 border-end bg-white pt-3 sticky-top" style="height: 100vh; overflow-y: auto;">
            <div class="text-center mb-4">
                <img src="../imgs/ordnobg.png" width="120">
            </div>
            
            <h6 class="text-uppercase text-muted ms-2 mb-3 small">Categorie</h6>
            
            <div class="nav flex-column nav-pills">
                <a class="link-categoria active" onclick="filtraCategoria('all', this)">
                    Visualizza tutte le portate
                </a>

                <?php while($cat = $categorie->fetch_assoc()): ?>
                    <a class="link-categoria" onclick="filtraCategoria(<?php echo $cat['id_categoria']; ?>, this)">
                        <?php echo $cat['nome_categoria']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="col-md-9 p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm sticky-top" style="z-index: 1000;">
                <h3 class="m-0 fw-bold">Menu</h3>
                <div>
                    <span class="fs-5 me-3">Totale: <strong class="text-primary"><span id="soldi-header">0.00</span>€</strong></span>
                    <button class="btn btn-dark btn-lg rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCarrello" onclick="aggiornaModale()">
                        Vedi Carrello <span class="badge bg-warning text-dark ms-2" id="pezzi-header">0</span>
                    </button>
                </div>
            </div>

            <div class="row g-4" id="griglia-prodotti">
                <?php while($p = $prodotti->fetch_assoc()): ?>
                
                <div class="col-md-4 col-lg-3 item-prodotto" data-cat="<?php echo $p['id_categoria']; ?>">
                    <div class="card card-prodotto h-100 shadow-sm" 
                         onclick="apriZoom(this)"
                         data-nome="<?php echo htmlspecialchars($p['nome_piatto']); ?>"
                         data-desc="<?php echo htmlspecialchars($p['descrizione']); ?>"
                         data-prezzo="<?php echo $p['prezzo']; ?>"
                         data-img="../imgs/prodotti/<?php echo $p['immagine']; ?>"
                         data-allergeni="<?php echo htmlspecialchars($p['lista_allergeni']); ?>"
                         data-id="<?php echo $p['id_alimento']; ?>"> <img src="../imgs/prodotti/<?php echo $p['immagine']; ?>" class="img-prodotto">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold mb-1"><?php echo $p['nome_piatto']; ?></h5>
                            <p class="small text-muted mb-2 flex-grow-1 descrizione-troncata">
                                <?php echo $p['descrizione']; ?>
                            </p>
                            
                            <div class="mb-3">
                                <?php 
                                $allergeni = explode(',', $p['lista_allergeni']);
                                foreach($allergeni as $a) {
                                    if(trim($a) != "") echo "<span class='badge bg-light text-dark border me-1'>".trim($a)."</span>";
                                }
                                ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="h5 text-primary mb-0"><?php echo $p['prezzo']; ?>€</span>
                                
                                <div class="input-group input-group-sm" style="width: 100px;" onclick="event.stopPropagation()">
                                    <button class="btn btn-outline-secondary" onclick="gestisciCarrello(<?php echo $p['id_alimento']; ?>, -1, <?php echo $p['prezzo']; ?>, '<?php echo addslashes($p['nome_piatto']); ?>')">-</button>
                                    <input type="text" id="q-<?php echo $p['id_alimento']; ?>" value="0" class="form-control text-center p-0 bg-white" readonly>
                                    <button class="btn btn-outline-success" onclick="gestisciCarrello(<?php echo $p['id_alimento']; ?>, 1, <?php echo $p['prezzo']; ?>, '<?php echo addslashes($p['nome_piatto']); ?>')">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalCarrello" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Il tuo ordine</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="corpo-carrello">
         <p class="text-center text-muted">Il carrello è vuoto</p>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <h4>Totale: <span id="totale-modale">0.00</span>€</h4>
        <button type="button" id="btn-invia-ordine" class="btn btn-success fw-bold px-4">CONFERMA E INVIA</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalZoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-80"> <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-lg-6 bg-light d-flex align-items-center justify-content-center">
                        <img id="zoom-img" src="" class="img-zoom">
                    </div>
                    
                    <div class="col-lg-6 p-5 d-flex flex-column justify-content-center position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"></button>
                        
                        <h2 class="fw-bold display-6 mb-3" id="zoom-nome">Nome Piatto</h2>
                        
                        <div class="mb-4">
                            <h5 class="text-muted text-uppercase small ls-1">Descrizione</h5>
                            <p class="lead fs-4" id="zoom-desc" style="line-height: 1.6;">Descrizione completa...</p>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-muted text-uppercase small ls-1">Allergeni</h5>
                            <div id="zoom-allergeni" class="d-flex flex-wrap gap-2">
                                </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top">
                            <span class="display-6 text-primary fw-bold"><span id="zoom-prezzo">0</span>€</span>
                            
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted small me-2">Aggiungi all'ordine:</span>
                                <button class="btn btn-outline-dark btn-lg rounded-circle" id="btn-zoom-minus" style="width: 50px; height: 50px;">-</button>
                                <span class="fs-4 fw-bold" id="zoom-qty">0</span>
                                <button class="btn btn-primary btn-lg rounded-circle" id="btn-zoom-plus" style="width: 50px; height: 50px;">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/gestioneCarrello.js"></script>
<script>
    // Funzione per aprire la modale Zoom
    function apriZoom(card) {
        // Recupera i dati dai data-attributes
        const id = card.getAttribute('data-id');
        const nome = card.getAttribute('data-nome');
        const desc = card.getAttribute('data-desc');
        const prezzo = card.getAttribute('data-prezzo');
        const img = card.getAttribute('data-img');
        const allergeniRaw = card.getAttribute('data-allergeni');

        // Popola i campi della modale
        document.getElementById('zoom-nome').innerText = nome;
        document.getElementById('zoom-desc').innerText = desc; // Qui si vede tutto il testo!
        document.getElementById('zoom-prezzo').innerText = prezzo;
        document.getElementById('zoom-img').src = img;

        // Gestione Allergeni (crea i badge colorati)
        const containerAllergeni = document.getElementById('zoom-allergeni');
        containerAllergeni.innerHTML = ''; // Pulisce
        if (allergeniRaw) {
            allergeniRaw.split(',').forEach(alg => {
                if(alg.trim() !== '') {
                    containerAllergeni.innerHTML += `<span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">${alg.trim()}</span>`;
                }
            });
        } else {
            containerAllergeni.innerHTML = '<span class="text-muted">Nessun allergene segnalato</span>';
        }

        // Sincronizza la quantità visualizzata con quella attuale del carrello
        const inputMenu = document.getElementById('q-' + id);
        const qtyAttuale = inputMenu ? parseInt(inputMenu.value) : 0;
        document.getElementById('zoom-qty').innerText = qtyAttuale;

        // Configura i bottoni + e - della modale per aggiornare il carrello "sotto"
        document.getElementById('btn-zoom-plus').onclick = function() {
            gestisciCarrello(id, 1, parseFloat(prezzo), nome);
            document.getElementById('zoom-qty').innerText = parseInt(document.getElementById('q-' + id).value);
        };
        
        document.getElementById('btn-zoom-minus').onclick = function() {
            gestisciCarrello(id, -1, parseFloat(prezzo), nome);
            document.getElementById('zoom-qty').innerText = parseInt(document.getElementById('q-' + id).value);
        };

        // Mostra la modale
        var myModal = new bootstrap.Modal(document.getElementById('modalZoom'));
        myModal.show();
    }
</script>

<?php include "../include/footer.php"; ?>