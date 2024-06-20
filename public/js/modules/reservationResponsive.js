
/**
 * Récupère les éléments de la classe table-responsive et récupère tous les th.
 * Sur chacun d'eux on récupère les valeur des th que l'on stocke dans un tableau
 * 
 * Sur chaque td.data des pseudo-elements créer en css on leur applique un attribut datalabel en leur assignant les valeurs
 */
export function responsiveArray() {

    document.querySelectorAll('.table-responsive').forEach(function(table){
        let labels = Array.from(table.querySelectorAll('th')).map(function(th){
            return th.innerText;
        })
      table.querySelectorAll('td.data').forEach(function(td, i){
        td.setAttribute('data-label', labels[i % labels.length]);
      })
  })
}