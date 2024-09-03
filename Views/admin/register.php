                                    <!-- Affiche le formulaire du renouvellement des identifiants -->
<?php
$title = 'Admin - S\'enregistrer';
$page = "Admin-connexion"; 

// S'il n'y a pas d'activté au de la de 20min, l'utilisateur est déconnecté
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');

} elseif (!isset($_SESSION['username_admin'])) {
    header('location:index.php');
}
?>

<section class=" container text-center d-flex align-items-center justify-content-center">
    
    <div class="col-12 col-md-6 col-lg-4">
        <!-- S'il y a une erreur on l'affiche-->
        <?php if (!empty($error)) { ?>
            <div class="bg-danger"> <?php echo $error; ?></div>
        <?php } 
            // On affiche le fomrulaire de renouvellement d'identifiant
            echo $addLogForm;
        ?>
         <!-- Appel du script Re-Captcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=**********"></script>
    </div>

</section>
