<?php
class Sabai_Addon_Date_DatePickerFormField implements Sabai_Addon_Form_IField
{
    protected $_addon;
    static private $_elements = array(), $_enableTimepicker = false;

    public function __construct(Sabai_Addon_Date $addon)
    {
        $this->_addon = $addon;
    }

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        $ele_date_id = $form->getFieldId($name) . '-date';
        $ele_time_id = $form->getFieldId($name) . '-time';
        
        if (!array_key_exists('#empty_value', $data)) {
            $data['#empty_value'] = null;
        }
        
        $data['#disable_time'] = !empty($data['#disable_time']);
        if (!isset($data['#default_value'])) {
            if (!empty($data['#current_date_selected'])) {
                $data['#default_value'] = $this->_addon->getApplication()->SystemToSiteTime(time());
            } else {
                $data['#default_value'] = $data['#empty_value'];
            }
        } elseif ($data['#default_value'] !== $data['#empty_value']) {
            $data['#default_value'] = $this->_addon->getApplication()->SystemToSiteTime($data['#default_value']);
        }

        // Define number of months to display on date picker
        if (0 >= $data['#number_months'] = intval(@$data['#number_months'])) {
            $data['#number_months'] = 1;
        }

        // Define min/max date
        if (isset($data['#min_date']) && !is_int($data['#min_date'])) {
            unset($data['#min_date']);
        }
        if (isset($data['#max_date'])) {
            if (!is_int($data['#max_date'])
                || (isset($data['#min_date']) && $data['#max_date'] < $data['#min_date'])
            ) {
                unset($data['#max_date']);
            }
        }

        // Build markup
        if (!$data['#disable_time']) {
            $markup = sprintf(
                '<input type="text" id="%1$s-text" name="%2$s[datetime][date]" value="%3$s" size="15" class="sabai-date-datepicker-date" />
<input type="text" id="%5$s" name="%2$s[datetime][time]" value="%4$s" size="5" class="sabai-date-datepicker-time" placeholder="HH:MM" />',
                $ele_date_id,
                Sabai::h($name),
                $data['#default_value'] !== $data['#empty_value'] ? date('Y/m/d', $data['#default_value']) : '',
                $data['#default_value'] !== $data['#empty_value'] ? date('H:i', $data['#default_value']) : '',
                $ele_time_id
            );
            // Enable timepicker script
            if (!self::$_enableTimepicker) {
                self::$_enableTimepicker = true;
            }
        } else {
            $markup = sprintf(
                '<input type="text" id="%s-text" name="%s[datetime][date]" value="%s" size="15" class="sabai-date-datepicker-date" />',
                $ele_date_id,
                Sabai::h($name),
                $data['#default_value'] !== $data['#empty_value'] ? date('Y/m/d', $data['#default_value']) : ''
            );
        }
        
        $children = array(
            'datetime' => array(
                '#type' => 'item',
                '#markup' => $markup,
                '#field_prefix' => @$data['#field_prefix'],
                '#field_suffix' => @$data['#field_suffix'],
                '#required' => @$data['#required'],
            ) + $form->defaultElementSettings(),
            'date' => array(
                '#type' => 'hidden',
                '#default_value' => $data['#default_value'] !== $data['#empty_value'] ? date('Y/m/d', $data['#default_value']) : '',
                '#attributes' => array('id' => $ele_date_id),
            ) + $form->defaultElementSettings(),
        );
        $data = array(
            '#tree' => true,
            '#children' => array($children),
        ) + $data + $form->defaultElementSettings();

        // Register pre render callback if this is the first date element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }

        self::$_elements[$form->settings['#id']][$name] = array(
            'date_id' => $ele_date_id,
            'time_id' => $ele_time_id,
            'min_date' => @$data['#min_date'],
            'max_date' => @$data['#max_date'],
            'disable_time' => $data['#disable_time'],
            'default_date' => $data['#default_value'] !== $data['#empty_value'] ? $data['#default_value'] : null,
            'number_months' => $data['#number_months'],
        );

        unset($data['#default_value'], $data['#value']);

        return $form->createFieldset($name, $data);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!$data['#disable_time']
            && ($value['datetime']['time'] = trim((string)@$value['datetime']['time']))
            && strlen($value['datetime']['time'])
        ) {
            $time = $value['datetime']['time'];
        } else {
            $time = null;
        }
        
        $value = $this->_addon->getApplication()
            ->Date_ValidateDatePickerFormField($name, @$value['date'], @$value['datetime']['date'], $data, $form, $time);
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
        // Enable time picker?
        if (self::$_enableTimepicker) {
            // Add js to set timepicker default options
            $js[] = sprintf(
                '$.timepicker.setDefaults({
            hourText: "%1$s",
            minuteText: "%2$s",
            amPmText: ["%3$s", "%4$s"],
            showLeadingZero: false,
            showNowButton: true,
            nowButtonText: "%5$s",
            showDeselectButton: true,
            deselectButtonText: "%6$s"
        });',
                    __('Hour', 'sabai'),
                    __('Minute', 'sabai'),
                    __('AM', 'sabai'),
                    __('PM', 'sabai'),
                    __('Now', 'sabai'),
                    __('Deselect', 'sabai')
            );
        }
        // Add js to instantiate date/time pickers
        foreach (self::$_elements[$form->settings['#id']] as $date) {
            $js[] = sprintf(
                '        SABAI.Date.datetimepicker({
            target: "#%1$s",
            minDate: %2$s,
            maxDate: %3$s,
            numberOfMonths: %4$d,
            timeTarget: %5$s
        });',
                $date['date_id'],
                isset($date['min_date']) ? sprintf('new Date(%d, %d, %d)', date('Y', $date['min_date']), date('n', $date['min_date']) - 1, date('j', $date['min_date'])) : 'null',
                isset($date['max_date']) ? sprintf('new Date(%d, %d, %d)', date('Y', $date['max_date']), date('n', $date['max_date']) - 1, date('j', $date['max_date'])) : 'null',
                $date['number_months'],
                !$date['disable_time'] ? sprintf('"#%s"', $date['time_id']) : 'null'
            );

        }
        // Add js
        $form->addJs(sprintf(
            'jQuery(document).ready(function ($) {
    var datetimepicker = function () {
        %s
    }
    if (typeof SABAI.Date === "undefined" || !$.isFunction(SABAI.Date.datetimepicker)) {
        $LAB.script("%s").wait(datetimepicker);
    } else {
        datetimepicker();
    }
});',
            implode(PHP_EOL, $js),
            implode('", "', $this->_addon->getApplication()->Date_Scripts())
            
        ));
    }
}