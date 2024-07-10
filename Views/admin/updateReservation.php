                                    <!-- Affiche la vue de modification de résrevation Administrateur -->
<?php
$title = 'ABCD Taxi - Admin-Mise à jour';

if(!isset($_SESSION['username_admin'])){
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
    <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
    
</section>