                                    <!-- Affiche le formulaire du renouvellement des identifiants -->
<?php
$title = 'Admin - S\'enregistrer';
$page = "Admin-connexion"; 
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
        <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
    </div>

</section>