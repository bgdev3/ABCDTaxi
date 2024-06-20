                                    <!-- Navigation afficher pour le Back Office -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-body text-light navAdmin mb-5 p-2">
    <a class="navbar-brand" href="index.php?controller=panelAdmin&action=index">Accueil</a>
    <button class="navbar-toggler order-2 order-md-2" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse mx-auto" id="menu">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php?controller=adminSlideshow&action=index&token=<?php echo trim($_SESSION['token']);?>">Diaporama</a></li>
            <li class="nav-item"><a class=" nav-link" href="index.php?controller=adminReservations&action=index&token=<?php echo trim($_SESSION['token']);?>">Réservations</a></li>
            <li class="nav-item"><a class=" nav-link" href="index.php?controller=historyTransport&action=index&token=<?php echo trim($_SESSION['token']);?>">Transports</a></li>
            <li class="nav-item"><a class=" nav-link" href="index.php?controller=historyClients&action=index&token=<?php echo trim($_SESSION['token']);?>">Clients</a></li>
            <li class="nav-item"><a class=" nav-link" href="index.php?controller=adminPrice&action=index&token=<?php echo trim($_SESSION['token']);?>">Tarifs</a></li>
        </ul>
    </div>
    <div class="btn-group">
        <button class="btn btn-danger btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Bienvenue dans votre espace <?php  echo ucfirst($_SESSION['username_admin']); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="index.php?controller=authAdmin&action=register&token=<?php echo trim($_SESSION['token']);?>">Renouveller mes Ids</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="index.php?controller=authAdmin&action=logout&token=<?php echo trim($_SESSION['token']);?>">Se déconnecter</a></li>
        </ul>
    </div>
</nav>
