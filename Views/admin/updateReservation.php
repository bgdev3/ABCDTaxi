                                    <!-- Affiche la vue de modification de résrevation Administrateur -->
<?php
$title = 'ABCD Taxi - Admin-Mise à jour';

// S'il n'y a pas d'activté au de la de 20min, l'utilisateur est déconnecté
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');
    
} elseif (!isset($_SESSION['username_admin'])) {
    header('location:index.php');
}
?>

<section>

    <h4 class="text-center text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Modification de réservation client</h4>
    <!-- S'il y a une erreur -->
    <?php if(!empty($error)) { ?>
        <div class="bg-danger col-12 col-md-8 col-lg-6 mx-auto text-center"> <?php echo $error; ?></div>
    <?php } 
    // Affiche le formulaire
    echo $form; ?>
    
    <!-- Appel du script Re-Captcha -->
    <script src="https://www.google.com/recaptcha/api.js?render=**********"></script>
    
</section>
