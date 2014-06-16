(function($){
    SABAI = SABAI || {};
    SABAI.Directory = SABAI.Directory || {};
    SABAI.Directory.googleMap = SABAI.Directory.googleMap || function (mapId, markers, updater, center, zoom, styles, options) {
        var gmap,
            marker,
            currentMarker,
            markerPosition,
            markerCluster,
            infowindow = new google.maps.InfoWindow({size: new google.maps.Size(150,50)}),
            infowindowTriggerEvent = infowindowTriggerEvent || 'hover',
            i,
            bounds,
            updaterTimeout,
            initialZoom,
            updateTrigger,
            update;
            
        if (!center) {
            center = markers.length ? new google.maps.LatLng(markers[0].lat, markers[0].lng) : new google.maps.LatLng(40.69847, -73.95144);
            if (markers.length > 1) {
                bounds = new google.maps.LatLngBounds();
            }
        } else {
            center = new google.maps.LatLng(center[0], center[1]);
            if (options.force_fit_bounds && markers.length > 0) {
                bounds = new google.maps.LatLngBounds();
            }
        }

        gmap = new google.maps.Map($(mapId).get(0), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false,
            panControl: false,
            zoom: zoom,
            center: center,
            scrollwheel: options.scrollwheel || false,
            styles: styles || null
        });
        initialZoom = gmap.getZoom();
        
        // Add markers
        if (options.marker_clusters) {
            markerCluster = new MarkerClusterer(gmap, [], options.marker_cluster_imgurl ? {imagePath: options.marker_cluster_imgurl + '/m'} : null);
        }
        for (i = 0; i < markers.length; i++) {
            markerPosition = new google.maps.LatLng(markers[i].lat, markers[i].lng);
            marker = new google.maps.Marker({
                position: markerPosition,
                icon: markers[i].icon || null
            });
            if (bounds) {
                bounds.extend(markerPosition);
                if (options.force_fit_bounds) {
                    // Extend bound to include the point opposite the marker so the center stays the same
                    bounds.extend(new google.maps.LatLng(center.lat() * 2 - markers[i].lat, center.lng() * 2 - markers[i].lng));
                }
            }
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function() {
                    if (currentMarker && currentMarker.get('id') === marker.get('id')) {
                        return;
                    }
                    // Pan to the position of marker if the marker is not visible, as well as set zoom level to initial value 
                    if (!gmap.getBounds().contains(marker.getPosition())) {
                        gmap.panTo(marker.getPosition());
                        gmap.setZoom(initialZoom);
                    }
                    if (currentMarker) {
                        currentMarker.setAnimation(null);
                    }
                    if (markers[i].content) {
                        infowindow.setContent(markers[i].content);
                        infowindow.open(gmap, marker); 
                    } else {
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                    }
                    currentMarker = marker;
                }
            })(marker, i));
            if (markers[i].trigger) {
                if ($(markers[i].trigger).length) {
                    $(markers[i].trigger)[infowindowTriggerEvent]((function (marker) {
                        return function () {
                            google.maps.event.trigger(marker, 'click');
                            return false;
                        };
                    })(marker));
                }
            }
            marker.set('id', i);
            if (options.marker_clusters) {
                markerCluster.addMarker(marker);
            } else {
                marker.setMap(gmap);
            }
        }
        
        updateTrigger = $(mapId + "-update");
        if (updateTrigger.length > 0) {
            update = function () {
                if (!updater || !updateTrigger.prop("checked")) return;
                updater.call(gmap, gmap.getCenter(), gmap.getBounds(), gmap.getZoom());
            };        
            // Update map when dragged or zoom changed
            google.maps.event.addListener(gmap, 'dragend', function () {
                updaterTimeout = setTimeout(update, 1000);
            });
            google.maps.event.addListener(gmap, 'mousedown', function () {
                if (updaterTimeout) clearTimeout(updaterTimeout);
            });
            if ($.cookie) {
                updateTrigger.prop("checked", $.cookie("sabai_directory_map_update")).click(function () {
                    if ($(this).prop("checked")) {
                        $.cookie("sabai_directory_map_update", true, {expires: 7});
                    } else {
                        $.removeCookie("sabai_directory_map_update");
                    }
                });
            }
        }
        
        // Clear current marker on closing infowindow
        google.maps.event.addListener(infowindow,'closeclick',function () {
            currentMarker = null;
        });

        if (bounds) {
            gmap.fitBounds(bounds);
        }
        
        return gmap;
    }
})(jQuery);
