(function($){
    SABAI.File = SABAI.File || {};
    SABAI.File.fineUploader = function (options) {
        var options = $.extend({
            tableSelector: "",
            maxNumFiles: 0,
            maxNumFileExceededError: "",
            uploaderSelector: "",
            inputName: "files",
            fineUploaderOptions: {},
            formSelector: "",
            sortable: true
        }, options),
            table = $(options.tableSelector),
            uploader = $(options.uploaderSelector),
            numFilesUploaded = 0;

        options.fineUploaderOptions.classes = {
            // used to get elements from templates
            button: "sabai-file-qq-upload-button",
            drop: "sabai-file-qq-upload-drop-area",
            dropActive: "sabai-file-qq-upload-drop-area-active",
            dropDisabled: "sabai-file-qq-upload-drop-area-disabled",
            list: "sabai-file-qq-upload-list",
            progressBar: "sabai-file-qq-progress-bar",
            file: "sabai-file-qq-upload-file",
            spinner: "sabai-file-qq-upload-spinner",
            finished: "sabai-file-qq-upload-finished",
            retrying: "sabai-file-qq-upload-retrying",
            retryable: "sabai-file-qq-upload-retryable",
            size: "sabai-file-qq-upload-size",
            cancel: "sabai-file-qq-upload-cancel",
            retry: "sabai-file-qq-upload-retry",
            statusText: "sabai-file-qq-upload-status-text",
            // added to list item <li> when upload completes
            // used in css to hide progress spinner
            success: "sabai-success",
            fail: "sabai-error"
        };
        options.fineUploaderOptions.template = "<div class=\"sabai-file-qq-uploader\">"
            + "<div class=\"sabai-file-qq-upload-drop-area\"><span>{dragZoneText}</span></div>"
            + "<a href=\"#\" class=\"sabai-file-qq-upload-button\">{uploadButtonText}</a>"
            + "<ul class=\"sabai-file-qq-upload-list\"></ul>"
            + "</div>",
            // template for one item in file list
        options.fineUploaderOptions.fileTemplate = "<li>"
            + "<div class=\"sabai-file-qq-progress-bar\"></div>"
            + "<span class=\"sabai-file-qq-upload-spinner\"></span>"
            + "<span class=\"sabai-file-qq-upload-finished\"></span>"
            + "<span class=\"sabai-file-qq-upload-file\"></span>"
            + "<span class=\"sabai-file-qq-upload-size\"></span>"
            + "<a class=\"sabai-file-qq-upload-cancel\" href=\"#\">{cancelButtonText}</a>"
            + "<a class=\"sabai-file-qq-upload-retry\" href=\"#\">{retryButtonText}</a>"
            + "<span class=\"sabai-file-qq-upload-status-text\">{statusText}</span>"
            + "</li>",
        uploader.fineUploader(options.fineUploaderOptions)
            .on("submit", function(event, id, fileName){
                numFilesUploaded++;
                if (options.maxNumFiles && numFilesUploaded > options.maxNumFiles) {
                    uploader.find(".sabai-file-qq-upload-button, .sabai-file-qq-upload-drop-area").hide();
                    alert(options.maxNumFileExceededError);
                    return false;
                }
            })
            .on("complete", function(event, id, fileName, response){
                if (!response.success) {
                    numFilesUploaded--;
                    return;
                }
                $.each(response["files"], function(i, file){
                    var new_row = "<tr class=\'sabai-file-row\'><td class=\'sabai-form-check\'><input name=\'"
                            + options.inputName + "[current][" + file["id"] + "][check][]\' type=\'checkbox\' value=\'"+ file["id"]
                            + "\' checked=\'checked\'></td>";
                    if (typeof file["thumbnail"] != "undefined") {
                        new_row += "<td><img src=\'" + file["thumbnail"] + "\' alt=\'\' /></td><td><input name=\'"
                            + options.inputName + "[current][" + file["id"] + "][name]\' type=\'text\' value=\'" + file["title"] + "\' /></td>";
                    } else {
                        new_row += "<td><span class=\'sabai-file sabai-file-file sabai-file-type-" + file["extension"] + "\'></span> <input name=\'"
                            + options.inputName + "[current][" + file["id"] + "][name]\' type=\'text\' value=\'" + file["title"] + "\' /></td>";
                    }
                    new_row += "<td>" + file["size_hr"] + "</td></tr>";
                    
                    if (!table.has(".sabai-file-row").length) {
                        table.find("tbody").html(new_row).effect("highlight", {}, 2000);
                    } else {
                        $(new_row).appendTo(table.find("tbody")).effect("highlight", {}, 2000);
                    }
                });
                SABAI.init(table.find("tbody").sortable("destroy").sortable({containment:"parent", axis:"y"}).parent()); // reset table
            })
            .on("error", function(event, id, fileName, reason) {
                numFilesUploaded--;
            });
        $(options.formSelector).submit(function(){
            if (options.maxNumFiles && table.find("tbody input[type='checkbox']:checked").length > options.maxNumFiles) {
                alert(options.maxNumFileExceededError);
                return false;
            }
        });
        if (options.sortable) {
            table.find("tbody").sortable({containment:"parent", axis:"y"});
        }
    };
})(jQuery);