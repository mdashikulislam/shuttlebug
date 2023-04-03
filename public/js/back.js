/**
 * office pages functions
 */

// globals
let rowselected = 0;

// ajax loader
// $(document).ajaxStart(function() {
//     $('#ajaxloader').show();
// });
// $(document).ajaxComplete(function() {
//     $('#ajaxloader').hide();
// });

// temporary alerts
$('div.alert-temp').delay(8000).slideUp(300);

// dataTable defaults
$.extend( $.fn.dataTable.defaults, {
    searching:  true,
    ordering:   true,
    info:       false,
    paging:     false,
    scrollY:    560,
    scrollX:    true,
    select:     'single',
    bSortClasses: false
});

// dataTable search
$('#tablesearch').keyup(function () {
    table.search( this.value ).draw();
});

// datatable row selected
function handleRowSelect(id) {
    rowselected = id;
    $('.call-edit').prop('disabled', false);
}

// datatable row deselected
function handleRowDeselect() {
    rowselected = 0;
    $('.call-edit').prop('disabled', true);
}

// call forms using rowselect
$(document).on('click', '.call-edit', function() {
    route = $(this).data('route') + '/' + rowselected;
    window.location.href = route;
});

// datatable apply filter
$('.filter').on('change', function () {
    let index   = $(this).data('index');
    let filter  = $(this).prop('name');
    let value   = $(this).val() > '' ? $(this).val() : 'unset';
    let icon    = 'i' + $(this).prop('name');

    if ( $(this).val() === '' ) {
        $(this).css('color', 'black');
        $('.'+icon).removeClass('text-danger');
    } else {
        $(this).css('color', 'red');
        $('.'+icon).addClass('text-danger');
    }

    $.ajax({
        type: "GET",
        url: '/office/setfilter/' + index + '/' + filter + '/' + value
    });
});

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
$(document).on('click', '#callLocationMap', function () {

    let field = 'geo';
    let unit = $('#unit').val();
    let street = $('#street').val();
    let suburb = $('#suburb').val();
    let city = $('#city').val();

    let address = street + ',' + suburb + ',' + city;
    let latlon = $('#geo').val();

    if ( typeof city === "undefined" && latlon === '' ) {
        latlon = '-34.041899,18.352533';
    }

    $('#locationMapModal').modal('show');
    $('#locationHeader').html(street + ',' + suburb + ',' + city);

    // get map based on latlon if given
    if ( latlon > '' ) {
        latlonMap(latlon, field);
    }
    // else, geocode the address then load latlon map
    else {
        addressCoords(address, field);
    }
});

// location geo locator map - finds given latlon
function latlonMap(latlon, field) {

    let b=latlon.split(",");
    // put the given latlon into the form
    document.getElementById(field).value = parseFloat(b[0]).toFixed(6) + ',' + parseFloat(b[1]).toFixed(6);

    let Latlng = new google.maps.LatLng(parseFloat(b[0]),parseFloat(b[1]));

    let mapOptions = {
        zoom:               16,
        center:             Latlng,
        mapTypeControl:     true,
        streetViewControl:  false,
        zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL
        }
    };

    let map = new google.maps.Map(document.getElementById('locationMap'), mapOptions);

    let marker = new google.maps.Marker({
        position:   Latlng,
        draggable:  true,
        map:        map
    });

    google.maps.event.addListener(marker, 'dragend', function (event) {
        // replace the form input with the moved latlon
        document.getElementById(field).value = event.latLng.lat().toFixed(6)+','+event.latLng.lng().toFixed(6);
    });

    $('#locationMapModal').on('shown.bs.modal', function () {
        google.maps.event.trigger(map, "resize");
        map.setCenter(Latlng);
    });
}

// geocode address
function addressCoords(address, field) {

    let geocoder = new google.maps.Geocoder();

    geocoder.geocode({'address': address + ',South Africa'}, function (results, status) {
        if (status === 'OK') {
            let coords = results[0].geometry.location.toString();
            coords = coords.replace(/\(|\)/g, "").replace(/ /g, "");
            latlonMap(coords, field);
        } else {
            alert('Geocode was not successful for the following reason: ' + status + "\n Could not find: \n" + address + "\n Check the Street, Suburb & City are correct in the address.");
        }
    });
}

// suburbs map
function suburbMap(latlon) {

    let b=latlon.split(",");
    let Latlng = new google.maps.LatLng(parseFloat(b[0]),parseFloat(b[1]));

    let mapOptions = {
        zoom:               13,
        center:             Latlng,
        mapTypeControl:     true,
        streetViewControl:  false,
        zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL
        }
    };

    let map = new google.maps.Map(document.getElementById('suburbMap'), mapOptions);

    // Add a 5km diameter circle to the map, centered on the school
    var schoolCircle = new google.maps.Circle({
        strokeColor: '#2694ff',
        strokeOpacity: 0.8,
        strokeWeight: 1,
        fillColor: '#2694ff',
        fillOpacity: 0.25,
        map: map,
        center: Latlng,
        radius: 2.5 * 1000
    });

    // place marker on school
    let marker = new google.maps.Marker({
        position:   Latlng,
        draggable:  true,
        map:        map
    });

    $('#suburbMapModal').on('shown.bs.modal', function () {
        google.maps.event.trigger(map, "resize");
        map.setCenter(Latlng);
    });
}