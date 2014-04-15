<?php
class Sabai_Addon_Autocomplete_UserFormField extends Sabai_Addon_Autocomplete_FormField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $defaults = array(
            '#ajax_url' => $this->_addon->getApplication()->MainUrl('/sabai/user/_autocomplete.json'),
            '#default_items_callback' => array($this, 'getDefaultUsers'),
            '#noscript' => array('#type' => 'textfield'),
            '#tagging' => false,
            '#format_selection' => 'return "<img alt=\'"+ item.username +"\' style=\'vertical-align:middle; height:20px;\' src=\'" + item.gravatar + "\' /> " + item.text;',
            '#format_result' => 'return "<img alt=\'"+ item.username +"\' style=\'vertical-align:middle; height:20px;\' src=\'" + item.gravatar + "\' /> " + item.text;',
        );
        $data = $defaults + $data;
        if (!isset($data['#attributes']['placeholder'])) {
            $data['#attributes']['placeholder'] = __('Select User', 'sabai');
        }
        
        return parent::formFieldGetFormElement($name, $data, $form);        
    }

    public function getDefaultUsers($defaultValue, &$defaultItems, &$noscriptOptions)
    {
        $identities = $this->_addon->getApplication()
            ->getPlatform()
            ->getUserIdentityFetcher()
            ->fetchByIds((array)$defaultValue);
        foreach ($identities as $identity) {
            $id = $identity->id;
            $text = $identity->name;
            $defaultItems[] = array(
                'id' => $id,
                'text' => Sabai::h($text),
                'username' => $identity->username,
                'gravatar' => $this->_addon->getApplication()
                    ->GravatarUrl($identity->email, Sabai::THUMBNAIL_SIZE_SMALL, $identity->gravatar_default, $identity->gravatar_rating),
            );
            $noscriptOptions[$id] = $text;
        }
    }
}