
// Charge les modules pour la page d'accueil
window.addEventListener('DOMContentLoaded', async ()=>{
    const slideShow = await import("./modules/slideShow.js");
    const manageMenu = await import("./modules/menuManager.js");
    const scrollTop = await import("./modules/scrollTop.js");
  
    // Rend invisible le loader lorsque la page est chargé
    document.querySelector('.loader-container').classList.add('loader-hide');
    document.querySelector('.loader').classList.add('loader-hide');
    
    // Execute les modules
    slideShow.slideShow(), manageMenu.menuManager(), scrollTop.scrollToTop();
})


let book = document.querySelector('div.date');
let getMap = document.querySelector('#map');
let customer = document.querySelector('.table-responsive');
let getForm = document.querySelector('form');
let searchTransport = document.querySelector('#searchTransport');

// Page Reservation
if(book != 'undefined'){
    async function slides() {
        const date = await import("./modules/dateReservation.js");
        date.dateReservation();
    }
    slides();
}

// Page Map et itinéraire
if(getMap){
    async function map() {
        const date = await import("./modules/googleMap.js");
        date.initMap();
    }
    map();
}

// Pages des tableaux
if(customer) {
    async function tableResponsive() {
        const date = await import("./modules/reservationResponsive.js");
        date.responsiveArray();
    }
    tableResponsive();
}

// Pages des fomrulaires
if(getForm) {
   
    async function form() {
        const date = await import("./modules/verif.js");
        date.verif();
    }
    form();
}

// Page admin
if(searchTransport) {
    async function search() {
        const date = await import("./modules/getHistory.js");
        date.getHistory();
    }
    search();
}



 