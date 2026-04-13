                                            <!-- Affiche la vue des tarifications -->
<?php
$title = $title = 'Admin - Tarification';

// S'il n'y a pas d'activté au de la de 20min, l'utilisateur est déconnecté
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:/public/');
    
} elseif (!isset($_SESSION['username_admin'])) {
    header('location:/public/');
}
?>
<section>

    <div class="text-center">
        <h4 class="text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Tarification en cours</h4>
    </div>

    <div class="admin-reservation col-12 col-lg-10 col-md-8 col-lg-8 mx-auto">
    
        <div class="d-flex flex-lg-row flex-column gap-md-5 justify-content-center">

            <div class="table-responsive w-75 mx-auto">
                <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle caption-top ">
                    <caption class="text-light text-center fs-6 ">Jours</caption>
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" class="text-danger "></th>
                            <th scope="col" class="text-danger ">Simple</th>
                            <th scope="col" class="text-danger">Aller-retour</th>          
                        </tr>
                    </thead>
                    <tbody>                                 
                            <tr>
                                <td class="data">Tarifs kilométriques</td>
                                <td class="data"><?php echo $prices->oneWayDay; ?></td>
                                <td class="data"><?php echo $prices->returnJourneyDay; ?></td>
                            </tr>
                             <tr>
                                <td class="data">Min kilométrique</td>
                                <td class="data"><?php echo $prices->minDistanceDay; ?></td>
                                <td class="data"><?php echo $prices->minDistanceDayReturn; ?></td>
                            </tr>
                    </tbody>
                </table>
            </div>
        
            <div class="table-responsive w-75 mx-auto">
                <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle caption-top">
                <caption  class="text-light  text-center fs-6">Nuits</caption>
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" class="text-danger "></th>
                            <th scope="col" class="text-danger ">Simple</th>
                            <th scope="col" class="text-danger">Aller-retour</th>          
                        </tr>
                    </thead>
                    <tbody>                                 
                             <tr>
                                <td class="data">Tarifs kilométriques</td>
                                <td class="data"><?php echo $prices->oneWayNight; ?></td>
                                <td class="data"><?php echo $prices->returnJourneyNight; ?></td>
                            </tr>
                             <tr>
                                <td class="data">Min kilométrique</td>
                                <td class="data"><?php echo $prices->minDistanceNight; ?></td>
                                <td class="data"><?php echo $prices->minDistanceNightReturn; ?></td>
                            </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="table-responsive w-75 mx-auto">
                <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle caption-top">
                <caption  class="text-light  text-center fs-6">Autres</caption>
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" class="text-danger">Attente</th>           
                        </tr>
                    </thead>
                    <tbody>                                 
                            <tr>
                                <td class="data"><?php echo $prices->waitingRate;?></td>
                            </tr>
                    </tbody>

                    <thead class="align-middle mt-2">
                        <tr>
                            <th scope="col" class="text-danger">Minimum de perception</th>           
                        </tr>
                    </thead>
                    <tbody>                                 
                            <tr>
                                <td class="data"><?php echo $prices->minPerception; ?></td>
                            </tr>
                    </tbody>
                </table>
            </div>
        
        </div>
        <div class="text-center mt-5">
            <a href="/public/adminPrice/updateAdminPrice/<?php echo $prices->idPrice;?>/<?php echo trim($_SESSION['token']);?>" class=" text-center btn btn-dark text-danger mt-2">Modifier les tarifs</a>
        </div>
   
    </div>
</section>