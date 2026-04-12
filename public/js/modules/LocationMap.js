

export async function initMapFooter(){
  // Initialize and add the map
let footerMap;
  // The location of Uluru
  const position = { lat: 44.86327582984626, lng: 4.875231096952067 };
  

  // Request needed libraries.
  //@ts-ignore
  const { Map } = await google.maps.importLibrary("maps");
  const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

  // The map, centered at Uluru
  footerMap = new Map(
    document.getElementById('map-footer'),
    {
      zoom: 10,
      center: position,
      mapId: 'DEMO_MAP_ID',
    }
  );

  // The marker, positioned at Uluru
  const marker = new AdvancedMarkerElement({
    map: footerMap,
    position: position,
    title: 'ABCD Taxi, Drôme'
  });
}

