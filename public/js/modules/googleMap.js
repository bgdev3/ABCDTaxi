// Import du module fetch
import { fetchManager } from "./fetch.js";


/**
 * Permet d'initialisé la map javascript via l'API GoogleMaps Javascript
 * Paramètre l'autocompletion des champs de destinations
 * et récupère les différents paramètre utilisateur en les passant à la méthode calcRoute
 */
export async function initMap(){

    // Options de la map
    const mapOptions =  {
        zoom: 7,
        center: { lat: 44.93884504475424, lng: 4.897501540613616 },
        mapTypeId: 'roadmap'
        };
    
    // Création de la map
    let map;
    let containMap = document.getElementById('map');

    // Si on se situe sur la page contenant la div de la map
    if (containMap !== null) {
        // Charge la librairi map
        const { Map } = await google.maps.importLibrary("maps");
        // Créer la map
        map = new Map(document.getElementById("map"), mapOptions);
    
        // Création de l'objet Direction service
        let directionsService = new google.maps.DirectionsService();
        // Creation de l'objet Direction Render qui servira pour l'afficahge
        let directionsRenderer = new google.maps.DirectionsRenderer();

        // Affiche la map
        directionsRenderer.setMap(map);
    
        // Récupère la chechbox et du temps d'attente
        const roundTrip = document.getElementById('check');
        const wait = document.getElementsByName('wait');
        // Initialise la valeur de la checkbox à false
        let choice = false;

        // Au clic, affiche la zone de texte et son label du temps d'attente
        //  et assigne la variable choice selon la valeur de la checkbox
        roundTrip.addEventListener('click', ()=>{
            for( const el of wait){
                el.classList.toggle('noneWait');
            }
            choice = roundTrip.checked ? true : false;
        });

        // récupère les champs d'itinéraires
        let itineraire = document.querySelectorAll('input[name=destination');
        // Appel de la fonctions d'autocomplétion de google
        autocomplete(itineraire);
        const btnGo = document.getElementById('btnGo');
        const btnDevis = document.querySelector('.btnMap');
        
        // Au click sur l'envoi d'itinéraire
        btnGo.addEventListener('click' ,()=>{
            let places = [];
            let waitValue = document.querySelector('input#wait').value;
            console.log(waitValue);
            // Au clic de l'élément, on attribut le booleen et on lance les requêtes http aux APIs
            // Si une ou aucune valeur ne sont renseignés, on affiche un message d'erreur.
            itineraire.forEach(el => {
                // Stocke dans un array les adresses afin de les transmettre en php et les stocké en bdd
                places.push( el.value);
                // Si les champs ne sont pas vides
                // et on appel calcRoute
                // et on affiche un message d'erreur si les champs ne sont pas valide
                if(!el.value == ""){
                    calcRoute(directionsService, directionsRenderer, choice, waitValue, btnDevis);

                } else {

                    btnDevis.classList.add('none');
                    // Récupère la langue sélectionné
                    fetchManager('index.php?controller=estimate&action=langState')
                    .then(reponse=>reponse.json())
                    .then(reponse=>{
                        let contain = reponse == 'en' ? "Please fill out the fields to get a quote!" : "Veillez remplir les champs afin d'obtenir un devis ! ";
                        document.getElementById('estimate').innerHTML = "<p class='error'>" + contain,tain + "</p>";
                    })
                   
                }
            });   
            // Envoi en Fetch Post les adresses valides de googles des destinations
            const destination = {
                depart: places[0],
                destination: places[1]
            };
            fetchManager('index.php?controller=registration&action=getPlaces', destination);
        });
    }
}


/**
 * Permet de mettre en place l'autocomplétion de google dans les champs texte
 * Retourne en asynchrone les adresses valides 
 * 
 * @param {array} itineraire contenant les 2 champs d'itinéraires
 */
async function autocomplete(itineraire){

    // Option d'autocomplétion google
    const optionPlaces = {
        types: ['establishment', 'geocode'],                                             //Localise les lieux par Géocode
        componentRestrictions : {country : ['fr']},                     //Limite les prédictiosn en france
        fields : ['place_id', 'geometry', 'name']                       // Augmente la précision des prédictions ( Limite la facturation)
        };
    
    //Appelle la librairie Places
    const {Autocomplete} = await google.maps.importLibrary("places");
    itineraire.forEach(el =>{

        // On appelle places Autocomplete qui renvoit un tableau d'objet contenant les prédictions
        new Autocomplete(el, optionPlaces);
    });
}


