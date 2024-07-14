// Import du module recaptcha
import{ loadRecaptcha } from "./recaptcha.js";
import { fetchManager } from "./fetch.js";

/**
 * Verification des entrées coté client de formulaire
 * Tant que les champs ne sont pas valide, l'envoi en POST ne s'effectue pas.
 */
 export function verif(){

    // Récupère les boutons de validation des différents formulaire
    let btnSend = document.querySelectorAll('input[type=submit].btnForm');
    let btnAdmin = document.querySelectorAll('input[type=submit].btnAdmin');

    let agree =  document.getElementById('agree');
    let validationCheck = document.querySelector('label[for=agree]');

    let dateForm = document.querySelector('input[type=date]');
    // Si l'élément HTML est présent, paramètre la date du jour en minimale
    if(dateForm)  {
        let now = new Date();
        let month = (now.getMonth() +1) < 10 ?  '0' + (now.getMonth() +1) : now.getMonth() +1;
        dateForm.min = now.getFullYear() + '-' + month + '-' + now.getDate();
        dateForm.max = "2060-12-12";
    }
    
   

    function validateForm(e) {
     
        // Récupère tous les champs obligatoires
        let chps = document.querySelectorAll('[required]');
        // Booléen permettant de valider l'envoi
        let valid = true;
        // Stop l'évenèment par défault du type submit
        e.preventDefault();
        // Sur chaque champs, lors du focus, on efface les styles d'erreur et le message s'il y en a
        // puis à la perte du focus, on re teste le champ.
        chps.forEach(el =>{
            el.addEventListener('focus', ()=>{
                reset();
                el.classList.remove('error');
            })
            el.addEventListener('blur', ()=>{
                validation(el);
            });
        });

        // On affiche un message personnalisé pour la case à cocher
        if(agree != null){
            agree.addEventListener('change', ()=>{
                if( agree.checked == true){
                    validationCheck.classList.remove('errorAgree');
                } else {
                    validationCheck.classList.add('errorAgree');
                }
            });
        };
        
        // On teste la validité de chaque champs avec l'api HTML5
        chps.forEach(el =>{
            if (!validation(el)) {
                valid = false;
            }
        });
        // Si non valide, on crée un message d'erreur que l'on affiche
        // Sinon si toutes les conditions sont remplis, on envoi le formulaire
        if (!valid) {
            reset();
            let span = document.createElement('span');
            let contain;
            // Effectue un fetch afin de récupérer la langue sélectionée par l'utilisateur et adapté le message d'erreur
            fetchManager('index.php?controller=contact&action=langState')
            .then(reponse=>reponse.json())
            .then(reponse => {
                 contain =  reponse == 'en' ?  'Please complete the required fields correctly.' :  "Veuillez renseigner correctement les champs requis.";

                 span.innerHTML = contain;
                 span.classList.add('msgError');
            })

            // affiche le message d'erreur, au dessus des champs de formulaire 
            let displayError = document.querySelector('label[for=name]');
            displayError ? displayError.before(span) : document.querySelector('#myForm').before(span);
        } else {

            document.querySelector('.loader-container').classList.remove('loader-hide');
            document.querySelector('.loader').classList.remove('loader-hide');
            reset();
            document.getElementById('myForm').submit();         
        }
    }


    /**
     * Verifie les champs du formulaire  
     * 
     * @param {string} ch valeur du champs récupèrer
     * @returns {bool} retourne true ou false
     */
    let validation = function validationChps(ch){
        if (ch.checkValidity()) {
            ch.classList.add('validate');
            if(agree != null)
            validationCheck.classList.remove('errorAgree');
            return true;
        }
        else {
            ch.classList.add('error');
            if(agree != null)
            validationCheck.classList.add('errorAgree');
           return false;
        }
    }


    /**
     * Supprime le message d'erreur s'il existe
     */
    function reset(){
        let span = document.querySelector('span.msgError');
        if(span !== null)
            span.remove();
    }

    // validateForm est appelé sur le bouton de validation correspondant au formulaire
    // sur lequel on se trouve.
    [btnSend, btnAdmin].map((el) => {
        el.forEach(btn=>{
            btn == null ?  btn == 'undefined' : btn.addEventListener('click', validateForm);
        });
    });

    // Affiche le loader au clic des boutons de confirmation d'action
    let btnDel = document.querySelectorAll('.btnConfirm');
    btnDel.forEach( el => {
        el.addEventListener('click', () => {
            document.querySelector('.loader-container').classList.remove('loader-hide');
            document.querySelector('.loader').classList.remove('loader-hide');
        })
    });

    // Si le re-captcha est présent, récupère le formulaire et applique le re-captcha
    // let captcha = document.querySelector('.grecaptcha-badge');
    // if(captcha) 
        Array.from(document.querySelectorAll('form[id=myForm]')).map(loadRecaptcha);
    
}