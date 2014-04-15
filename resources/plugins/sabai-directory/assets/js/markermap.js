if (!google.maps.Polygon.prototype.getBounds) {
    google.maps.Polygon.prototype.getBounds = function() {

    var bounds = new google.maps.LatLngBounds(),
        paths = this.getPaths(),
        path,
        pathsCount = paths.getLength(),
        pathCount,
        p,
        i;
                
        for (p = 0; p < pathsCount; p++) {
            path = paths.getAt(p);
            pathCount = path.getLength();
            for (i = 0; i < pathCount; i++) {
                bounds.extend(path.getAt(i));
            }
        }

        return bounds;
    }
}

(function($){
    SABAI = SABAI || {};
    SABAI.GoogleMaps = SABAI.GoogleMaps || {};
    SABAI.GoogleMaps.geocoder = new google.maps.Geocoder();
    SABAI.GoogleMaps.markerMap = function (mapId, clat, clng, lat, lng, zoom, icon, styles, options) {
        var map = new google.maps.Map(document.getElementById(mapId), {
            zoom: zoom,
            center: new google.maps.LatLng(clat, clng),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false,
            scrollwheel: options.scrollwheel || false,
            styles: styles || null
        }),
        overlay = new google.maps.Marker({
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            icon: icon || null
        }),
        updateValues = function (latlng) {
            $("#" + mapId + "-zoom").attr("value", map.getZoom());
            $("#" + mapId + "-lat").attr("value", latlng.lat());
            $("#" + mapId + "-lng").attr("value", latlng.lng());
            if (!$("#" + mapId + "-addr").val()) {
                $("#" + mapId + "-fetch").click();
            }
        },
		updateAll = function (latlng) {
			overlay.setPosition(latlng);
            overlay.setAnimation(google.maps.Animation.BOUNCE);
            window.setTimeout(function() {
                overlay.setAnimation(null);
                map.panTo(latlng);
                updateValues(latlng);
            }, 1000);
		};
        if (lat && lng) {
            overlay.setPosition(new google.maps.LatLng(lat, lng));
        }
        google.maps.event.addListener(map, "click", function(event) {
			updateAll(event.latLng);
        });
        google.maps.event.addListener(map, "zoom_changed", function(event) {
            // update zoom
			$("#" + mapId + "-zoom").attr("value", map.getZoom());
        });
        google.maps.event.addListener(overlay, "dragend", function(event) {
            window.setTimeout(function() {
                map.panTo(event.latLng);
                updateValues(event.latLng);
            }, 1000);
        });
        $("#" + mapId).fitMaps();
        $("#" + mapId + "-search").click(function(){
			SABAI.GoogleMaps.geocoder.geocode({'address': $.trim($("#" + mapId + "-addr").val())}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					updateAll(results[0].geometry.location);
				} else {
					alert("Geocoder failed due to: " + status);
				}
			});
			return false;
        });
        $("#" + mapId + "-fetch").click(function(){
			var latlng = overlay.getPosition();
			if (!latlng) {
				return false;
			}
			SABAI.GoogleMaps.geocoder.geocode({'latLng': latlng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						$("#" + mapId + "-addr").attr("value", results[0].formatted_address);
					}
				} else {
					alert("Geocoder failed due to: " + status);
				}
			});
            return false;
        });
    }
})(jQuery);
