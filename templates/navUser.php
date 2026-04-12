                                <!-- Navigation afficher pour un utilisateur non authentifié -->
<!-- Ficher de langues -->
<?php include 'init_lang.php'; ?>

<div class="header__logo flex_row" >
    <div class="header__logo-phone">
        <a href="tel:0664142034" title="Contact" alt="Icone téléphonique"><i class="fi fi-tr-phone-call"></i> </a>
        <a href="tel:+33664142034" title="Contact" alt="Numéro de téléphone"><span>06 64 14 20 34</span> </a> 
    </div>

    <a href="/public/<?php echo '?lang=' . $lang; ?>">
        <h1><img  src="/public/logo/logo-abcd.svg" width="100" height="100" alt="ABCD Taxi" title="<?php echo $language->get('welcome'); ?>"></h1>
    </a>
                        <!-- MENU BURGER -->
    <div class="header__burger">
        <span></span>
    </div>
</div>

                                                    <!-- MENU DE NAVIGATION -->
<nav id="nav_menu" class="header__nav">
    <ul class="header__nav-list">

                <!-- Teste la valeur de la langue sélectionné, a défault la valeur est 'fr'
                    Ceci afin de paramétrer le lien à chaque choix -->
        <?php if (isset($lang) && $lang == 'fr') { 
                $href = '/public/?lang=en'; $flag = '/public/logo/france.svg'; $class = 'header__nav-flags'; $title = 'Langue francais';
            } else {
                $href = '/public/?lang=fr'; $flag = '/public/logo/anglais.svg'; $class = 'header__nav-flag_en'; $title = 'Langue anglais';
            }
        ?>
            
        <li><a href="<?php echo $href; ?>" class="<?php echo $class; ?>" title="<?php echo $class; ?>" > <img src="<?php echo $flag; ?>" with="50" height ="50" alt="Choix de langues" ></a></li> 
        <li class="header__nav-item "><a href="/public/" title="<?php echo $language->get('welcome'); ?>" class="<?php if(isset($page) && $page ==  $language->get('titlePageWelcome')) echo "active-link ";?>link-underline link-underline-opacity-0"><?php echo $language->get('welcome'); ?></a></li>
        <li class="header__nav-item "><a href="#" class="header__nav-bottom <?php if(isset($page) && $page== $language->get('titlePageReservation')) echo "active-link";?>" title="<?php echo $language->get('reservation'); ?>"><?php echo $language->get('reservation'); ?><span><i class="fi fi-rs-angle-small-down"></i></span></a>
           
            <ul class="header__nav__sousMenu">
                <li><a href="/public/date/index/<?php echo trim($_SESSION['token']);?>" title="<?php echo $language->get('book'); ?>" id="book" ><?php echo $language->get('book'); ?></a></li>
                <li><a href="/public/user" title="<?php echo $language->get('manage'); ?>"><?php echo $language->get('manage'); ?></a></li>
            </ul>
        </li>

        <li class="header__nav-item"><a href="/public/contact" class="<?php if(isset($page) && $page== $language->get('titlePageContact')) echo "active-link";?>" title="<?php echo $language->get('contact'); ?>" ><?php echo $language->get('contact'); ?></a></li>
        <li class="header__nav-item"><a href="/public/about" class="<?php if(isset($page) && $page==  $language->get('titlePageAbout')) echo "active-link";?>" title="<?php echo $language->get('about'); ?>" ><?php echo $language->get('about'); ?></a></li>
    </ul>
    
    <p class="abcd">Arrivez Bien à Chaque Destination</p>
</nav> 
