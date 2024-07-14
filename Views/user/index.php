                                        <!-- Affiche la vue de la page de connexion utilisateur -->
<?php
// Fichier de langues
include 'init_lang.php';
$title =  $language->get('titlePageMyResrevation');

// Si l'utilisateur est connecté, on redirige vers la liste de réservation
if (isset($_SESSION['username']))
    header('location:index.php?controller=reservations&action=index');
elseif (isset($_SESSION['username_admin']))
    header('location:index.php?controller=panelAdmin&action=index');
elseif(!isset($_SESSION['token']))
    header('location:index.php');
?>

<section class="main__content_confirm">
    <div class="reservation_info confirm">

        <!-- S'il y a une erreur -->
        <?php if (!empty($error)) { ?>

            <span class="msgError"> <?php echo $error; ?></span>

        <?php } 
        // S'il l'utilisateur n'est pas connecté
        if (!isset($_SESSION['username'])) { ?>

            <h2 class="title_confirm"><?php echo $language->get('titleCustomer'); ?></h2>
            <div class="reservation_info">

            <?php echo $addAuth; ?>
    </div>
        <!-- Appel du script Re-Captcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
        <!-- Sinon on indique et invite à se déconnecter s'il navigue jusqu'ici -->
        <?php } else { ?>

            <h2 class="title_confirm"><?php echo $language->get('cautionCancel'); ?></h2>
            <?php echo $addAuth; ?>
        <?php } ?>
        
    </div>
</section>
