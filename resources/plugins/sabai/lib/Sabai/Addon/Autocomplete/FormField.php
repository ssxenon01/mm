<?php
class Sabai_Addon_Autocomplete_FormField implements Sabai_Addon_Form_IField
{
    static private $_elements = array();
    protected $_addon, $_name;

    public function __construct(Sabai_Addon_Autocomplete $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        $ele_id = $form->getFieldId($name) . '-autocomplete';
        $data += array(
            '#multiple' => false,
            '#max_selection' => 0,
            '#noscript' => array(),
            '#min_input_length' => 2,
            '#ajax_quiet_millis' => 200,
            '#ajax_request_limit' => 10,
            '#ajax_request_param_term' => 'term',
            '#ajax_request_param_page' => Sabai::$p,
            '#ajax_response_param_items' => 'items',
            '#ajax_response_param_total' => 'total',
            '#default_items' => array(),
            '#format_selection' => 'return item.text;',
            '#format_result' => 'return item.text;',
        );
        
        // Load default items and noscript options
        if (!empty($data['#default_value']) && empty($data['#default_items']) && isset($data['#default_items_callback'])) {
            if (!isset($data['#noscript']['#options'])) $data['#noscript']['#options'] = array();
            $this->_addon->getApplication()->CallUserFuncArray($data['#default_items_callback'], array((array)$data['#default_value'], &$data['#default_items'], &$data['#noscript']['#options']));
        }

        if (!isset($data['#noscript']['#type'])
            || !in_array($data['#noscript']['#type'], array('textfield', 'select'))
        ) {
            $data['#noscript']['#type'] = empty($data['#tagging']) ? 'select' : 'textfield';
        }
        if (!isset($data['#width'])) {
            $data['#width'] = isset($data['#field_prefix']) || isset($data['#field_suffix']) ? '90%' : '98%';
        }
        $children =  array(
            'select' => array(
                '#type' => 'item',
                '#markup' => sprintf('<input type="hidden" id="%s" name="%s[select]" value="%s" />', $ele_id, Sabai::h($name), Sabai::h(implode(',', (array)@$data['#default_value']))),
                '#id' => $ele_id . '-container',
                '#field_prefix' => @$data['#field_prefix'],
                '#field_suffix' => @$data['#field_suffix'],
                '#required' => @$data['#required'],
            ) + $form->defaultElementSettings(),
            'noscript' => array(
                '#title' => $data['#title'],
                '#description' => $data['#description'],
                '#multiple' => $data['#multiple'],
                '#prefix' => '<noscript>',
                '#suffix' => '</noscript>',
                '#field_prefix' => @$data['#field_prefix'],
                '#field_suffix' => @$data['#field_suffix'],
                '#required' => @$data['#required'],
            ) + $data['#noscript'] + $form->defaultElementSettings(),
            // Add a hidden value that will only be sent if js is disabled.
            // We cannot use #type = hidden here because we need to add those prefix/suffix.
            'is_noscript' => array(
                '#type' => 'markup',
                '#value' => sprintf('<input type="hidden" value="1" name="%s[is_noscript]" />', Sabai::h($name)),
                '#prefix' => '<noscript>',
                '#suffix' => '</noscript>',
            ) + $form->defaultElementSettings(),
        );
        if (isset($data['#default_value'])) {
            if ($children['noscript']['#type'] === 'textfield') {
                $children['noscript']['#default_value'] = implode(',', (array)$data['#default_value']);
            } else {
                $children['noscript']['#default_value'] = $data['#default_value'];
                $children['noscript']['#max_selection'] = $data['#max_selection'];
            }
        }
        $data['#noscript'] = $children['noscript'];
        $element = $form->createFieldset($name, $data, $children);

        // Register pre render callback if this is the first date element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }

        self::$_elements[$form->settings['#id']][$name] = array(
            'id' => $ele_id,
            'multiple' => $data['#multiple'],
            'width' => isset($data['#width']) ? $data['#width'] : '100%',
            'min_input_length' => $data['#min_input_length'],
            'placeholder' => (string)@$data['#attributes']['placeholder'],
            'ajax_url' => $data['#ajax_url'],
            'ajax_quiet_millis' => $data['#ajax_quiet_millis'],
            'ajax_request_limit' => @$data['#ajax_request_limit'],
            'ajax_request_param_term' => $data['#ajax_request_param_term'],
            'ajax_request_param_page' => $data['#ajax_request_param_page'],
            'ajax_response_param_items' => $data['#ajax_response_param_items'],
            'ajax_response_param_total' => $data['#ajax_response_param_total'],
            'default_items' => $data['#default_items'],
            'format_selection' => $data['#format_selection'],
            'format_result' => $data['#format_result'],
            'tagging' => !empty($data['#tagging'])
        );

        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Submitted from a browser with js disabled?
        if (!empty($value['is_noscript'])) {
            $type = $data['#noscript']['#type'];
            $name = $name . '[noscript]';
            $value = @$value['noscript'];
            $field_impl = $this->_addon->getApplication()->Form_FieldImpl($type);
            if ($type === 'textfield') {
                $value = explode(',', $value['select']);
                // Remove empty values
                $value = array_filter($value);
                foreach (array_keys($value) as $key) {
                    $field_impl->formFieldOnSubmitForm($name, $value[$key], $data['#noscript'], $form);
                }
                $this->_validateMaxSelection($name, $value, $data, $form);
            } else {
                $field_impl->formFieldOnSubmitForm($name, $value, $data['#noscript'], $form);
            }

            return;
        }

        $value = explode(',', $value['select']);
        // Remove empty values
        $value = array_filter($value);

        // Is it a required field?
        if (empty($value)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('This field may not be empty.', 'sabai'), $name . '[select]');
            }
            $value = array();

