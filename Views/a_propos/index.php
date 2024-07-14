                                                    <!-- Affiche la vue A propos -->
<?php 
// fichier de langues
include 'init_lang.php';
// Variables retourné à base.php pour l'affichage
$title =  $language->get('titlePageAbout');
$page =  $language->get('titlePageAbout'); 
if(isset($_SESSION['username_admin']))
    header('location:index.php?controller=panelAdmin&action=index');
elseif (isset($_SESSION['username']))
    header('location:index.php?controller=reservations&action=index');
?>

<!-- Affiche la vue de la page contact -->
<section class="main__content-apropos">
    <h2 class="main__content-title"><?php echo $language->get('titleAbout'); ?></h2>
   
    <article class="apropos">

        <h3> <?php echo $language->get('titleAbout2'); ?></h3>

        <p> <?php echo $language->get('about_para1'); ?> <br><br>
            <?php echo $language->get('about_para2'); ?><br><br>
            <?php echo $language->get('about_para3'); ?><br><br>
            <?php echo $language->get('about_para4'); ?><br><br>
            <em> <?php echo $language->get('about_para5'); ?></em>
        </p>

        <div class="apropos_flex">
            <img src="logo/logo-abcd.svg" class="apropos_flex_logo" alt="Logo abcd Taxi" title="ABCD Taxi">
        </div>

    </article>
</section>
