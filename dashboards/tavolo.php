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

    /* CARD PRODOTTO */
    .img-prodotto { height: 160px; object-fit: cover; border-bottom: 1px solid #eee; }
    .card { border-radius: 15px; border: none; overflow: hidden; transition: 0.3s; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
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
                    <div class="card h-100 shadow-sm">
                        <img src="../imgs/prodotti/<?php echo $p['immagine']; ?>" class="img-prodotto">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold mb-1"><?php echo $p['nome_piatto']; ?></h5>
                            <p class="small text-muted mb-2 flex-grow-1"><?php echo $p['descrizione']; ?></p>
                            
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
                                
                                <div class="input-group input-group-sm" style="width: 100px;">
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
        <button type="button" class="btn btn-success fw-bold px-4">CONFERMA E INVIA</button>
      </div>
    </div>
  </div>
</div>


<script src="../js/gestioneCarrello.js"></script>
<?php include "../include/footer.php"; ?>