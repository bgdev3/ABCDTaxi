                                                <!-- Footer affiché hors Back Office -->

<footer class="footer">

    <aside class="flex_row footer_logo">
        <img src="/public/logo/cv.webp" width="100" height="100" alt="<?php echo $language->get('vitalcard'); ?>" title="<?php echo $language->get('vitalcard'); ?>"> 
        <img src="/public/logo/logo-conventionne.webp" width="50" height="50" alt="<?php echo $language->get('cpam'); ?>" title="<?php echo $language->get('cpam'); ?>"> 
        <img src="/public/logo/cb.webp" width="50" height="50" alt="<?php echo $language->get('payments'); ?>" title="<?php echo $language->get('payments'); ?>"> 
    </aside>

    <div class="flex_col">
        <div class="footer--space-between">
            <h3 class="footer-title"><?php echo $language->get('foo_connect'); ?></h3>
            <div class="flex_row ">
                <a href="https://www.facebook.com/profile.php?id=100093468794865" title="facebook"><img src="/public/logo/facebook.webp" width="100" height="100" alt="facebook"></a>
                <a href="https://www.instagram.com/abcdtaxi" title="Instagram"><img src="/public/logo/instagram.webp" width="100" height="100" alt="Instagram"></a>
            </div>
        </div>  

        <div class=" flex_col footer--reverse">
            <div class="">
                <h3 class="footer-title"><?php echo $language->get('foo_location'); ?></h3>
                <address>1, lotissement l'Abricotine<br> 26800 PORTES LES VALENCES</address>
            </div>
      
           
                <div id="map-footer"></div>
                <script>
                    (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                        key: "YOUR_KEY",
                        v: "weekly",
                    
                    });
                    </script>
         
           
                  
      
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
            <li><a href="/public/" title="<?php echo $language->get('welcome'); ?>"><?php echo $language->get('welcome'); ?></a></li>
            <li><a href="/public/date/index/<?php echo trim($_SESSION['token']) ?? '';?>" title="<?php echo $language->get('book'); ?>"><?php echo $language->get('book'); ?></a></li>
            <li><a href="/public/user" title="<?php echo $language->get('manage'); ?>"><?php echo $language->get('manage'); ?></a></li>
            <li><a href="/public/contact" title="<?php echo $language->get('contact'); ?>"><?php echo $language->get('contact'); ?></a></li>
            <li><a href="/public/about" title="<?php echo $language->get('about'); ?>"> <?php echo $language->get('about'); ?></a></li>     
        </ul>
    </nav>
    
    <p><a href="/public/home/mentions" title="<?php echo $language->get('notice'); ?>"><?php echo $language->get('notice'); ?></a> | &copy;2024 - ABCD Taxi | <?php echo $language->get('createdBy'); ?> <a href="https://bgdev.fr">bgdev</a> | <a href="/public/authAdmin"><?php echo $language->get('admin'); ?></a></p>
</footer>
