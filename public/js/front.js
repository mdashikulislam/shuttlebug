/**
 * front pages functions
 */

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// globals
//let rowselected = 0;

// temporary alerts
$('div.alert-temp').delay(5000).slideUp(300);

/**
 * trip sheet route map
 */
function routemap(mapdata) {
    let directionsService = new google.maps.DirectionsService();
    let directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
    let bounds = new google.maps.LatLngBounds();
    let mapOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false,
        streetViewControl: false,
        maxZoom: 16,
        zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL
        }
    };

    map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
    directionsDisplay.setMap(map);

    // initialize info windows
    let infoWindow  = new google.maps.InfoWindow(), marker, i;
    let markers     = [];

    // just create a normal marker for a single location. no directions required
    if ( mapdata.length === 1 ) {
        let waypoints   = mapdata;

        for( i = 0; i < waypoints.length; i++ ) {
            let latlon = waypoints[i]['geo'].split(',');
            let lat = parseFloat(latlon[0]);
            let lon = parseFloat(latlon[1]);
            let position = new google.maps.LatLng(lat,lon);

            bounds.extend(position);
            marker = new google.maps.Marker({
                position: position,
                map: map
            });
            markers.push(marker);
            attachInfo(marker, i);
        }
        map.fitBounds(bounds);

    // use directions service to plot route
    } else {
        let start   = mapdata[0]['geo'];
        let end     = mapdata[mapdata.length - 1]['geo'];

        let waypts  = [];
        let wps = mapdata.slice(1, -1);

        for (let i = 0; i < wps.length; i++) {
            waypts.push({
                location: wps[i]['geo'],
                stopover: true
            });
        }

        let request = {
            origin: start,
            destination: end,
            waypoints: waypts,
            optimizeWaypoints: false,
            travelMode: google.maps.TravelMode.DRIVING
        };

        directionsService.route(request, function(response, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                let route = response.routes[0];

                for (var i = 0; i < route.legs.length; i++) {
                    let icon = assets + '/map/n' + (i) + '.png';
                    if ( i === 0 ) {
                        icon = assets + '/map/green0.png';
                    }
                    let marker = new google.maps.Marker({
                        position: route.legs[i].start_location,
                        map: map,
                        icon: icon
                    });
                    attachInfo(marker, i);
                    markers.push(marker);
                }

                //end position
                let marker = new google.maps.Marker({
                    position: route.legs[i - 1].end_location,
                    map: map,
                    icon: assets + '/map/red' + (i) + '.png'
                });
                attachInfo(marker, i);
            }
        });
    }

    function attachInfo(marker, i) {
        google.maps.event.addListener(marker, 'click', function() {
            let infodata = infoData(i);
            infoWindow.setContent(infodata);
            infoWindow.open(map, marker);
        });
    }

    function infoData(i) {
        let data = mapdata[i];
        let infodata = '<div class="text-center" style="width:200px;">';
        let type = i === 0 ? 'Pickup' : 'Dropoff';
        infodata += '<h3 style="font-size:1.6em; color:#2f568a; margin: 0 auto .2em auto;">'+type+'</h3>';
        infodata += '<div style="border:2px solid #c3c3c3;font-size:1.2em; font-style:italic; font-weight:bold;"><br>';
        let names = data['passengers'].split(',');
        for( let n = 0; n < names.length; n++ ) {
            infodata += names[n].trim()+'<br>';
        }
        infodata += '<br></div><br>at<span style="font-size:1.2em; font-weight:bold;"> '+data['venue']+'</span><br></div>';
        // if ( i !== 0 ) {
        //     infodata += '<br><br><div style="text-align:center;"><span style="font-size:1em; font-weight:bold;">' +
        //         '<a class="capgeo ui-btn ui-btn-inline ui-mini ui-btn-icon-right ui-icon-location" id="capgeo'+i+'" data-loc="'+names[0]+'">' +
        //         'Correct this Location</a></span><br><br>(see info panel for instructions)</div></div>';
        // }
        return infodata;
    }
}

// dataTable defaults
// $.extend( $.fn.dataTable.defaults, {
//     searching:  true,
//     ordering:   true,
//     info:       false,
//     paging:     false,
//     scrollY:    560,
//     scrollX:    true,
//     select:     'single',
//     bSortClasses: false
// });

// dataTable search
// $('#tablesearch').keyup(function () {
//     table.search( this.value ).draw();
// });

// datatable row selected
// function handleRowSelect(id) {
//     rowselected = id;
//     $('.call-edit').prop('disabled', false);
// }

// datatable row deselected
// function handleRowDeselect() {
//     rowselected = 0;
//     $('.call-edit').prop('disabled', true);
// }

// call forms using rowselect
// $(document).on('click', '.call-edit', function() {
//     route = $(this).data('route') + '/' + rowselected;
//     window.location.href = route;
// });

