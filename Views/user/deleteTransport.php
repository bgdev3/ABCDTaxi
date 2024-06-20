                                <!-- Vue de suppression d'un transport utilisateur -->
<?php  
// Fichier de langues
include 'init_lang.php';
 $title = $language->get('titlePageReservation');
?>

<!-- Affiche la page de confirmation de suppression de transport -->
<section class="main__content_confirm">
    <div class="reservation_info confirm">

        <?php if (!empty($error)) { ?>

            <h2 class="main__content-title"><?php echo $error; ?></h2>
            <a href="index.php" class="btnForm"><?php echo $language->get('backHome'); ?></a>

        <?php } else { ?>

            <h2 class="title_confirm"><?php echo $language->get('confirmCancel'); ?><br></h2>
            <h3 class="title_confirm"><?php echo $language->get('confirmCancel2'); ?></h3>
            
            <?php echo $form; 
            } ?>
    </div>
</section>