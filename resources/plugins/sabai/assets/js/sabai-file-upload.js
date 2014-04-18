(function($){
    SABAI.File = SABAI.File || {};
    SABAI.File.upload = SABAI.File.upload || function (options) {
        var options = $.extend({
            tableSelector: "",
            maxNumFiles: 0,
            maxNumFileExceededError: "",
            uploaderSelector: "",
            paramName: "sabai_file",
            inputName: "files",
            sortable: true,
            formData: {}
        }, options),
            numFilesUploaded = 0;
        $(options.uploaderSelector).fileupload({
            url: options.url,
            dataType: 'json',
            paramName: options.paramName,
            formData: options.formData,
            singleFileUploads: true,
            //forceIframeTransport: true,
            submit: function (event, data) {
                if (options.maxNumFiles && numFilesUploaded + data.files.length > options.maxNumFiles) {
                    if (options.maxNumFileExceededError) alert(options.maxNumFileExceededError);
                    return false;
                }
                numFilesUploaded += data.files.length;
            },
            done: function (e, data) {
                var table = table = $(options.tableSelector);
                $.each(data.result.files, function (index, file) {           
                    var new_row = "<tr class=\'sabai-file-row\'><td class=\'sabai-form-check\'><input name=\'"
                        + options.inputName + "[current][" + file.id + "][check][]\' type=\'checkbox\' value=\'"+ file.id
                        + "\' checked=\'checked\'></td>";
                    if (typeof file.thumbnail != "undefined") {
                        new_row += "<td><img src=\'" + file.thumbnail + "\' alt=\'\' /></td><td><input name=\'"
                            + options.inputName + "[current][" + file.id + "][name]\' type=\'text\' value=\'" + file.title + "\' /></td>";
                    } else {
                        new_row += "<td><span class=\'sabai-file sabai-file-file sabai-file-type-" + file.extension + "\'></span> <input name=\'"
                            + options.inputName + "[current][" + file.id + "][name]\' type=\'text\' value=\'" + file.title + "\' /></td>";
                    }
                    new_row += "<td>" + file.size_hr + "</td></tr>";
                    
                    if (!table.has(".sabai-file-row").length) {
                        table.find("tbody").html(new_row).effect("highlight", {}, 2000);
                    } else {
                        $(new_row).appendTo(table.find("tbody")).effect("highlight", {}, 2000);
                    }
                });
                if (options.sortable) {
                    SABAI.init(table.find("tbody").sortable("destroy").sortable({containment:"parent", axis:"y"}).parent()); // reset table
                }
            }
        });
        $(options.formSelector).submit(function () {
            if (options.maxNumFiles && $(options.tableSelector).find("tbody input[type='checkbox']:checked").length > options.maxNumFiles) {
                if (options.maxNumFileExceededError) alert(options.maxNumFileExceededError);
                return false;
            }
        });
        if (options.sortable) {
            $(options.tableSelector).find("tbody").sortable({containment:"parent", axis:"y"});
        }
    }
})(jQuery);