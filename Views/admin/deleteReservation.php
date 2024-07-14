                                        <!-- Vue qui affiche confimration de suppression de réservation Admin -->
<?php
$title = 'Admin - suppression réservation';

// S'il n'y a pas d'activté au de la de 20min, l'utilisateur est déconnecté
if (isset($_SESSION['token_time']) && (time() - $_SESSION['token_time'] > 1200)) {
    session_unset(); session_destroy(); header('location:index.php');
    
} elseif (!isset($_SESSION['username_admin'])) {
    header('location:index.php');
}
?>

<section>
        <div class="text-center col-12 col-sm-10 col-md-8 col-lg-10 mx-auto mt-5">
            <h4 class="border border-warning  fs-5 fst-italic text-danger rounded  col-12 col-md-6 col-lg-4 mx-auto p-2">Supprimer définitivement ?</h4>
            <!-- Affiche al confirmation -->
            <?php echo $form; ?>
        </div>
</section>