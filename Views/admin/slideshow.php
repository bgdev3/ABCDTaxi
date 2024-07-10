                                            <!-- Affiche la vue du diaporama -->
<?php
$title = 'Admon - Slideshow';
$page = "Admin-Slideshow"; 

if(!isset($_SESSION['username_admin'])){
    header('location:index.php');
}
?>

<section>
    <!-- Affiche les diapos récupérées -->
    <h4 class="text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Ajouter ou supprimer des slides au diaporama</h4>

    <section class="row d-flex justify-content-center m-5 ">
        <!-- Boucle sur la variable $slides afin de creer sur chaque diapo
         une img contenant le chemin de la diapo correspondante
         et un lien de suppression de la diapo
        -->
       <?php foreach($slides as $slide) { ?>
        
           <div id='img_slide' class="col-12 col-md-4 col-lg-4 m-2 position-relative border">
                <img class="img-fluid" src="<?php echo $slide->picture_path; ?>" alt="">
                <a class="btn btn-dark text-danger btn-sm position-absolute top-0 end-0 rounded-0" href="index.php?controller=adminSlideshow&action=deleteSlide&id=<?php echo $slide->IdPicture; ?>&token=<?php echo trim($_SESSION['token']); ?>">X</a>
            </div>
          
       <?php } ?>        
    </section>
    <p class="text-center text-warning mt-5">Afin d'optimiser l'affichage du diaporama, le format d'image recommandé est de 1280 x 470.<br>
    <!-- Ici, s'il n'y a pas d'erreur on affiche le champ d'upload du formulaire -->
    <div class="col-sm-12 col-md-8 col-lg-6 mx-auto">

        <?php
        if (!empty($error)) { ?>
            <div class="bg-danger "> <?php echo $error; ?></div>
        <?php }; echo $fileForm; ?>
        
    </div>
</section>
