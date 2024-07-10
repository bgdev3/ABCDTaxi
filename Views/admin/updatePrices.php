                                        <!-- Vue de mise à jour des tarifications -->
<?php
$title = $title = 'Admin - Tarification';

if(!isset($_SESSION['username_admin'])){
    header('location:index.php');
}
?>

<section>

    <div class="text-center">
        <h4 class="text-center text-center text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Tarification</h4>
    </div>

    <?php if (!empty($error)) { ?>

            <div class="bg-danger "> <?php echo $error; ?></div> 

    <?php } 
        echo $form; 
    ?>
    
</section>