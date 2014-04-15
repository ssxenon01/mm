(function($){
    SABAI.FieldUI = SABAI.FieldUI || {};
    SABAI.FieldUI.adminFields = function (messages) {
        var messages = $.extend({submitConfirm:"", leaveConfirm:"", deleteFieldConfirm:""}, messages),
            form_is_submitting = false,
            form_submit_timeout,
            available_fields_offset,
            _field_create = function() {
                var $this = $(this);
                $this.attr("data-modal-title", $this.text());
                SABAI.ajax({
                    type: "get",
                    target: "#sabai-modal",
                    url: this.href + (this.href.indexOf("?", 0) === -1 ? "?" : "&") + "field_type=" + $this.data("field-type")
                        + "&field_name=" + $this.data("field-name"),
                    onError: function(error, target, trigger) {SABAI.flash(error.message, "error");},
                    onContent: function(response, target, trigger) {target.focusFirstInput();},
                    trigger: $this,
                    modalWidth: 600
                });
                return false;
            },
            _field_edit = function() {
                var $this = $(this), 
                    field = $(this).closest(".sabai-fieldui-field"),
                    field_id = field.find(".sabai-fieldui-field-id").attr("value"),
                    field_ele_id = field.attr("id"),
                    target_selector = "#sabai-fieldui-field-form" + field_id,
                    target = $(target_selector);
                if (target.find("> form").length) {
                    // previously loaded form exists
                    target.slideDown("fast");
                    SABAI.scrollTo(target);
                } else {
                    // load form
                    SABAI.ajax({
                        type: "get",
                        target: target_selector,
                        url: this.href + (this.href.indexOf("?", 0) === -1 ? "?" : "&") + "ele_id=" + field_ele_id + "&field_id=" + field_id,
                        onContent: function(response, target, trigger) {target.focusFirstInput();},
                        onError: function(error, target, trigger) {SABAI.flash(error.message, "error");},
                        trigger: $this,
                        modalWidth: 600,
                        slide: true,
                        scrollTo: target_selector
                    });
                }
                _select_field(field);
                return false;
            },
            _field_delete = function() {
                var field = $(this).closest(".sabai-fieldui-field");
                _select_field(field);
                // Confirm deletion
                if (!confirm(messages.deleteFieldConfirm)) return false;

                // Is this field already saved?
                if (field.find(".sabai-fieldui-field-id").attr("value")) {
                    // Set timeout to submit form automatically 
                    form_submit_timeout = setTimeout(function(){
                        $("#sabai-fieldui").submit();
                    }, 2000);
                }
                // Fadeout
                field.fadeTo("fast", 0, function(){
                    $(this).slideUp("medium", function(){
                        $(this).remove();
                    });
                });
                return false;
            },
            _update_field = function (field, result) {
                if (result.title) {
                    if (result.required) {
                        result.title = result.title + "<span class=\"sabai-fieldui-field-required\">*</span>";
                    }
                    field.find("> .sabai-fieldui-field-label").html(result.title).show();
                } else {
                    field.find("> .sabai-fieldui-field-label").hide();
                }
                if (result.description) {
                    field.find("> .sabai-fieldui-field-description").html(result.description).show();
                } else {
                    field.find("> .sabai-fieldui-field-description").hide();
                }
                var field_title = result.admin_title + " - " + result.name;
                field.find("> .sabai-fieldui-field-preview").html(result.preview).end()
                    .find(".sabai-fieldui-field-title").text(field_title).end()
                    .find(".sabai-fieldui-field-edit").attr("data-modal-title", field_title).end()
                    .effect("highlight", {}, 1500);
            },
            _select_field = function (field) {
                _deselect_selected_fields();
                field.addClass("sabai-fieldui-field-selected");
            },
            _deselect_selected_fields = function (force) {
                $("#sabai-fieldui").find(".sabai-fieldui-field-selected").each(function(){
                    var $this = $(this), form = $this.find(".sabai-fieldui-field-form");
                    if (force) {
                        form.slideUp();
                        $this.removeClass("sabai-fieldui-field-selected");
                    } else if (!form.length || !form.is(":visible")) {
                        $this.removeClass("sabai-fieldui-field-selected");
                    }
                });
            },
            sortable_conf = {
                axis: "y",
                items: ".sabai-fieldui-field",
                handle: ".sabai-fieldui-field-info",
                connectWith: "#sabai-fieldui-active .sabai-fieldui-fields",
                helper: "clone",
                opacity: 0.8,
                cursor: "move",
                placeholder: "sabai-fieldui-field-placeholder",
                start: function(event,ui) {
                    _deselect_selected_fields();
                    ui.placeholder.width(ui.helper.outerWidth()).height(ui.helper.outerHeight());
                    // Clear currently active timeout
                    if (form_submit_timeout) {
                        clearTimeout(form_submit_timeout);
                    }
                },
                update: function(event, ui) {
                    ui.item.addClass("sabai-fieldui-moved");
                    // Set timeout to submit form automatically 
                    form_submit_timeout = setTimeout(function(){
                        $("#sabai-fieldui").submit();
                    }, 2000);
                    
                }
            };
        $(".sabai-fieldui-available").on("click", ".sabai-fieldui-fields > a", _field_create);
        // Init field controls
        $(".sabai-fieldui-field-control").on("click", ".sabai-fieldui-field-edit", _field_edit)
            .on("click", ".sabai-fieldui-field-delete", _field_delete);
        // Make fields sortable
        $("#sabai-fieldui-active .sabai-fieldui-fields").sortable(sortable_conf);
        // Field expand/collapse
        $("a.sabai-fieldui-toggle").click(function() {
            var $this = $(this);
            var $fields = $this.closest(".sabai-fieldui-available").find(".sabai-fieldui-fields");
            if ($fields.is(":hidden")) {
                $fields.slideDown("fast");
                $this.find("i").removeClass("sabai-icon-caret-down").addClass("sabai-icon-caret-up");
            } else {
                $fields.slideUp("fast");
                $this.find("i").removeClass("sabai-icon-caret-up").addClass("sabai-icon-caret-down");
            }
            return false;
        });
        // Form submit callback
        $("#sabai-fieldui").submit(function() {
            var $form = $(this);
            _deselect_selected_fields(true);
            form_is_submitting = true;
            SABAI.ajax({
                type: $form.attr("method"),
                target: $form,
                url: $form.attr("action"),
                data: $form.serialize(),
                onSuccess: function (result, target, trigger) {
                    target.find(".sabai-fieldui-moved").removeClass("sabai-fieldui-moved");
                }
            });
            return false;
        });
        // Alert user when leaving the page if new form fields or form layout have not been saved yet
        window.onbeforeunload = function() {
            if (form_is_submitting) {
                form_is_submitting = false; // reset
                return;
            }
            if ($("#sabai-fieldui").find(".sabai-fieldui-moved").length) {
                return messages.leaveConfirm;
            }
        };
        
        available_fields_offset = $('#sabai-fieldui-available-wrap').offset();
        $(window).scroll(function () {
            if($(window).scrollTop() > available_fields_offset.top - 40) {
                $('#sabai-fieldui-available-wrap').css({position:"fixed", top:"40px", left:available_fields_offset.left + "px"});
            } else {
                $('#sabai-fieldui-available-wrap').css("position", "static");
            }
        });
        $(window).resize(function () {
            $('#sabai-fieldui-available-wrap').css("position", "static");
            available_fields_offset.left = $('#sabai-fieldui-available-wrap').offset().left;
            if($(window).scrollTop() > available_fields_offset.top - 40) {
                $('#sabai-fieldui-available-wrap').css({position:"fixed", top:"40px", left:available_fields_offset.left + "px"});
            }
        });
        
        $(SABAI).bind('sabai.fieldui.field.created', function(e, data){
            data.target.hide();
            var container = $("#sabai-fieldui-active").find(".sabai-fieldui-fields").first();
            if (!container.length) return;
            var field = $("#sabai-fieldui-field")
                .clone(true)
                .attr("id", "sabai-fieldui-field" + data.result.id)
                .appendTo(container)
                .find(".sabai-fieldui-field-id").attr("value", data.result.id).end()
                .find(".sabai-fieldui-field-form").attr("id", "sabai-fieldui-field-form" + data.result.id).end()
                .find(".sabai-fieldui-field-help").remove().end()
                .addClass(data.result.field_system ? "sabai-fieldui-core-field" : "sabai-fieldui-custom-field")
                .addClass("sabai-fieldui-field-type-" + data.result.type_normalized)
                .show();
            SABAI.scrollTo(field);
            _update_field(field, data.result);
            $("#sabai-fieldui").submit();
            _select_field(field);
            // Remove button from existing fields
            var existing_field_btn = $("#sabai-fieldui-existing-fields").find("a[data-field-name='" + data.result.name + "']");
            if (existing_field_btn.length) {
                existing_field_btn.fadeOut(300, function() {$(this).remove();});
            }
        });
        
        $(SABAI).bind('sabai.fieldui.field.updated', function(e, data){
            data.target.slideUp().find("> form").remove();
            var field = $("#" + data.result.ele_id);
            SABAI.scrollTo(field);
            _update_field(field, data.result);
        });
        
        $(SABAI).bind('sabai.fieldui.field.cancelled', function(e, data){
            data.target.slideUp();
            var field = data.target.closest(".sabai-fieldui-field");
            SABAI.scrollTo(field);
        });
    };    
})(jQuery);