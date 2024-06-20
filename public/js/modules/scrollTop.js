
/**
 * Permet lacréation, l'apparition du scrollTop 
 *  et le scroll au top de la page lors du click sur l'élément
 */

export function scrollToTop(){
    let arrow = document.createElement('i');
    arrow.setAttribute('class', 'fi fi-rs-angle-up backToTop');
    document.body.append(arrow);

    window.addEventListener('scroll', ()=>{
        if(window.scrollY > 50){
            arrow.style.display = 'block';
        }
        else{
            arrow.style.display = 'none';
        }
    });

    arrow.addEventListener('click', ()=>{
        window.scrollTo({
            top:0,
            left:0,
            behavior:'smooth'
        });
    });
    
    arrow.addEventListener('mouseover', ()=>{
        arrow.style.opacity = '.9';
    });
    arrow.addEventListener('mouseleave', ()=>{
        arrow.style.opacity = '.2';
    });
}