/**
 * Permet d'effectuer une requête d'itinéraire transmise à l'API Direction service
 * Et dans le même temps lors de l'envoi de la requête
 * on envoit les données complémentaire via l'API Fetch afin d'actualiser l'affichage
 * 
 * @param {Object} directionsService  Permet de calculer un itinéraire, recoit les requêtes d'itinéraire et renvoit un chemin efficace
 * @param {Object} directionsRenderer Permet d'afficher le résultat directement sur une carte
 * @param {bool} choice  Transmet la valeur booleene
 * @param {int} waitValue Transmet le temps d'attente
 */
function calcRoute(directionsService, directionsRenderer, choice, waitValue, btnDevis) {
    // Requête
    const request = {
        origin:{query: document.getElementById('start').value},
        destination: {query: document.getElementById('end').value},      
        travelMode: google.maps.TravelMode.DRIVING,   
        unitSystem: google.maps.UnitSystem.METRIC
        }
    directionsService.route(request, (result, status) => {

        if (status == google.maps.DirectionsStatus.OK) {
            // Recupere la div à mettre à jour en temps réel
            let estimate = document.getElementById('estimate');
            // On affiche le btnDevis uniquement si les requêtes google sont valides
            btnDevis.classList.remove('none');
            // Recupère le temps en secondes du trajet par directionService, la distance, la valeur de la checkbox et le temps d'attente
            // Envoi au php via API Fetch afin de traiter avec les données
            // Une fois récupérer dans la promesse résolu,  on affiche les résultats renvoyés
            let tpsTrajet = [result.routes[0].legs[0].duration.value, result.routes[0].legs[0].distance.value, choice, waitValue];
            // Envoit via API Fetch les données paramétrer en JSON afin de les traiter en PHP
            fetchManager('index.php?controller=estimate&action=quoteCalculation', tpsTrajet)
            .then(reponse=>reponse.json())
            .then(reponse => {
              console.log(reponse);
              
                // Tableau de traduction selon la langue sélectionnée
                let lang = ['Distance: ', 'Tarifs approximatif: ', 'Temps de trajet estimé: ', 'Heure d\'arrivée estimée', '(inclut le temps d\'attente: 0.50 cts/min)', 'Heure d\'arrivée au rendez-vous estimée' ];
                if (reponse.lang == 'en') 
                    lang = ['Distance: ', 'Approximate rates: ', 'Estimated travel time: ', 'Estimated arrival time', '(includes waiting time: 0.50 cts/min)', 'Estimated time of arrival at the meeting'];
                
                // Si un transport est en allé simple est validé, on affiche ces informations
                // sinon un version plus minimaliste et plus cohérentes sont affichées pour un aller-retour
                if (reponse.choice == false) {
                    estimate.innerHTML = "<h3>" + lang[0] + "</h3><p>" + result.routes[0].legs[0].distance.text + "</p><h3>" +lang[1] +"</h3><p>"
                    + reponse.price +"</p><h3>" + lang[2]+"</h3><p>"+ result.routes[0].legs[0].duration.text +
                    "</p><h3>" + lang[3] + "</h3><p>"+ reponse.time + "</p>";
                } else {
                    estimate.innerHTML = "</p><h3>"+lang[1] +"</h3><p>" + reponse.price +
                    "<br><small>"+lang[4] +"</small></p><h3>"+lang[5] +"</h3><p>"+ reponse.time + "</p>";
                }
            })
            .catch(() => {
                // Recupère la langue sélectioné et applique l'alert correspondante
                fetchManager('index.php?controller=estimate&action=langState')
                .then(reponse=>reponse.json())
                .then(reponse=>{
                    let alertMessage = reponse == 'en' ? "Une erreur est survenue, vous allez être rediriger à l'étape pécédente en cliquant 'ok'" : "An error has occurred, you will be redirected to the previous step by clicking 'ok'";
                    alert(alertMessage);
                    window.location ="index.php";
                })
               
            });
            // Affiche l'itinéraie
            directionsRenderer.setDirections(result);
        } else {
            // Efface les routes si non OK
            directionsRenderer.setDirections({routes : []});
            btnDevis.classList.add('none');

            // On affiche un message d'erreur
            document.querySelector('.btnMap').classList.add('none');

            fetchManager('index.php?controller=estimate&action=langState')
            .then(reponse=>reponse.json())
            .then(reponse=>{
                let emptyQuotation = reponse == 'en' ? "Please provide a valid destination!" : "Veuillez indiquez une destination valides !";
                estimate.innerHTML = "<p class='error'>" + emptyQuotation + "</p>";
            })
           
        }
    });
}


