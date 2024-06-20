
/**
 * Permet de vérifier le token re-Captcha passé en post dans les formulaires
 */
function loadRecaptchaToken() {
    grecaptcha.ready(function () {
        grecaptcha.execute('6LebG6MpAAAAAIDVxKKsnIql8WG-028Dvudz5l-k', { action: 'myForm' }).then(function (token) {
            var recaptchaResponse = document.getElementById('recaptchaResponse');
            recaptchaResponse.value = token;
        });
    });
}

// Exporte la fonction 
 export function loadRecaptcha() {
    loadRecaptchaToken();
    setInterval(function(){loadRecaptchaToken();}, 100000);
}