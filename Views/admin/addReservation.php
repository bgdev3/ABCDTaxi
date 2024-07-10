                                            <!-- Affiche la vue d'ajout d'un réservation Administrateur -->
<?php
$title = 'Admin - Ajout réservation';

if (!isset($_SESSION['username_admin'])) {
    header('location: index.php');
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
        <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
</section>
