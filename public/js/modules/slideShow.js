import { fetchManager } from "./fetch.js";
/**
*Fonction qui gère l'affichage et le timing du diaporama 
*/
 export function slideShow(){
    let slide, slider, decal, start, next, prev, dots, slideWidth, urls = [], count=0;
    slide = document.getElementById('main_diapo');
    next = document.querySelector('a.diapo__next');
    prev = document.querySelector('a.diapo__prev');
    let screenWith = screen.width;
    // Si le slider est présent
    if(slide){
        // Effectue un fetch afin de récupèrer les diapos stockées sur le serveur
        fetchManager('index.php?controller=adminSlideshow&action=addSlide', screenWith)
        .then(reponse=>reponse.json())
        .then(reponse => {
            
            // Assigne dans un array le chemin de chaque diapo dans la résolution de promesse
            for(let item of reponse.slides){
                urls.push(item['picture_path']);
            }
            // Récupère et stocke uniquement les valeur de l'objet correspondant au chemin
            urls = Object.values(urls);

            // Crée les éléments img en passant le chemin des images
            createImg(urls, slide, reponse.size['w'], reponse.size['h']);

            // Convertit la nodeListe en array afin d'obtenir la longeur de l'array
            // puis de le passer à createDots afin de créer autant de dots que d'img
            slider = Array.from(document.querySelectorAll('img.diapo__slide'));
            createDots(slider.length);
            // Récupère tous les dots crées
            dots = document.querySelectorAll('span.slide__dot__item');
            setClass(dots, slider);
            slideDots();
        });
       
        // récupère la taille des images
        slideWidth = slide.getBoundingClientRect().width;
        console.log(slideWidth);
        // lance les fonctions
        startTimer(),touchFinger(slide);
        // évènements
        slide.addEventListener('mouseover', stopTimer);
        slide.addEventListener('mouseout', startTimer);
        next.addEventListener('click', slideNext);
        prev.addEventListener('click', slidePrev);

        //Redimensionne la fenetre pour le responsive
        window.addEventListener('resize', ()=>{
            slideWidth = slide.getBoundingClientRect().width;
            // réactualise le size lors du changement de la taille d'écran
            slideNext();
        });
    }

    // Fonction qui fait défiler vers la droite.
     function slideNext(){
        count++;
        if(count == slider.length){
            count = 0;
        }
        nextSlide(decal, slideWidth, count, slide);
        setClass(dots, slider);
    }

     // Fais défiler vers la gauche
     function slidePrev(){
        count--;
        if(count < 0){
            count= slider.length -1;
        } 
        nextSlide(decal, slideWidth, count, slide);
        setClass(dots, slider);
    }

    // Permet au clic sur les dots le défilements des photos
    function slideDots(){
        
        for(let i = 0; i < dots.length; ++i){
            dots[i].addEventListener('click', ()=>{
                count  = i;
                nextSlide(decal, slideWidth, count, slide);
                setClass(dots, slider);
            });
        }
    }

    // Fonction qui supprime les classes en bouclant et les rajoute
    // selon l'index de count
    function setClass(dt, sl){
        for(let i=0; i<sl.length; ++i){
            sl[i].classList.remove('slide-show');
            dt[i].classList.remove('active');
        }
        dt[count].classList.add('active');
        sl[count].classList.add('slide-show');
    }

    // Fonction qui décale les slides
    function nextSlide(dec, slw, ct, sl){
        dec = -slw * ct;
        sl.style.transform=`translateX( ${dec}px)`;
    }

    // Les timers
    function startTimer(){
        start = setInterval(slideNext, 2000);
    }

    function stopTimer(){
        clearInterval(start);
    }   

    /**
     * Gère les différents slide au doigts de l'utilisdateur
     */
    function touchFinger(){
       
        if(screen.width <= 1024) {
            let distance; let touch, start=0; 
            // let between = 20;
            // Au premier point de contact
            slide.addEventListener("touchstart", function(evt) {
                // Récupère les "touches" effectuées
                touch = evt.changedTouches[0];
                start = touch.pageX;
                distance = 0;
            }, {passive:true});
            // Stop l'évènement au simple clic
            slide.addEventListener('touchmove', (evt)=>{
                evt.stopPropagation();
            }, {passive:true})
            // Quand le contact s'arrête
            slide.addEventListener("touchend", function(evt) {
                 touch = evt.changedTouches[0];
                //  console.log(touch.length());
                 distance = touch.pageX - start;
                // Si le slide effectué > 0, on change l'image appropriée
                if(distance > 0){
                    slideNext();
                }else if(distance < 0){
                   slidePrev();
                }
            });
        }
    }
}


/**
 * Crée les élements imgs
 * @param [urls] Collection des photos
 * @param [slide] Conteneur des imgs
 */
function createImg(urls, slide, w, h){
   
    urls.forEach(el => {
        let img  = document.createElement('img');
        img.setAttribute('src', el);
        img.setAttribute('width', w);
        img.setAttribute('height', h);
        img.setAttribute('alt', 'Image de taxi');
        img.setAttribute('class', 'diapo__slide slide-show');
        slide.append(img);
    });
}


/**
 * Crée les élément dots
 * @param [occ] Nombre d'occurence correspondant au nombre d'image crée
 */
function createDots(occ) {
    // Boucle sur le nombre d'occurence
    for(let i = 0; i < occ; i++){
        let dots = document.createElement('span');
        // Ajoute la classe active uniquement au premier élément span crée
        i == 0 ? dots.setAttribute('class', 'slide__dot__item active') : dots.setAttribute('class', 'slide__dot__item');
        document.querySelector('div.slide__dot').appendChild(dots);
    }
}

