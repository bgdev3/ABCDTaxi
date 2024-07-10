                    <!-- Affiche la liste des réservations en cours -->
<?php
$title = 'Admin - Réservations';
$page = "Admin-Réservations en cours";

if(!isset($_SESSION['username_admin'])){
    header('location:index.php');
}
?>

<section>
        
    <div class="text-center">
        <h4 class="text-center text-center text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Liste des réservations</h4>
        <a href="index.php?controller=adminReservations&action=addReservationsAdmin&token=<?php echo trim($_SESSION['token']); ?>" class="btn btn-dark text-danger mt-2 ">Ajouter une réservation</a>
    </div>

    <div class="admin-reservation col-12 col-sm-10 col-md-8 col-lg-12 mx-auto">
    
        <div class="table-responsive">
            <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle mb-5">
                <thead class="align-middle">
                    <tr>
                        <!-- <th scope="col">#</th> -->
                        <th scope="col" class="text-danger ">Date</th>
                        <th scope="col" class="text-danger">Nom</th>
                        <th scope="col" class="text-danger ">Prénom</th>
                        <th scope="col" class="text-danger ">Nb. passagers</th>
                        <th scope="col" class="text-danger">Téléphone</th>
                        <th scope="col" class="text-danger">Départ</th>
                        <th scope="col" class="text-danger">Destination</th>
                        <th scope="col" class="text-danger">Heure </th>
                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Parcours les jointures complètes
                    foreach($reservations as $items => $val) { 
                        // Parcours les enrtegistrements des jointures
                        foreach($val as $reservation) { 
                        $date = new DateTime($reservation->date_transport); ?>
                            <tr >
                                <td class="data"><?php echo $date->format('d/m/Y') ?></td>
                                <td class="data"><?php echo $reservation->name; ?></td>
                                <td class="data"><?php echo $reservation->surname; ?></td>
                                <td class="data"><?php echo $reservation->nbPassengers; ?></td>
                                <td class="data"><?php echo $reservation->tel; ?></td>
                                <td class="data"><?php echo $reservation->departurePlace; ?></td>
                                <td class="data"><?php echo $reservation->destination; ?></td>
                                <td class="data"><?php echo $reservation->departureTime; ?></td>
                                <td class="flexTd"><a href="index.php?controller=adminReservations&action=updateReservationAdmin&token=<?php echo trim($_SESSION['token']);?>&id=<?php echo $reservation->idTransport;?>" class="btn btn-transparent bg-dark text-success"><i class='fi fi-tr-pen-circle'></i></a>
                                <a href="index.php?controller=adminReservations&action=deleteReservationAdmin&token=<?php echo trim($_SESSION['token']);?>&id=<?php echo $reservation->idTransport;?>" class="btn btn-transparent  bg-dark text-danger"><i class='fi fi-sr-cross-circle'></i></a></td>
                            </tr>
                        <?php  } 
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>