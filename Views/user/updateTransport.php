                                        <!-- Vue de mise à jour d'un transport utilisateur -->
<?php 
// Fichier de langues
include 'init_lang.php'; 
$title = $language->get('titlePageReservation');
// Si le temps de connexion est supérieur à 20min sans activité, 
// L'utilisateur est déconnecté 
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');
    
} elseif (!isset($_SESSION['username']))
    header('location:index.php');
// Formate la date pour l'affichage
$date = new DateTime($transport->date_transport);
?>

<section class="main__content">
  
    <h2 class="main__content-titleUpdate"> <?php  echo $language->get('titleUpdate'); echo $date->format('d-m-Y') ;?></h2>
    <p class="info"><?php  echo $language->get('customerInfo'); ?></p>

    <!-- Si error n'est pas vide -->
    <?php if (!empty($error)) { ?>
        <!-- Affiche l'erreur -->
        <span class="msgError"> <?php echo $error; ?></span>
    <?php } ?>

    <div class='updateShape'>
        
        <div class="updateTransport">
           <!-- Affiche le formulaire de mise à jour -->
            <?php echo $updateForm; ?>
        </div>

        <h2><?php  echo $language->get('else');?></h2>
        <div class="newReservation">
            <p><?php  echo $language->get('subTitleUpdate');?></p>
            <!-- Récupère l'id du tansport sélectionner afin de le supprimer et de rediriger vers le processus de réservation -->
            <!-- Affiche les boutons de suppressions et de nouvelle réservation -->
                <?php echo $newReservation; ?>
        </div>      
    </div>
</section>
