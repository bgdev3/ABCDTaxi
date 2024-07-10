                                                <!-- Footer affiché hors Back Office -->

<footer class="footer">

    <aside class="flex_row footer_logo">
        <img src="logo/cv.webp" width="100" height="100" alt="<?php echo $language->get('vitalcard'); ?>" title="<?php echo $language->get('vitalcard'); ?>"> 
        <img src="logo/logo-conventionne.webp" width="50" height="50" alt="<?php echo $language->get('cpam'); ?>" title="<?php echo $language->get('cpam'); ?>"> 
        <img src="logo/cb.webp" width="50" height="50" alt="<?php echo $language->get('payments'); ?>" title="<?php echo $language->get('payments'); ?>"> 
    </aside>

    <div class="flex_col">
        <div class="footer--space-between">
            <h3 class="footer-title"><?php echo $language->get('foo_connect'); ?></h3>
            <div class="flex_row ">
                <a href="https://www.facebook.com/profile.php?id=100093468794865" title="facebook"><img src="logo/facebook.webp" width="100" height="100" alt="facebook"></a>
                <a href="https://www.instagram.com/abcdtaxi" title="Instagram"><img src="logo/instagram.webp" width="100" height="100" alt="Instagram"></a>
            </div>
        </div>  

        <div class=" flex_col footer--reverse">
            <div class="">
                <h3 class="footer-title"><?php echo $language->get('foo_location'); ?></h3>
                <address>1, lotissement l'Abricotine<br> 26800 PORTES LES VALENCES</address>
            </div>

            <div>
                <h3 class="footer-title"><?php echo $language->get('foo_contact'); ?></h3>
                <div class="flex_col">
                    <a href="tel:+33664142034" title="<?php echo $language->get('contact'); ?>"> <i class="fi fi-tr-phone-call "></i> 06 64 14 20 34 </a>
                    <a href="mailto:abcd.taxi.26@orange.fr" title="Email"> <i class="fi fi-rr-envelope-download"></i> abcd.taxi.26@orange.fr</a>
                </div>
            </div> 
        </div>
    </div>

    <nav class="footer__nav ">
        <ul class="">
            <li><a href="index.php" title="<?php echo $language->get('welcome'); ?>"><?php echo $language->get('welcome'); ?></a></li>
            <li><a href="index.php?controller=date&action=index&token=<?php echo trim($_SESSION['token']);?>" title="<?php echo $language->get('book'); ?>"><?php echo $language->get('book'); ?></a></li>
            <li><a href="index.php?controller=user&action=index" title="<?php echo $language->get('manage'); ?>"><?php echo $language->get('manage'); ?></a></li>
            <li><a href="index.php?controller=contact&action=index" title="<?php echo $language->get('contact'); ?>"><?php echo $language->get('contact'); ?></a></li>
            <li><a href="index.php?controller=about&action=index" title="<?php echo $language->get('about'); ?>"> <?php echo $language->get('about'); ?></a></li>     
        </ul>
    </nav>
    
    <p><a href="index.php?controller=home&action=mentions" title="<?php echo $language->get('notice'); ?>"><?php echo $language->get('notice'); ?></a> | <?php echo $language->get('rights'); ?> | <a href="index.php?controller=authAdmin&action=index"><?php echo $language->get('admin'); ?></a></p>
</footer>