

export async function initMapFooter(){
  // Initialize and add the map
let footerMap;
  // The location of Uluru
  const position = { lat: -25.344, lng: 131.031 };

  // Request needed libraries.
  //@ts-ignore
  const { Map } = await google.maps.importLibrary("maps");
  const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

  // The map, centered at Uluru
  footerMap = new Map(
    document.getElementById('map-footer'),
    {
      zoom: 4,
      center: position,
      mapId: 'roadmap',
    }
  );

  // The marker, positioned at Uluru
  const marker = new AdvancedMarkerElement({
    map: footerMap,
    position: position,
    title: 'Uluru'
  });
}

