                                                <!-- Vue du panel administrateur -->
<?php
$title = 'Admin - Acceuil';
// Démarre la session
session_start();
// Si l'admin n'ets pas connecté, redirige vers la page d'accueil
!isset($_SESSION['username_admin']) ?? header('location:index.php');;
// if(!isset($_SESSION['username_admin'])){
//     header('location:index.php');
// } 
?>

<section class="text-center pb-5">
    <h4>Que souhaitez vous consulter ?</h4>
    <div class='container-fluid d-flex flex-wrap flex-column align-items-center flex-lg-row   justify-content-lg-evenly mb-5 mt-4 mt-lg-5'>
        <div class="card bg-dark border border-secondary" style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title text-danger">Diaporama</h5>
                <p class="card-text text-light pt-4 pb-4">Gérer les différentes photos du diaporama !</p>
                <a href="index.php?controller=adminSlideshow&action=index&token=<?php echo trim($_SESSION['token'])?>" class="btn btn-dark text-danger border border-secondary">Accéder au diaporama</a>
            </div>
        </div>
        <div class="card bg-dark border border-secondary mb-4 mt-4 " style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title text-danger">Réservations</h5>
                <p class="card-text text-light pt-4 pb-4">Accéder et gérer la liste des réservations en cours !</p>
                <a href="index.php?controller=adminReservations&action=index&token=<?php echo trim($_SESSION['token'])?>" class="btn btn-dark text-danger border border-secondary">Accéder aux réservations</a>
            </div>
        </div>
        <div class="card bg-dark border border-secondary" style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title text-danger">Historique</h5>
                <p class="card-text text-light">Accéder et gérer les différents historiques</p>
                <a href="index.php?controller=historyClients&action=index&token=<?php echo trim($_SESSION['token'])?>" class="btn btn-dark text-danger border border-secondary mb-2">Historique clients</a>
                <a href="index.php?controller=historyTransport&action=index&token=<?php echo trim($_SESSION['token'])?>" class="btn btn-dark text-danger border border-secondary">Historique des transports </a>
            </div>
        </div>
        <div class="card bg-dark border border-secondary mb-4 mt-4" style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title text-danger">Tarification</h5>
                <p class="card-text text-light pt-4 pb-4">Accéder et gérer la tarification de vos courses</p>
                <a href="index.php?controller=adminPrice&action=index&token=<?php echo trim($_SESSION['token'])?>" class="btn btn-dark text-danger border border-secondary w-75">Tarifs</a>
            </div>
        </div>
    </div>
    
</section>