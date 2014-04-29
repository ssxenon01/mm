(function($){
    SABAI.Markdown = SABAI.Markdown || {};
    SABAI.Markdown.editor = SABAI.Markdown.editor || function(id, langs, helpSettings) {        
        var converter = Markdown.getSanitizingConverter(),
            helpButton = !helpSettings.url ? null : {
                handler: function(){
                    window.open(helpSettings.url, "markdown-help", "width=" + helpSettings.width + ",height=" + helpSettings.height + ",scrollbars=yes,resizable=yes,location=no,toolbar=no,menubar=no,status=no");
                }
            },
            editor = new Markdown.Editor(converter, "-" + id, {strings: langs, helpButton: helpButton});          
        editor.run();
        $("#wmd-input-" + id).load(function() {
            var $preview = $("#wmd-preview-" + id), textarea_width = $(this).outerWidth();
            $preview.css("maxWidth", textarea_width
                - parseInt($preview.css("padding-left"), 10)
                - parseInt($preview.css("padding-right"), 10)
                - parseInt($preview.css("borderLeftWidth"), 10)
                - parseInt($preview.css("borderRightWidth"), 10)
            );
            $("#wmd-button-bar-" + id).css("maxWidth", textarea_width);
        });
    };
})(jQuery);