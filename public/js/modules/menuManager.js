
/**
 * Applique les différentes classes CSS au header afin de gérer l'animation sticky, 
 * le menu burger selon les formats, le blocage du scroll en menu ouvert sur mobile.
 */
export function menuManager(){

    function displayMenu(){

        let navMobile, checkBurger, checkFlags, page, body;
        page = document.querySelector('html');
        body = document.querySelector('body');
        checkBurger = document.querySelector('.header__burger');
        checkFlags = document.querySelector('.header__nav-flags');
        navMobile = document.querySelector('.header__nav');
    
        if(checkBurger !== null) {
            checkBurger.addEventListener('click', ()=>{
                checkBurger.classList.toggle('burger');
                navMobile.classList.toggle('showMenu');
                checkFlags.classList.toggle('flag-position');
                page.classList.toggle('unSroll');
                body.classList.toggle('unScroll');
            });
        }
       
    }

    function hideMenu(){
        let header,  position = 0;
        header = document.getElementById('header');
        window.addEventListener('scroll', ()=>{
            if((document.body.getBoundingClientRect()).top < position ){
                header.classList.add('hideMenu');
            }
            else{
                header.classList.remove('hideMenu');
            }
            position = document.body.getBoundingClientRect().top;
        });
    }

    function flag() {
        let flag = document.getElementById('flag');
        let link = document.getElementById('lang');
        
        link.addEventListener('click', ()=> {
            console.log(flag);
            if(link.href == 'index.php?lang=en')
                flag.src = 'logo/anglais.svg';
            else
                    flag.src = 'logo/france.svg';
        })
    }
   
    displayMenu(), hideMenu();
}