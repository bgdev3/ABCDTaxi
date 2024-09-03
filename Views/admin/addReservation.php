                                            <!-- Affiche la vue d'ajout d'un réservation Administrateur -->
<?php
$title = 'Admin - Ajout réservation';

// S'il n'y a pas d'activté au de la de 20min, l'utilisateur est déconnecté
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');
    
} elseif (!isset($_SESSION['username_admin'])) {
    header('location:index.php');
}
?>

<section>
   
    <h4 class="text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Ajout réservation client</h4>
    <?php 
       
            if (!empty($error)) { ?>
                <div class="bg-danger col-12 col-md-8 col-lg-10 mx-auto text-center"> <?php echo $error; ?></div>
            <?php } 
                // Si le formulaire est déclaré 
              echo $addForm; 
            ?>
        
        <!-- Appel du script Re-Captcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=**********"></script>
</section>
