                            <!-- Affiche la liste des réservations utilisateur -->
<?php 
include 'init_lang.php';
$title = $language->get('titlePageReservation');

// Si le temps de connexion est supérieur à 20min sans activité, 
// L'utilisateur est déconnecté 
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');

} elseif (!isset($_SESSION['username']))
    header('location:index.php');
?>
<!-- 
    Si la liste des réservations est vide, on affiche une info 
    et on invite à se rediriger vers la page d'accueil
    Aussi, si l'utilisateur n'est pas déclaré,
    un retour à la page d'accueil est requis
 -->
 <!-- Si la liste est vide -->
<?php if (empty($list)) { ?>

    <section class="main__content_confirm">
        <div class="reservation_info confirm">
            <!-- Si info n'est pas vide et si username n'est pas déclaré -->
            <?php if (!empty($info) || !isset($_SESSION['username'])) { ?>
                <!-- On indique que tous les transports de la liste ont été supprimés -->
                <h2 class="main__content-title"><?php echo $info; ?></h2>
                <a href="index.php" class="btnForm"><?php echo $language->get('backHome'); ?></a>

            <?php } ?>
        </div>
    </section>
<!-- Sinon on affiche la liste des réservations -->
<?php } else { ?>

    <section class="main__content">
        <h2 class="main__content-title"><?php echo $language->get('titleReservation'); ?></h2>
        <div class="main__content_table">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th ><?php echo $language->get('date'); ?></th>
                        <th ><?php echo $language->get('passengers'); ?></th>
                        <th ><?php echo $language->get('hour'); ?></th>
                        <th ><?php echo $language->get('departurePlace'); ?></th>
                        <th ><?php echo $language->get('destination'); ?></th>
                        <th ><?php echo $language->get('customerRoundTrip'); ?></th>
                        <th ><?php echo $language->get('price'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // On boucle dans le tableau $list qui contient la liste des créations
                    // Et on intègre le token dans l'URL
                    foreach ($list as $value){
                    //    Converti la date en format
                        $date = new DateTime($value->date_transport);
                        echo "<tr>";
                        echo "<td  class='data'>" . $date->format('d-m-Y') . "</td>";
                        echo "<td  class='data'>" . $value->nbPassengers . "</td>";
                        echo "<td  class='data'>" . $value->departureTime . "</td>";
                        echo "<td  class='data'>" . $value->departurePlace . "</td>";
                        echo "<td  class='data'>" . $value->destination . "</td>";
                        echo "<td  class='data'>" . $value->roundTrip . "</td>";
                        echo "<td  class='data'>" . $value->price . "&euro;</td>";
                        echo "<td class='flexTd'><a class='change-link' href='index.php?controller=reservations&action=updateTransport&id=$value->idTransport&token=".trim($_SESSION['token'])."'><i class='fi fi-tr-pen-circle'></i></a>
                                                <a class='delete-link'href='index.php?controller=reservations&action=deleteTransport&id=$value->idTransport&token=".trim($_SESSION['token'])."'><i class='fi fi-sr-cross-circle'></i></a></td>";
                        echo "</tr>";
                    } 
                    ?>

                </tbody>
            </table>
        </div>
    </section>
<?php } ?>
       
