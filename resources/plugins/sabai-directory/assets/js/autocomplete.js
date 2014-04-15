(function($){
    SABAI = SABAI || {};
    SABAI.GoogleMaps = SABAI.GoogleMaps || {};
    SABAI.GoogleMaps.autocomplete = function (input, options) {
        var $input = $(input);
        if (!$input.length) return;
        options = options || {};
        options.types = ['geocode'];
        $input.each(function(){
            var ele = $(this).get(0);
            new google.maps.places.Autocomplete(ele, options);
            google.maps.event.addDomListener(ele, 'keydown', function(e) { 
                if (e.keyCode == 13) { 
                    e.preventDefault();
                }
            });
        });
    }
})(jQuery);