//Global Variables
var map;
var service;
var markers;
var markerCluster;
var all = true;
var entryInfoElements;
var  entryInfoObjects;
var contentTotal;
var contentString = '<div class="card">'+ 
'<div class="card-body">'+ 
  '<div class="container">'+
  '<h5 class="card-title text-center">C.A PARKO</h5>'+
    '<div class="row">'+      
        '<ul class="list-unstyled">'+
        '<li><Strong class="font-weight-bold">Cliente: </Strong> Alimentos Toli</li>'+
        '<li><Strong class="font-weight-bold">Sede: </Strong>Dapibus ac facilisis in</li>'+
        '<li><Strong class="font-weight-bold" >Sede: </Strong>Dapibus ac facilisis in</li>'+
        '<li class="text-right class="text-wrap""><button type="button" class="btn btn-info">Info</button></li>'+
        '</ul>'+
   
  '</div>'+
  '<div class="row">'+
  '<ul class="list-unstyled">'+
        '<li><Strong class="font-weight-bold">Direccion:</Strong> Alimentos Toli</li>'+
        '<li><Strong class="font-weight-bold">Estado:</Strong>Dapibus ac facilisis in</li>'+
        '<li><Strong class="font-weight-bold">Estado:</Strong></li>'+
        '</ul>'+
'</div>'+  
   
'</div>'+
'</div>'+
'</div>';
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
        var contentString2 = '<div class="card " >'+ 
        '<div class="card-body">'+ 
          '<div class="container">'+
          '<h5 class="card-title">C.A PARKO</h5>'+
            '<div class="row">'+      
                '<ul class="list-unstyled">'+
                '<li><Strong class="font-weight-bold text-break">Cliente: </Strong>'+ data.cliente_nombres +'</li>'+
                '<li><Strong class="font-weight-bold">Sede: </Strong>Dapibus ac facilisis in</li>'+
                '<li><Strong class="font-weight-bold" >Sede: </Strong>Dapibus ac facilisis in</li>'+
                '<li><button type="button" class="btn btn-info">Info</button></li>'+
                '</ul>'+
           
          '</div>'+
          '<div class="row">'+
          '<ul class="list-unstyled">'+
                '<li><Strong class="font-weight-bold">Direccion:</Strong> Alimentos Toli</li>'+
                '<li><Strong class="font-weight-bold">Estado:</Strong>Dapibus ac facilisis in</li>'+
                '<li><Strong class="font-weight-bold">Estado:</Strong></li>'+
                '</ul>'+
        '</div>'+  
           
        '</div>'+
        '</div>'+
        '</div>';
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
        center: position,
        styles:  [
          {elementType: 'geometry', stylers: [{color: '#ebe3cd'}]},
          {elementType: 'labels.text.fill', stylers: [{color: '#523735'}]},
          {elementType: 'labels.text.stroke', stylers: [{color: '#f5f1e6'}]},
          {
            featureType: 'administrative',
            elementType: 'geometry.stroke',
            stylers: [{color: '#c9b2a6'}]
          },
          {
            featureType: 'administrative.land_parcel',
            elementType: 'geometry.stroke',
            stylers: [{color: '#dcd2be'}]
          },
          {
            featureType: 'administrative.land_parcel',
            elementType: 'labels.text.fill',
            stylers: [{color: '#ae9e90'}]
          },
          {
            featureType: 'landscape.natural',
            elementType: 'geometry',
            stylers: [{color: '#dfd2ae'}]
          },
          {
            featureType: 'poi',
            elementType: 'geometry',
            stylers: [{color: '#dfd2ae'}]
          },
          {
            featureType: 'poi',
            elementType: 'labels.text.fill',
            stylers: [{color: '#93817c'}]
          },
          {
            featureType: 'poi.park',
            elementType: 'geometry.fill',
            stylers: [{color: '#a5b076'}]
          },
          {
            featureType: 'poi.park',
            elementType: 'labels.text.fill',
            stylers: [{color: '#447530'}]
          },
          {
            featureType: 'road',
            elementType: 'geometry',
            stylers: [{color: '#f5f1e6'}]
          },
          {
            featureType: 'road.arterial',
            elementType: 'geometry',
            stylers: [{color: '#fdfcf8'}]
          },
          {
            featureType: 'road.highway',
            elementType: 'geometry',
            stylers: [{color: '#f8c967'}]
          },
          {
            featureType: 'road.highway',
            elementType: 'geometry.stroke',
            stylers: [{color: '#e9bc62'}]
          },
          {
            featureType: 'road.highway.controlled_access',
            elementType: 'geometry',
            stylers: [{color: '#e98d58'}]
          },
          {
            featureType: 'road.highway.controlled_access',
            elementType: 'geometry.stroke',
            stylers: [{color: '#db8555'}]
          },
          {
            featureType: 'road.local',
            elementType: 'labels.text.fill',
            stylers: [{color: '#806b63'}]
          },
          {
            featureType: 'transit.line',
            elementType: 'geometry',
            stylers: [{color: '#dfd2ae'}]
          },
          {
            featureType: 'transit.line',
            elementType: 'labels.text.fill',
            stylers: [{color: '#8f7d77'}]
          },
          {
            featureType: 'transit.line',
            elementType: 'labels.text.stroke',
            stylers: [{color: '#ebe3cd'}]
          },
          {
            featureType: 'transit.station',
            elementType: 'geometry',
            stylers: [{color: '#dfd2ae'}]
          },
          {
            featureType: 'water',
            elementType: 'geometry.fill',
            stylers: [{color: '#b9d3c2'}]
          },
          {
            featureType: 'water',
            elementType: 'labels.text.fill',
            stylers: [{color: '#92998d'}]
          }
        ]
        
      });
        

    // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
      
      
        markers = entryInfoObjects[0].map(function( datos) {
            var lat = parseFloat(datos.coord_e);
            var lng = parseFloat(datos.coord_n);
            
            var location = {lat: lat, lng: lng};
            console.log(location);
            // var marker = new google.maps.LatLng(lat,lng);
            var marker = new google.maps.Marker({
                position: location,
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

  
    
    
  
   
  