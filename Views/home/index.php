                                            <!-- Affiche la vue de la page d'accueil  -->
<?php 

// Fichier langue
include 'init_lang.php';
// Ouvre la session
session_start();

$title =  $language->get('titlePageWelcome');
$page =  $language->get('titlePageWelcome');

if(isset($_SESSION['admin']))
unset($_SESSION['admin']);  
// Token créé avant la page d'authentification afin qu'il soit intégrer dans le hidden du formulaire
if (!isset($_SESSION['token'])) {
    // Si le token n'existe pas, on en assigne un
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    // Enregistrement du timestamp pour identifier le moment precis de la creation du token
    $_SESSION['token_time'] = time(); 
} else {
    unset($_SESSION['token']);
    unset( $_SESSION['token_time']);
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    $_SESSION['token_time'] = time();
}
// Si le Username est déclaré et donc on se situe dans l'espace client, 
// on redirige automatiquement sur la page de confirmation d'action
// pour quitter ou revenir à l'étape précédente.
if(isset($_SESSION['username'])) {
    header('location:index.php?controller=user&action=index');
} elseif(isset($_SESSION['username_admin'])){
    header('location:index.php?controller=authAdmin&action=index');
}
isset($_SESSION['username_admin']) ?? header('location:index.php?controller=panelAdmin&action=index');
?>    
            
<section class="diapo">
    <!-- Contenuer de diapo -->
    <div id="main_diapo" class="flex_row slides"></div>

    <a href="#" class="diapo__prev" title="Précédent">&lsaquo;</a>
    <a href="#" class="diapo__next" title="Suivant">&rsaquo;</a>
    <div class="slide__dot">
    <div id="container-dot" class="dots"></div>
    </div>
</section>

    <!-- BLOC PRINCIPAL -->

<div class="main_bloc">

    <section class="main__content flex_col">
        <img class="main__content-logo" src="image/taxi.webp" width="183" height="183" alt="logo réservation">
        <div class="main__content--change-width flex_col">
            <h2 class="main__content-title"><?php echo $language->get('welcomeTitleEstimate'); ?></h2>
            <article class="main__content-border flex_col">
                <p class="devisArticle"><?php echo $language->get('paragraph1'); ?>
                    <br><br><span><?php echo $language->get('paragraph1.1'); ?>,</span><br> <?php echo $language->get('paragraph1.2'); ?>,<br> <?php echo $language->get('paragraph1.3'); ?><br><?php echo $language->get('paragraph1.4'); ?>
                    <br><br><?php echo $language->get('paragraph1.5'); ?>  
                </p>
                <a href="index.php?controller=date&action=index&token=<?php echo trim($_SESSION['token']);?>" class="main__content-btn btnDevis" title="<?php echo $language->get('btn1'); ?>" class="book"><?php echo $language->get('btn1'); ?><i class="fi fi-rr-arrow-small-right"></i></a>
                <p><?php echo $language->get('paragraph2'); ?><br><br><?php echo $language->get('paragraph2.1'); ?>
                    <br><br><?php echo $language->get('paragraph2.2'); ?>
                </p>
            
            </article>
            <a href="index.php?controller=date&action=index&token=<?php echo trim($_SESSION['token']);?>"class="main__content-btn" title="<?php echo $language->get('btn2'); ?>" class="book"><?php echo $language->get('btn2'); ?><i class="fi fi-rr-arrow-small-right"></i></a>
        </div>
    
    </section>

    <section class="main__content flex_col">
        <img class="main__content-logo" width="183" height="183" src="image/events.webp" alt="logo évènement">
        <div class="main__content--change-width flex_col">
            <h2 class="main__content-title"><?php echo $language->get('titleEvents'); ?></h2>
            <article class="main__content-border--change ">
                <p><?php echo $language->get('bk2_para1'); ?><br><br> <?php echo $language->get('bk2_para1.1'); ?>
                    <br><br><?php echo $language->get('bk2_para1.2'); ?>
                </p>
            </article>
            <a href="index.php?controller=Contact&action=index" class="main__content-btn  main__content-btn--right" title="<?php echo $language->get('bk2_btn'); ?>"><?php echo $language->get('bk2_btn'); ?><i class="fi fi-rr-arrow-small-right"></i></a>
        </div>
    </section>

    <section class="main__content flex_col">
        <img class="main__content-logo" width="183" height="183" src="image/taxiCpam.webp" alt="logo transport medical">
        <div class="main__content--change-width flex_col">
            <h2 class="main__content-title"><?php echo $language->get('titleCare'); ?></h2>
            <article class="main__content-border">
                <p><?php echo $language->get('bk3_para1'); ?><br><br><?php echo $language->get('bk3_para1.1'); ?></p>
            </article>
            <a href="index.php?controller=Contact&action=index" class="main__content-btn" title="<?php echo $language->get('bk2_btn'); ?>"><?php echo $language->get('bk2_btn'); ?><i class="fi fi-rr-arrow-small-right"></i></a>
        </div>
    </section>
    
</div>