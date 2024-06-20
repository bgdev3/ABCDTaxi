
/**
 * Permet de factoriser l'utilisation de Fetch en POST afin que les données
 * soient sécurisées
 * 
 * @param {string} url Récupère l'url à contacter
 * @param {array} data Récupère les données à paramétrer
 * @returns reponseData  Retourne la promesse résolu
 */
export function fetchManager(url, data = null) {

    // Objet littéral des paramètres
    const options= {
        method:'POST',
        header: {
                Accept: 'application/json',
                'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }
    // Appel et récupère le retour de fetch
    let reponseData = fetch(url, options);
    
    return reponseData;
}
