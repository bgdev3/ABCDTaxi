                                        <!-- Affiche le fomrulaire de connexion de l'administrateur -->

<?php
$title = 'Admin - Authentification';
$page = "Admin-connexion"; 
// Si l'utilisateur est connecté, on renvoi vers le panel administrateur
unset($_SESSION['username']);
if (isset($_SESSION['username_admin'])) {
    header('location:index.php?controller=panelAdmin&action=index');
} 
?>

<section class=" container text-center  vh-100 d-flex align-items-center justify-content-center">
    <!-- Permet l eresponsive avec Boostrap -->
    <div class=" col-12 col-md-6 col-lg-4 ">
        <!-- S'il y a une erreur on l'affiche -->
        <?php if (!empty($error)) { ?>
            <div class="bg-danger "> <?php echo $error; ?></div>
        <?php } 
        // Si la variable est bien déclaré, on l'affiche
        // if (isset($addSignUpForm))
            echo $addSignUpForm;
            ?>
        <a href="index.php" class="btn btn-dark text-danger">Revenir</a>
        <!-- Appel du script Re-Captcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k"></script>
    </div>
</section>