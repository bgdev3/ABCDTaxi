                                    <!-- Affiche la vue de la page du devis -->
<?php

use App\Models\PriceModel;
// Fichier de langues
include 'init_lang.php';
$title = $language->get('titlePageReservation');
// Tant que l'utilisateur est connecté, il est automatiquement
// redirigé vers la proposition de déconnexion.
// Sinon si l'heure et donc la date ne sont pas enregistrées en session
// on redirige à l'étape précédente
if (isset($_SESSION['username'])) 
    header('location:/public/user');
if(isset($_SESSION['username_admin']))
    header('location:/public/panelAdmin');
 elseif (!isset($_SESSION['time'])) 
    header('location:/public/date/index/'. $_SESSION['token']);

// Si le l'admin revient sur cette vue sans se connecter, 
// mais ayant rendu active bootstrap, les sessions sont vidées
if(isset($_SESSION['admin'])) {
    unset($_SESSION['admin'], $_SESSION['username_admin']);  
}
// On regénère ici le token de sécutité en cas de modification de réservation 
// en ré effectuant le parcours de réservation complet.
// Sinon le token précédant ne fonctionnerait pas.
if (!isset($_SESSION['token'])) {
    // Si le token n'existe pas, on en assigne un
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    // Enregistrement du timestamp pour identifier le moment precis de la creation du token
    $_SESSION['token_time'] = time(); 
} else {
    unset($_SESSION['token']);
    unset( $_SESSION['token_time']);
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    $_SESSION['token_time'] = time();
}
?>

<section class="main__content">

    <aside class="headerStep">
            <div class="step">1</div><div class="step">2</div><div>3</div>
    </aside>

    <h2 class="main__content-title"><?php echo $language->get('titleEstimate');?></h2>
    <div class="reservation_devis">
      
        <div class="devis flex_row ">
            <article class="itineraire">
                <div >
                    <!-- Affiche les champs de destinations -->
                    <?php echo $addLabel; ?>
                    <input type="button" id="btnGo" value="Go !">
                </div>
                
                <aside> 
                    <div id="map"></div>
                    <!-- Script d'hamorcage de la google map -->
                    <script>
                        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",
                            l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,
                            e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");
                            for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);
                            a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));
                            a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));
                            d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                            key: "AIzaSyBha66sj8fHFN7hyxebyMn1avD03oRhCww",
                        });
                    </script>
                    
                </aside>

            </article>
            <article id="estimate">
                <?php echo $language->get('estimate');?>
            </article>
        </div>
        <a href="/public/registration" class="btnDevis btnMap none"> <?php echo $language->get('btnEstimate');?></a>
    </div>


    <small class="smallDevis"> 
    
      

        <?php  echo $language->get('minPerception');
                    $modelPrice = new PriceModel();
                    $priceModel = $modelPrice->findAll();
                    $minPerception = $priceModel->minPerception;
                    echo $minPerception;
            ?> 
            euros.<br>

        <?php echo $language->get('conditions');?>
    </small>

</section>