            return;
        }

        $this->_validateMaxSelection($name . '[select]', $value, $data, $form);
    }

    private function _validateMaxSelection($name, &$value, array $data, Sabai_Addon_Form_Form $form)
    {
        if (!$data['#multiple']) {
			$value = isset($value[0]) ? $value[0] : null;
			return;
		}
        
        if (!empty($data['#max_selection'])
            && count($value) > $data['#max_selection']
        ) {
            $form->setError(sprintf(__('Maximum of %d items is allowed for this field.', 'sabai'), $data['#max_selection']), $name);
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
        $form->renderChildElements($name, $data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_elements[$form->settings['#id']])) return;

        $js = array();
        foreach (self::$_elements[$form->settings['#id']] as $element) {
            $js[] = sprintf(
                '$("#%1$s-container").show().siblings("noscript").remove();
        $("#%1$s").select2({
            allowClear: true,
            width:"%17$s",
            placeholder: "%2$s",
            minimumInputLength: %15$d,
            multiple: %3$s,
            ajax: {
                url: "%4$s",
                dataType: "json",
                quietMillis: %16$d,
                data: function (term, page) { // page is the one-based page number tracked by Select2
                    return {
                        %6$s: term, //search term
                        %7$s: page // page number
                    };
                },
                results: function (data, page) {
                    var more = (page * %5$d) < data.%8$s; // whether or not there are more results available
                    // notice we return the value of more so Select2 knows if more results can be loaded
                    return {results: data.%9$s, more: more};
                }
            },
            initSelection : function (element, callback) {
                var data = %10$s;
                if (callback) {
                    callback(data);
                } else {
                    return data;
                }
            },
            formatResult: function (item) {
                %11$s
            },
            formatSelection: function (item) {
                %12$s
            },
            formatNoMatches: function () {
                return "%13$s";
            },
            formatInputTooShort: function (input, min) {
                return "%14$s";
            },
            createSearchChoice: %18$s
        });',
                $element['id'],
                $element['placeholder'],
                $element['multiple'] ? 'true' : 'false',
                $this->_addon->getApplication()->Url($element['ajax_url']),
                $element['ajax_request_limit'],
                $element['ajax_request_param_term'],
                $element['ajax_request_param_page'],
                $element['ajax_response_param_total'],
                $element['ajax_response_param_items'],
                // multiselect expects array, otherwise a single object
                json_encode($element['multiple'] ? $element['default_items'] : array_pop($element['default_items'])),
                $element['format_result'],
                $element['format_selection'],
                __('No matches found', 'sabai'),
                sprintf(__('Please enter %d or more characters', 'sabai'), $element['min_input_length']),
                $element['min_input_length'],
                $element['ajax_quiet_millis'],
                $element['width'],
                $element['tagging'] // enable the tagging mode?
                    ? 'function (term) { if (term.indexOf(",") !== -1 || term.indexOf(\'"\') !== -1 || term.indexOf("/") !== -1) { return; } else { return {id: term, text: term.replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\')}; } }'
                    : 'null'
            );
        }

        $form->addJs(sprintf(
            'jQuery(document).ready(function($) {
    %s
});',
            implode(PHP_EOL, $js)
        ));
    }
}
