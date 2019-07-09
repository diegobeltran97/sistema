//Global Variables
var map;
var service;
var markers;
var markerCluster;
var all = true;
var entryInfoElements;
var  entryInfoObjects;
var contentTotal;
// Initialize and add the map

function content() {
     entryInfoElements =
        document.querySelectorAll('[data-entry-info]');

    // Map over each element and extract the data value
      entryInfoObjects =
        Array.from(entryInfoElements).map(
            item => JSON.parse(item.dataset.entryInfo)
        );
   
    // You'll now have an array of objects to work with
   

     contentTotal = entryInfoObjects[0].map( data => { 
        var contentString2 = '<div class="card">'+ 
        '<div class="card-body">'+ 
          '<div class="container" style=" max-width:240px; " >'+
          '<h5 class="card-title">C.A PARKO</h5>'+
            '<div class="row">'+      
                '<ul class="list-unstyled">'+
                '<li><Strong class="font-weight-bold">Cliente: </Strong> ' + data.cliente+ '</li>'+
                '<li><Strong class="font-weight-bold">Sede: </Strong>'+ data.nombre_sede + '</li>'+
                '<li><Strong class="font-weight-bold" >Nombre de Pozo </Strong>' + data.nombre+ '</li>'+
      
                '<a class="btn btn-primary  btn-details" href="./wells.php?action=details&id='+data.pozo_id+'" target="_blank" >Details</a>'+
                '</ul>'+
           
          '</div>'+
          '<div class="row">'+
          '<ul class="list-unstyled">'+
                '<li><Strong class="font-weight-bold">Direccion: </Strong>' + data.direccion + '</li>'+
                '<li><Strong class="font-weight-bold">Estado: </Strong> ' +  data.estado+ '</li>'+
                '<li><Strong class="font-weight-bold">Ciudad: </Strong> ' + data.nombre_ciudad + ' </li>'+
                '<li><Strong class="font-weight-bold">Cordenadas:</Strong></li>'+
                '<li><Strong class="font-weight-bold">Fecha Construccion: </Strong>' + data.fecha_construccion+ '</li>'+
                '</ul>'+
        '</div>'+  
           
        '</div>'+
        '</div>'+
      '</div>' ; 
        return  contentString2; 
    });
  
}

function initMap(location, typeZoom) {
    content();
    // The location of Map
    var position;
    var zoom2;
    var venezuela = { lat: 6.4238, lng: -66.5897 };
    if ( location != null) {
        position = location;
    } else {
      position = venezuela;
    }
  
    if ( typeZoom != null) {
      zoom2 = typeZoom;
  } else {
    zoom2 = 5;
  }

     // Create the map , with the propierties
     map = new google.maps.Map(document.getElementById("map"), {
        zoom: zoom2,
        center: position
        
      });
        

    // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
      
      var iconBase = "https://unpkg.com/leaflet@1.3.3/dist/images/marker-icon.png";
        markers = entryInfoObjects[0].map(function( datos) {
            var lng = parseFloat(datos.coord_e);
            var lat = parseFloat(datos.coord_n);
            
            var location = { lat: lat, lng: lng };
            console.log(location);
            // var marker = new google.maps.LatLng(lat,lng);
            var marker = new google.maps.Marker({
                position: location,
                icon: iconBase,
                map: map
              });
    

    
           return marker;
        
         });
      
      
  
        // var infowindow = new google.maps.InfoWindow( { 
        //     content: contentString 
        //  });
  
    markers.map(  (marker, i) => {
        contentTotal[i] = new google.maps.InfoWindow( { 
               content: contentTotal[i],
               maxWidth: 250,
               
             });
        marker.addListener('click', function() { contentTotal[i].open(map, marker) });
    });
  
        
      
}

  
    
    
  
   
  