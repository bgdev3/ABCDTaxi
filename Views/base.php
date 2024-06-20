                                          <!-- Base commune à toutes les vues -->
<!-- Fichier de langues -->
<?php include 'init_lang.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="logo/favicon-32.webp">
   <!-- MétaDescription -->
   <meta name="description" content="ABCD Taxi pour appel et pré-réservation. Toutes distances. Départ 30kms autour de Tain-Tournon. Tout déplacement conventionné, professionnel ou privé.">
  
   <!-- Fonts chargé dans scss/base/typo -->
   <link rel="stylesheet" type="text/css" href="scss/style..css">
   
    <!-- script module.js -->
    <script type="module" src="js/main.js"></script>
   <!-- CDN Bootstrap seulement si l'administrateur est connecté -->
   <?php if(isset($_SESSION['admin'])) { ?>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
   <?php } ?>
   <title><?php echo $title; ?></title>
</head>
                    
<body>
   <div  class="wrapper ">
      <header id="header" class="header borderBottom">
       
                                          <!-- BARRE DE NAVIGATION -->
         <!-- Si aucun utilisateur n'est connecté, on affiche le menu conventionnel -->
         <?php if (!isset($_SESSION['username']) && !isset($_SESSION['admin'])) {  
                  require_once "../templates/navUser.php"; 

                  //   Sinon l'admin est connecté
               } elseif (isset($_SESSION['username_admin'])) {  
                  require_once "../templates/navAdmin.php";
            
               // Sinon c'est l'entête utilisateur qui est affiché  -->
               } elseif( isset($_SESSION['username'])) {
                   require_once "../templates/navUserAuth.php";
               } 
         ?>
       
      </header> 
      
      <main id="content">
         <?= $content ?>
      </main>

         <!-- Si on est sur le backOffice, le footer n'est pas affiché -->
      <?php if (!isset($_SESSION['admin'])) { 
               require_once "../templates/footerUser.php";
            } else { 
               require_once "../templates/footerAdmin.php";
            } 
      ?>

   </div>
</body>
</html>