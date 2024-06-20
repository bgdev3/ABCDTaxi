// Import du module fetch 
import { fetchManager } from "./fetch.js";


/**
 * Permet de gérer les différentes fonctionnalités Front relatives
 * aux dates de réservations
 */
export function dateReservation(){

    
    /**
     * Permet l'ajout des jours supplémentaire à afficher lors du clic
     * sur "PLus de disponibilités"
     */
    function addDays(){ 
        let date = document.querySelector('div#more');
        let nb = 0;
        // Récupère l'évènement au click
        if(date != null){
            date.addEventListener('click', ()=>{
                // A chaque click la variable nb est incrémenté à 7
                // correspondant à l'affichage par default du nombre de jours à afficher
                // On la passe en GET au controller Date par l'API Fetch
                // afin de mettre à jour l'affichage
                // Enfin on affiche les éléments en bouclant sur la reponse recu
                nb+=7;
                if(nb > 21){
                    alert("Les réservations ne sont disponible uniquement sur 28 jours.");
                    date.innerHTML ="Merci de vérifier vos dates..."
                    return;
                }

                // Appel de l'API Fetch
                fetchManager(`index.php?controller=date&action=addDays`, nb)
                .then(reponse=>reponse.json())
                .then((reponse)=>{
                    // Boucle sur le premier index de la reponse afin d'afficher les dates
                    for(let el of reponse['date']){
                        let timing="";
                        // Boucle sur la deuxième reponse afin d'afficher les horaires
                        // A chaque itérations
                        for(let time of reponse['time']){
                            timing += `<a href='index.php?controller=estimate&action=index' class='hours shape hide'> ${time} </a>`;
                        }
                        let contain = `<div class='day shape'>${el}<i class='fi fi-rs-angle-small-down'></i></div>
                                    <div class='gridHours'>${timing}</div> 
                                    <button id="btn-reservation" class="reservation_button hide moreDays ">Voir plus</button>`;
                        document.querySelector('.date').innerHTML+= contain;
                        // SelectHours à chaque creation du bouton "Voir plus"
                        selectHours();
                        // Appel la fonction afin d'écouter le click sur les nouveaux éléments
                        // et d'appliquer les classes correspondantes
                        hideHours();
                        // Appel de addHours afin d'appliquer la fonctionnalité du bouton "voir plus" à la réponse de Fetch
                        addHours();
                    }
                })
                .catch(error => alert(`Une erreur est survenue: ${error}`))
            });     
        }          
    }
    
    
    /**
     * Gère l'affichage des horaires au clic de l'élément date correspondant
     * 
     * Récupère l'élément date cliqué, affiche le bloc horaire correspondant
     * et lui applique les classes dédié.
     * Affiche le bon chevron au click de l'élément date
     */
    function hideHours(){
        let date, child, btn, day;
        date =  document.querySelectorAll('div.day.shape');
        // Sur chaque élément date cliqué

        date.forEach(el=>{
            el.addEventListener('click', ()=>{
                day = el.textContent;
                // console.log(day);
                fetchManager('index.php?controller=estimate&action=getDayOfWeek', day);
                // fetch(`index.php?controller=estimate&action=getDayOfWeek&day=${day}`);
            // On toggle la mise en forme actice à l'élément cliqué
                el.classList.toggle('activeDay');
                // On annule les bordures pour l'effet souhaité à gridHours
                el.nextElementSibling.classList.toggle('border');
                // On récupère le bouton "Voir plus" correspond et un toggle la classe hide
                // en fonction du clic
                btn = el.nextElementSibling.nextElementSibling;
                btn.classList.toggle('hide');

                // On récupère les horaires afin de les afficher ou les cacher au click
                // de la date correspondante
                child = el.nextElementSibling.children;
                Array.from(child).forEach(item=>{
                    item.classList.toggle('hide');
                });
                // On affiche en meme temps le bon chevron up ou down
                if(el.firstElementChild.className == 'fi fi-rs-angle-small-down'){
                    el.firstElementChild.className ='fi fi-rs-angle-small-up';
                }
                else{
                    el.firstElementChild.className = 'fi fi-rs-angle-small-down';
                }
            });
        });
    }


    /**
     * Gère l'affichage des horaires supplémentaires aprés le clic 
     * sur le bouton "Voir plus" et assigne les indisponibiltés horaires
     */
    function addHours(){
      
        // On recupère tous les boutons "Voir plus"
        let btn = document.querySelectorAll('#btn-reservation');
        btn.forEach((el)=>{
          
        // Récupère gridHours et au click récupère le contenu php des horaires supplémentaires
        // via l'API Fetch
            let contain = el.previousElementSibling;
            el.addEventListener('click',()=>{
                // Récupère la date dont le bouton 'Voir plus' fait parti du même conteneur
                let day = el.previousElementSibling.previousElementSibling.textContent;
                // Appel de la fonction de formatage de la date
                let date = formatDate(day);
                fetchManager(`index.php?controller=date&action=addHours`)
                .then(reponse=>reponse.json())
                .then((reponse)=>{
                //   Boucle sur la réponse : à chaque itération on crée les div horaires
                // et on l'assigne à contain
                    for(let time of reponse['hours']){
                        let att="";
                        // Boucle sur les enregistrements de réservations récupérés coté serveur
                        for(let dbTime of reponse['dbHours']){
                            // Si date_transport vaut la date sélection ET que departureTime vaut une heure du tableau de données, 
                            // alors l'attribut disabled est créé et assigné au lien créé
                            if(dbTime.date_transport == date && time == dbTime.departureTime) {
                                att = 'disabled';
                            }
                        }
                        contain.innerHTML += `<a href='index.php?controller=estimate&action=index' class='hours shape ${att}'> ${time}</a>`;
                    }
                    selectHours();
                })
                .catch(error => alert(`Un erreur est survenue: ${error}`))
                // Enfin, on on fait un display none du btn "Voir plus"
                // afin de ne plus le rendre disponible
                el.style.display="none";
            });
        })
    }
    

    /**
     * Formatte la date afin de la comparer
     * 
     * @param {string} day Date du jour sélectionné
     * @return retourne la date en format US afin de la comparer
     */
    function formatDate(day) {

        let month = {'janvier' : 'january', 'février' : 'febrary', 'mars' : 'march', 'avril' : 'april', 'mai' : 'may', 'juin' : 'june', 'juillet' : 'july',
        'aout' : 'august', 'septembre' : 'september', 'octobre' : 'october', 'novembre' : 'november', 'décembre' : 'december'};
        // Split la string en tableau
        day = day.split(' ');
        // Parse la date en timestamp
        let date = Date.parse(day[1] + " " + month[day[2]] + " " + day[3]);
        console.log(date);
        // Créer un objet date
        date = new Date(date);
        // rajoute un 0 au format s'il y a moins de 2 chiffres
        let dateMonth = (date.getMonth() + 1) <= 10 ? '0' + (date.getMonth() + 1) : date.getMonth()+ 1;
        let dateDay = date.getDate() <= 10 ? '0' + date.getDate() : date.getDate();
        // Récupère le fomrat de date voulu
       date = date.getFullYear() + '-' + dateMonth + '-' + dateDay;
      
       return date;
    }
   

    /**
    * Récupère l'horaire sélectionné, la traite et l'envoi au php
    */
    function selectHours() {

        let btnHours = document.querySelectorAll('a.hours.shape');
        let chaine;
        btnHours.forEach( el => {
            // Captur l'évènement afin d'attendre la redirection du lien
            // avant d'effectuer le fetch dans le but d'éviter des conflit de content type
            el.addEventListener('click', (e)=>{
                e.preventDefault();
                chaine = el.textContent;
                console.log(chaine);
                fetchManager(`index.php?controller=estimate&action=getTime`, chaine);
                // Redirige à 50ms d'écart
                setTimeout(() => {window.location="index.php?controller=estimate&action=index"}, 50);
                // Au clic split la chaine, convertis la chaine en seconde
                // et l'envoit via Fetch afin de la stocker en session et la manipuler plus tard
            });
        })  
    } 
    addDays(), addHours(), hideHours(), selectHours();
}