// datatable apply filter
// $('.filter').on('change', function () {
//     let index   = $(this).data('index');
//     let filter  = $(this).prop('name');
//     let value   = $(this).val() > '' ? $(this).val() : 'unset';
//     let icon    = 'i' + $(this).prop('name');
//
//     if ( $(this).val() === '' ) {
//         $($(this)).css('color', 'black');
//         $('.'+icon).removeClass('text-danger');
//     } else {
//         $($(this)).css('color', 'red');
//         $('.'+icon).addClass('text-danger');
//     }
//
//     $.ajax({
//         type: "GET",
//         url: '/office/setfilter/' + index + '/' + filter + '/' + value
//     });
// });

// generate password
function generatePassword(len) {
    let pwd = [], cc = String.fromCharCode, R = Math.random, rnd, i;
    pwd.push(cc(48+(0|R()*10)));    // push a number
    pwd.push(cc(65+(0|R()*26)));    // push an upper case letter
    pwd.push(cc(35+(0|R()*3)));     // push a symbol
    pwd.push(cc(35+(0|R()*3)));     // push another symbol
    // pwd.push(cc(35+(0|R()*4)));     // push a symbol

    for(i=4; i<len; i++){
        rnd = 0|R()*62; // generate upper OR lower OR number
        pwd.push(cc(48+rnd+(rnd>9?7:0)+(rnd>35?6:0)));
    }

    // shuffle letters in password
    return pwd.sort(function(){ return R() - .5; }).join('');
}

// load location map modal
// $(document).on('click', '#callLocationMap', function () {
//
//     let field = 'geo';
//     let unit = $('#unit').val();
//     let street = $('#street').val();
//     let suburb = $('#suburb').val();
//     let city = $('#city').val();
//
//     let address = street + ',' + suburb + ',' + city;
//     let latlon = $('#geo').val();
//
//     $('#locationMapModal').modal('show');
//     $('#locationHeader').html(unit + ',' + street + ',' + suburb + ',' + city);
//
//     // get map based on latlon if given
//     if ( latlon > '' ) {
//         latlonMap(latlon, field);
//     }
//     // else, geocode the address then load latlon map
//     else {
//         addressCoords(address, field);
//     }
// });

// location geo locator map - finds given latlon
// function latlonMap(latlon, field) {
//
//     let b=latlon.split(",");
//     // put the given latlon into the form
//     document.getElementById(field).value = parseFloat(b[0]).toFixed(6) + ',' + parseFloat(b[1]).toFixed(6);
//
//     let Latlng = new google.maps.LatLng(parseFloat(b[0]),parseFloat(b[1]));
//
//     let mapOptions = {
//         zoom:               16,
//         center:             Latlng,
//         mapTypeControl:     true,
//         streetViewControl:  false,
//         zoomControlOptions: {
//             style: google.maps.ZoomControlStyle.SMALL
//         }
//     };
//
//     let map = new google.maps.Map(document.getElementById('locationMap'), mapOptions);
//
//     let marker = new google.maps.Marker({
//         position:   Latlng,
//         draggable:  true,
//         map:        map
//     });
//
//     google.maps.event.addListener(marker, 'dragend', function (event) {
//         // replace the form input with the moved latlon
//         document.getElementById(field).value = event.latLng.lat().toFixed(6)+','+event.latLng.lng().toFixed(6);
//     });
//
//     $('#locationMapModal').on('shown.bs.modal', function () {
//         google.maps.event.trigger(map, "resize");
//         map.setCenter(Latlng);
//     });
// }

// geocode address
// function addressCoords(address, field) {
//
//     let geocoder = new google.maps.Geocoder();
//
//     geocoder.geocode({'address': address + ',South Africa'}, function (results, status) {
//         if (status === 'OK') {
//             let coords = results[0].geometry.location.toString();
//             coords = coords.replace(/\(|\)/g, "").replace(/ /g, "");
//             latlonMap(coords, field);
//         } else {
//             alert('Geocode was not successful for the following reason: ' + status + "\n Could not find: \n" + address + "\n Check the Street, Suburb & City are correct in the address.");
//         }
//     });
// }

// suburbs map
// function suburbMap(latlon) {
//
//     let b=latlon.split(",");
//     let Latlng = new google.maps.LatLng(parseFloat(b[0]),parseFloat(b[1]));
//
//     let mapOptions = {
//         zoom:               13,
//         center:             Latlng,
//         mapTypeControl:     true,
//         streetViewControl:  false,
//         zoomControlOptions: {
//             style: google.maps.ZoomControlStyle.SMALL
//         }
//     };
//
//     let map = new google.maps.Map(document.getElementById('suburbMap'), mapOptions);
//
//     // Add a 5km diameter circle to the map, centered on the school
//     var schoolCircle = new google.maps.Circle({
//         strokeColor: '#2694ff',
//         strokeOpacity: 0.8,
//         strokeWeight: 1,
//         fillColor: '#2694ff',
//         fillOpacity: 0.25,
//         map: map,
//         center: Latlng,
//         radius: 2.5 * 1000
//     });
//
//     // place marker on school
//     let marker = new google.maps.Marker({
//         position:   Latlng,
//         draggable:  true,
//         map:        map
//     });
//
//     $('#suburbMapModal').on('shown.bs.modal', function () {
//         google.maps.event.trigger(map, "resize");
//         map.setCenter(Latlng);
//     });
// }