// Import du module fetch
import { fetchManager } from "./fetch.js";
import { responsiveArray } from "./reservationResponsive.js";

/**
 * Permet la sélection des différents historique de transport
 * en testant la valuer du select afin de gerer l'affichage des éléments,
 * puis lors de la recherche, transmet la valeur au php afin de récupérer les données
 * correspondantes selon l'action choisi. L'API Fetch retourne les données dans une promesse
 * puis gère les différents affichage selon l'action voulu.
 */
export function getHistory() {

    let select = document.getElementById('sort');
    let search = document.getElementById('search');
    let searchLabel = document.getElementById('searchLabel');
    let searchDate = document.getElementById('searchDate');
    let searchDateLabel = document.getElementById('searchDateLabel');
    let option, val = [];                                      
                                              
    // Lors du click sur le select
    if(select) {
        select.addEventListener('change', ()=> {
            // Stocke la valeur du sélect
           option = select.options[select.selectedIndex].value;
            // Teste option et affiche les éléments HTML correspondant selon le select sélectionné
            switch(option) {
    
                case 'name' :
                    [search, searchLabel].map((el) => {el.classList.remove('d-none')});
                    [searchDate, searchDateLabel].map((el) => {el.classList.add('d-none')});
                break;
    
                case 'date' :
                    [searchDate, searchDateLabel].map((el) => {el.classList.remove('d-none')});
                    [search, searchLabel].map((el) => {el.classList.add('d-none')});
                break;
                default :
                    [search, searchDate, searchLabel, searchDateLabel].map((el) => {el.classList.add('d-none')});
                break;
            }
        });
    
    }
    
   
    // Lors du clic sur le bouton de recherche
    document.getElementById('sortTransport').addEventListener('click', ()=>{
        // assigne la valeur du select
        // puis teste selon la valeur du select, assigne la valeur de l'input correspondant
        val.push(option);
            if(option == 'name')
        val.push(search.value);
            if(option == 'date') 
        val.push(searchDate.value);
        // Envoi les données au controller correspondant
        fetchManager('index.php?controller=historyTransport&action=selectHistory', val)
        .then(reponse=>reponse.json())
        .then(reponse => {
           
            let date = [], contains = [], contain = [], labels =[];
           
            // Si la promesse est résolu        
                // Boucle sur les données retournées
            for(let data of reponse) {
               
                labels = ['Nom', 'Prénom', 'Réservé le', 'Effectué le', 'Heure de départ', 'Lieu départ', 'Destination', 'Aller-retour', 'Devis', 'Date annulation'];
                // Si cancellationDate est null, envoi des données récupérées à formater
                data.cancellationDate == null ? 
                date =formateDate([new Date(data.date_reservation), new Date(data.date_transport), '-']) :
                date = formateDate( [new Date(data.date_reservation), 'Annulé', new Date(data.cancellationDate)]);
                        
                // Si Case 'name'
                if (option == 'name') {
                    // Tableau des valeurs de la bdd récupérer dans la promesse
                    contain  = [date[0], date[1], data.departureTime, data.departurePlace, data.destination, data.roundTrip, data.price, date[2]];
                    labels.splice(0, 2);
                    contains.push(contain);
                // Sinon
                } else {
                    // Boucle sur le deuxième tableau de données
                    for (let el of data) {
                    
                        // Réintègre l'array afin de s'assurer que splice ne modifie pas les index du tableau
                        date = el.cancellationDate == null ? formateDate([new Date(el.date_reservation), new Date(el.date_transport), '-']) : 
                                                    formateDate( [new Date(el.date_reservation), 'Annulé', new Date(el.cancellationDate)]);
                    
                        // Si tous les transport ou 'date', 'cancel' ou 'done', ajuste les labels et les données récupérées à afficher
                        if ( option == 'all') {
                            
                            contain = [el.name, el.surname, date[0], date[1], el.departureTime, el.departurePlace, el.destination, el.roundTrip, el.price, date[2]];
                            contains.push(contain);

                        } else if(option == 'date'){

                            contain = [el.name, el.surname, date[0], date[1], el.departureTime, el.departurePlace, el.destination, el.roundTrip, el.price, date[2]];
                            contains.push(contain);
                        } else if (option == 'cancel') {
                            
                            contain = [el.name, el.surname, date[0], date[1], el.departureTime, el.departurePlace, el.destination, el.roundTrip, el.price, date[2]];
                            contains.push(contain);
                            
                        } else if (option == 'done') {
                            
                            contain = [el.name, el.surname, date[0], date[1], el.departureTime, el.departurePlace, el.destination, el.roundTrip, el.price, date[2]];
                            contains.push(contain);
                        }
                    } 
                }
            }
            if(contain.length === 0){
                alert("Aucun transport ne correspond à la recherche");
            } else{
                templateData(labels, contains);
            }
            // Sinon indique un message adapté
            // Permet le responsive du tableau d'affichage
            responsiveArray();   
        })
        // Capture l'erreur
        .catch(error => {
            alert("Aucun transport ne correspond à la recherche");
        });
        // Vide le tableau de valeur
        val = [];
    })    


    /**
     * Permet la construction du tableau de données à afficher
     * en récupérant en paramètre les données serveur
     * 
     * @param {array} labels Contient les th du tableau à construire
     * @param {array} contain Contient les données td du tableau à construire
     */
    function templateData(labels, contain) {
        let containThead = "", containBody = "";
        // Applique sur chaque élément du tableau un éélément html th en les stockant
        labels.map(el => { containThead += `<th scope='col' class='text-danger'>${el}</th>`; })
        document.getElementById('thead').innerHTML = `<tr>${containThead}</tr>`;
        
        // Boucle sur chaque élément de contain afin de créer un tr puis des td sur les éléments précédemment construit
        for(let array of contain) {
            // Sur chaque itération : 
            // stocke les tr, crée des td en bouclant sur les tableau de données de chaque itérations
            containBody += "<tr>";

            for(let el of array) {
                containBody += `<td class='data'>${el}</td>`;
            }
            containBody += "</tr>";
            // Appelle le contenu sur les éléments HTML dédiés.
            document.getElementById('searchTransport').innerHTML = containBody;
        }      
    }

    /**
     * Formate les dates récupérées de la bdd dans un format conforme
     * @param {Date} date Tableau de données à formater pour l'affichage des différentes dates
     * @returns {array} Tableau des données formatées
     */
    function formateDate(date) {

        let day, data = [];
        // Mappe date et si l'occurence est de type Date, on la formate
        date.map(el => {
            if (el instanceof Date ) {
                day = el.toLocaleDateString("fr");
                data.push(day);
                // Sinon elle est ajoutée au tableau de données
            } else {data.push(el);}
        });
        return data;
    }
}