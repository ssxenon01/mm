(function($){
    SABAI = SABAI || {};
    SABAI.GoogleMaps = SABAI.GoogleMaps || {};
    SABAI.GoogleMaps.directionMap = SABAI.GoogleMaps.directionMap || function (mapId, lat, lng, trigger, input, mode, content, panelId, options) {
        var gmap, destination, destinationMarker, directionsDisplay, directionsService, infoWindow;
        
        if (!lat || !lng) return;

        // Instantiate a directions service.
        directionsService = new google.maps.DirectionsService();

        destination = new google.maps.LatLng(lat, lng);

        // Create a map
        gmap = new google.maps.Map($(mapId).get(0), {
            zoom: options.zoom || 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: destination,
            scrollwheel: options.scrollwheel || false,
            styles: options.styles || null
        });
        
        destinationMarker = new google.maps.Marker({
            position: destination, 
            map: gmap,
            animation: google.maps.Animation.DROP,
            icon: options.icon || null
        });

        // Create a renderer for directions and bind it to the map.
        directionsDisplay = new google.maps.DirectionsRenderer({
            map: gmap,
            draggable: true,
            panel: $(panelId).hide().get(0),
            suppressMarkers: true
        })

        // Instantiate an info window to hold step text.
        infoWindow = new google.maps.InfoWindow({maxWidth: 300});

        if (content) {
            infoWindow.setContent(content);
            // Display destination details in info window
            google.maps.event.addListener(destinationMarker, 'click', function() {
                infoWindow.open(gmap, destinationMarker);
            });
            setTimeout(function() {
                google.maps.event.trigger(destinationMarker, 'click');
            }, 1500);
        }
        
        if ($(trigger).length && $(input).length) {
            $(trigger).click(function(){
                infoWindow.close();

                // Retrieve the start and end locations and create a DirectionsRequest
                var request = {
                    origin: $(input).val(),
                    destination: destination,
                    travelMode: google.maps.TravelMode[$(mode).val()] || google.maps.TravelMode.WALKING
                };

                // Route the directions and pass the response to a
                // function to create markers for each step.
                directionsService.route(request, function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        //var warnings = document.getElementById("warnings_panel");
                        //warnings.innerHTML = "" + response.routes[0].warnings + "";
                        directionsDisplay.setDirections(response);
                        SABAI.scrollTo(mapId);
                        $(panelId).show().find('img.adp-marker').hide();
                    }
                });
            });
        }
    }
})(jQuery);