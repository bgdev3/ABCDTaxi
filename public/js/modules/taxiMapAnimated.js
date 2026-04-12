
export function taxiMapAnimated() {

    
    const img = document.getElementById('taxiMap');

     let screenWidth = window.innerWidth;
    console.log(screenWidth);

    if (screenWidth < 1024) {

       img.addEventListener('touchmove', (e) =>{
            const touch = e.touches[0];
            const rect = img.getBoundingClientRect();
            const x = ((touch.clientX - rect.left) / rect.width) * 100;
            const y = ((touch.clientY - rect.top) / rect.height) * 100;
            img.style.transformOrigin = `${x}% ${y}%`;
            img.style.transform = 'scale(1.5)';
        });
        img.addEventListener('touchend', () =>{
            img.style.transformOrigin = 'center center';
            img.style.transform = 'scale(1)';
        });

    } else {

        img.addEventListener('mousemove', (e) =>{
            const rect = e.target.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            img.style.transformOrigin = `${x}% ${y}%`;
            img.style.transform = 'scale(1.5)';
        });
        img.addEventListener('mouseleave', () =>{
            img.style.transformOrigin = 'center center';
            img.style.transform = 'scale(1)';
        });
        
    }
}
