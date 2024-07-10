                    <!-- Vue de la lsite des transports effectués ou annulés -->
<?php
$title = $title = 'Admin - Historique transports';
?>

<section>

    <!-- Si une errreur est présente -->
    <?php if(!empty($errorAuth)) { ?>
        
        <div class="bg-danger col-12 col-md-8 col-lg-10 mx-auto text-center"> <?php echo $errorAuth; ?></div>

    <?php } else { ?>
        
        <div class="text-center">
            <h4 class="text-center text-center text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Historique des transports</h4>
        </div>
   
    <div class="admin-reservation col-12 col-sm-10 col-md-8 col-lg-12 mx-auto">
        <!-- select pour le choix des listes à afficher -->
        <?php echo $searchForm; ?>

        <div class="table-responsive">
            <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle mb-5">
                <thead class="align-middle" id="thead">
                    <tr>
                        <th scope="col" class="text-danger">Nom</th>
                        <th scope="col" class="text-danger">Prénom</th>
                        <th scope="col" class="text-danger">Réservé le</th>
                        <th scope="col" class="text-danger">Effectué le</th>
                        <th scope="col" class="text-danger ">Heure départ</th>
                        <th scope="col" class="text-danger">Lieu départ</th>
                        <th scope="col" class="text-danger">Destination</th>
                        <th scope="col" class="text-danger">Aller-retour</th>
                        <th scope="col" class="text-danger">Devis</th>
                        <th scope="col" class="text-danger">Date annulation</th>
                        <th scope="col" class="text-danger"></th>
                    </tr>
                </thead>
                <tbody id="searchTransport">
                    <?php
                        // Parcours les jointures complètes
                        foreach($list as $items => $reservation) {

                            foreach($reservation as $val) {  
                                // Convertit les dates au bon format
                                $date_transport = new DateTime($val->date_transport); 
                                $date_reservation = new DateTime($val->date_reservation); 
                                // Teste cancelation afin d'assigner les valeurs correspodantes avant de les insérer dans le tableau
                                if( $val->cancelation == 1 ) {
                                        $state = "Annulé";
                                        $date_cancelation = new DateTime($val->cancellationDate); 
                                        $date_cancelation =  $date_cancelation->format('d/m/Y');
                                } else {
                                    $state = $date_transport->format('d/m/Y');
                                    $date_cancelation = "-";
                                }
                                ?>
                                <tr>
                                    <td class="data"><?php echo $val->name ?></td>
                                    <td class="data"><?php echo $val->surname ?></td>
                                    <td class="data"><?php echo $date_reservation->format('d/m/Y') ?></td>
                                    <td class="data"><?php echo $state ?></td>
                                    <td class="data"><?php echo $val->departureTime; ?></td>
                                    <td class="data"><?php echo $val->departurePlace; ?></td>
                                    <td class="data"><?php echo $val->destination; ?></td>
                                    <td class="data"><?php echo $val->roundTrip; ?></td>
                                    <td class="data"><?php echo $val->price; ?></td>
                                    <td class="data"><?php echo $date_cancelation; ?></td>
                                    <td class="data"><a class='addReservation'title="Ajout réservation" href="index.php?controller=adminReservations&action=addReservationsAdmin&token=<?php echo trim($_SESSION['token']); ?>&id=<?php echo $val->idTransport_histo; ?>" >+</a></td>
                                </tr>
                        <?php }  
                        } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php } ?>
</section>