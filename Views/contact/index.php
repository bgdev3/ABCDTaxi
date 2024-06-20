                                            <!-- Affiche la vue de la page contact -->
<?php
// Fichier de langues
include 'init_lang.php';
$title =  $language->get('titlePageContact');
$page = $language->get('titlePageContact'); 

isset($_SESSION['username_admin']) ?? header('location:index.php?controller=panelAdmin&action=index');
?>

<section class="main__content">
    <!-- Si sendingMail vaut TRUE c'est à dire si le mail est envoyé avec succés, on affiche la confimration -->
    <?php if ($sendingMail) { ?>

        <div class="reservation_info confirm">
            <h2 class="main__content-title"><?php echo $language->get('mailConfirm'); ?></h2>
            <a href="index.php" class="btnForm"><?php echo $language->get('confirmBack'); ?></a>
        </div>

        <!-- Sinon on réaffiche le formulaire avec un message d'erreur -->
    <?php } else { ?>

        <h2 class="main__content-title"><?php echo $language->get('titleContact'); ?></h2>
        <div class="reservation_info">
            <small>* (<?php echo $language->get('required'); ?>)</small>

            <!-- S'il y a un erreur on l'affiche -->
            <?php if(!empty($error)){ ?>
                    <span class="msgError"> <?php echo $error; ?></span>

                <?php }; 
                // Affiche le fomrulaire
                echo $addForm; ?>
        </div>

    <?php } ?>

</section>
<!-- Appel du script Re-captcha -->
<script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>