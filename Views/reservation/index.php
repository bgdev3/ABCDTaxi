                                                    <!-- Affiche la vue des dates -->
<?php
include 'init_lang.php';
$title =  $language->get('titlePageReservation');
$page = $language->get('titlePageReservation');

if(isset($_SESSION['username_admin']))
    header('location:/public/panelAdmin');

// Si le l'admin revient sur cette vue sans se connecter, 
// mais ayant rendu active bootstrap, les sessions sont vidées
if(isset($_SESSION['admin'])) {
    unset($_SESSION['admin'], $_SESSION['username_admin']);  
}

?> 

<section class="main__content">

    <aside class="headerStep">
            <div class="step">1</div><div>2</div><div>3</div>
    </aside>
    
    <h2 class="main__content-title"><?php echo $language->get('titleDate'); ?></h2>
    <div class="reservation">
       
        <div class="date">
            <!-- Boucle sur l'array recu par le controller afin d'afficher les hours
                A chaque itérations on crée les div correspondantes
            -->
            <?php  
                foreach($dates as $date){ 
                    echo "<div class='day shape'>" . $date . "<i class='fi fi-rs-angle-small-down'></i></div>" ;
            ?>

                <div class="gridHours">

                    <?php
                        // Assigne le timezone correspondnat selon la langue déclaré
                        $locale = 'fr_FR'; $timezone = 'Europe/Paris';
                        if(isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') {
                            $locale = 'en_US';
                            $timezone = 'Europe/London';
                        } 
                        // Convertit la date en chaine de caractère au fomrat correspondant afin de la tester pour l'affichage
                        $date_database = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE, $timezone, IntlDateFormatter::GREGORIAN);
                        $date_database = datefmt_parse($date_database, $date) +  86400;
                        $date_database = date('Y-m-d', $date_database);
                        // Parcours le tableau d'heures
                        foreach($times as $val) {
                            $disabled = "";
                            // A chaque occurrences de $times, vérifie si la valeur est présente
                            // dans les enregistrements récupérées coté serveur.
                            // Si c'est le cas, un attribut css est créer et stocké afin de l'assigné au lien crée par la suite
                           
                            foreach($_SESSION['dbHours'] as $time){
                                // Retire le 0 des dizaine si présent
                                $heure = $time->departureTime[0] == '0' ? substr($time->departureTime, 1) : $time->departureTime;
                                // Si l'heure corrspond à celui présent dans l'enregistrement parcouru
                                // ainsi que la date, on assigne l'attribut css
                                if($val == $heure && $date_database == $time->date_transport) 
                                    $disabled = "disabled";
                            }
                            echo  "<a href='/public/estimate' class='hours shape hide " . $disabled ."' >" . $val . "</a>";
                        } 
                    ?>

                </div>
            <button id="btn-reservation" class="moreDays hide "><?php echo $language->get('seeMore'); ?></button>
            <?php } ?>

        </div>
        <div id="more"><?php echo $language->get('moreDate'); ?></div>

    </div>
</section>