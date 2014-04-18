(function($){
    $.datepicker.setDefaults({
        altFormat: "yy/mm/dd",
        autoSize: true,
        changeYear: true,
        changeMonth: true,
        showButtonPanel: false
    });
    
    SABAI.Date = SABAI.Date || {};
    SABAI.Date.datetimepicker = SABAI.Date.datetimepicker || function (options) {
        var o = $.extend({
            target: null,
            minDate: null,
            maxDate: null,
            numberOfMonths: 1,
            timeTarget: null
        }, options),
            $date_ele,
            $date_ele_text,
            $time_ele;
        
        if (!o.target) return;
        
        $date_ele = $(o.target);
        $date_ele_text = $(o.target + "-text"); 
        if (!$date_ele.length || !$date_ele_text.length) return;
        
        $($date_ele_text).datepicker({
            altField: o.target,
            minDate: o.minDate,
            maxDate: o.maxDate,
            numberOfMonths: o.numberOfMonths
        });
        if ($date_ele.attr("value")) {
            $date_ele_text.attr("value", $.datepicker.formatDate($date_ele_text.datepicker("option", "dateFormat"), new Date($date_ele.attr("value") + " 00:00:00")));
        }
        
        if (!o.timeTarget) return;
        
        $time_ele = $(o.timeTarget);
        if (!$time_ele.length) return;
        
        $time_ele.timepicker();
    }
})(jQuery);