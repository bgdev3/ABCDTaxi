                                <!-- Affiche la vue de la page du formulaire des infos personnelles -->
<?php
include 'init_lang.php';
$title = $language->get('titlePageReservation');
// $page = "reservation";

// Si l'utilisateur est connecté une redirection au liste de réservation est effectué indiquant
// une info de déconnexion en cas de slide arrière
if (isset($_SESSION['username'])) 
    header('location:index.php?controller=user&action=index');
// elseif(isset($_SESSION['username_admin']))
//     header('location:index.php?controller=panelAdmin&action=index');
// Sinon si l'utilisateur n'est pas connecté ET que l'heure de réservation n'est sélectionné en cas d'un slide arrière
// un eredirection est effectué au debut du processus de réservation
elseif (!isset($_SESSION['time'])) 
    header('location:index.php');


// Si un idClient est déclaré c'est que q'un utilisateur est enregistré, l'envoi du mail s'est effectué
// et du coup on affiche les informations correspondante
// Sinon c'est le formulaire d'enregistrement qui est affiché
if (isset($_SESSION['idUser'])) { ?>

    <section class="main__content_confirm">
        <div class="reservation_info confirm">

            <!-- Si un message d'erreur de mail est présent, on affiche le message -->
            <?php if (!empty($message)) { ?>

                <h2 class="main__content-title"><?php echo $message; ?></h2>
                <a href="index.php" class="btnForm" ><?php echo $language->get('backHome');?></a>

            <?php } else { ?>

                <h2 class="main__content-title"><?php echo $language->get('confirmSendTitle');?></h2>
                <p><?php echo $language->get('confirmSendPara');?></p>
                <a href="index.php" class="btnForm"><?php echo $language->get('backHome');?></a> 

            <?php } ?>

        </div>

    </section>
          
    <?php } else { ?>

        <section class="main__content">
            <aside class="headerStep">
                    <div class="step">1</div><div class="step">2</div><div class="step">3</div>
            </aside>

            <h2 class="main__content-title"><?php echo $language->get('titleRegistration'); ?></h2>
            <div class="reservation_info">
                <small>* (<?php echo $language->get('required'); ?>)</small>

                <!-- Si un erreur de formulaire est présente, on l'affiche -->
                <?php if (!empty($error)) { ?>

                    <span class="msgError"> <?php echo $error; ?></span>

                <?php }; 
                    echo $addForm; 
                ?>

            </div>
        </section>
        <!-- Appel du script de Re-Captcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
    <?php } ?>


 