<?php
class Sabai_Addon_Date_Helper_ValidateDatePickerFormField extends Sabai_Helper
{
    public function help(Sabai $application, $name, $dateValue, $textValue, array $data, Sabai_Addon_Form_Form $form, $timeValue = null)
    {
        $dateValue = trim((string)$dateValue);
        $textValue = trim((string)$textValue);
        $ret = isset($data['#empty_value']) ? $data['#empty_value'] : null;

        if (!strlen($textValue)) { // no value in text field, user must have edited it manually
            // Field required?
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please select a date.', 'sabai'), $name);
            }
            
            return $ret;
        } elseif (!strlen($dateValue)) { // no value in hidden field        
            if (strtotime($textValue)) { // is the textfield value a valid date datetime string?
                $dateValue = $textValue; // use the textfield value
            } else {
                // Field required?
                if ($form->isFieldRequired($data)) {
                    $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please select a date.', 'sabai'), $name);
                }
                
                return $ret;
            }
        }

        // Fetch year/month/day
        list($year, $month, $day) = $this->_getDate(strtotime($dateValue));
        if (!checkdate($month, $day, $year)) {
            $form->setError(__('Invalid date.', 'sabai'), $name);
            
            return $ret;
        }
        
        // Fetch hour/minute
        if (!empty($timeValue)) {
            $time = explode(':', $timeValue);
            if (count($time) !== 2 || !is_numeric($time[0]) || !is_numeric($time[1]) || $time[0] < 0 || $time[0] > 23 || $time[1] < 0 || $time[1] > 59) {
                $form->setError(__('Invalid time.', 'sabai'), $name);
                return mktime(0, 0, 0, $month, $day, $year);
            }
            $ret = mktime($time[0], $time[1], 0, $month, $day, $year);
        } else {
            $ret = mktime(0, 0, 0, $month, $day, $year);
        }
        
        $ret = $application->SiteToSystemTime($ret);

        // Make sure the submitted date falls between allowed date rage
        if (isset($data['#min_date']) && isset($data['#max_date'])) {
            if ($ret < $data['#min_date']
                || $ret > $data['#max_date']
            ) {
                $min_date_str = $application->DateTime($data['#min_date']);
                $max_date_str = $application->DateTime($data['#max_date']);
                $form->setError(sprintf(__('Date must be between %s and %s.', 'sabai'), $min_date_str, $max_date_str), $name);
            }
        } elseif (isset($data['#min_date'])) {
            if ($ret < $data['#min_date']) {
                $min_date_str = $application->DateTime($data['#min_date']);
                $form->setError(sprintf(__('Date must be later than %s.', 'sabai'), $min_date_str), $name);
            }
        } elseif (isset($data['#max_date'])) {
            if ($ret > $data['#max_date']) {
                $max_date_str = $application->DateTime($data['#max_date']);
                $form->setError(sprintf(__('Date must be earlier than %s.', 'sabai'), $max_date_str), $name);
            }
        }
        
        return $ret;
    }
    
    protected function _getDate($timestamp)
    {
        $date = getdate($timestamp);

        return array($date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes']);
    }
}