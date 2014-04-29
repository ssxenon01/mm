<?php
class Sabai_Addon_FieldUI_Controller_Admin_CreateField extends Sabai_Addon_Form_Controller
{
    private $_fieldType, $_fieldWidget, $_bundle, $_field;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';
        $this->_submitButtons = array(array('#value' => __('Add Field', 'sabai'), '#btn_type' => 'primary'));

        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        $form['#enable_storage'] = true;
        $form['#bundle'] = $this->_bundle->name;

        // Get available field types
        $field_types = $this->Field_Types();

        // Creating from an existing field?
        if (($field_name = $context->getRequest()->asStr('field_name'))
            && ($field = $this->getModel('FieldConfig', 'Entity')->name_is($field_name)->fetchOne())
        ) {
            $this->_fieldType = $field->type;
            if (!$field_types[$this->_fieldType]['creatable']) {
                // The field is not reuseable.
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
            if ($this->getModel('Field', 'Entity')->bundleId_is($this->_bundle->id)->fieldconfigId_is($field->id)->count()) {
                // The field is already added to the bundle.
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
            $this->_field = $field;
        } else {
            // Make sure a valid field type is in the request
            if (!$this->_fieldType = $context->getRequest()->asStr('field_type')) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        if (empty($field_types[$this->_fieldType]['widgets'])
            || !$field_types[$this->_fieldType]['creatable']
        ) {
            $context->setError(__('Invalid field type.', 'sabai'));
            return false;
        }

        // Make sure a valid widget type is in the request
        if (!isset($formStorage['field_widget'])
            || (!$this->_fieldWidget = $this->getModel('Widget', 'Field')->name_is($formStorage['field_widget'])->fetchOne())
        ) {
            unset($formStorage['field_widget']);
            
            if (count($field_types[$this->_fieldType]['widgets']) > 1) {
                // Display widget selection form
                $this->_submitButtons = array(array('#value' => __('Next', 'sabai')));
                asort($field_types[$this->_fieldType]['widgets']);
                $form['field_widget'] = array(
                    '#type' => 'radios',
                    '#title' => __('Form element type', 'sabai'),
                    '#description' => __('Select the type of form element you would like to present to the user when editing this field.', 'sabai'),
                    '#options' => $field_types[$this->_fieldType]['widgets'],
                    '#default_value' => $field_types[$this->_fieldType]['default_widget'],
                    '#required' => true,
                );
                $form['field_type'] = array(
                    '#type' => 'hidden',
                    '#value' => $this->_fieldType,
                );
                if ($this->_field) {
                    $form['field_name'] = array(
                        '#type' => 'hidden',
                        '#value' => $this->_field->name,
                    );
                }

                return $form;
            }
            
            // Only 1 field widget available for this field, so try to select it and proceed
            $formStorage['field_widget'] = array_shift(array_keys($field_types[$this->_fieldType]['widgets']));
            if (!$this->_fieldWidget = $this->getModel('Widget', 'Field')->name_is($formStorage['field_widget'])->fetchOne()) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        // Set options
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            jQuery(SABAI).trigger("sabai.fieldui.field.created", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            target.hide();
        }';

        $ifieldtype = $this->Field_TypeImpl($this->_fieldType);
        $ifieldwidget = $this->Field_WidgetImpl($this->_fieldWidget->name);
        $settings = (array)$ifieldtype->fieldTypeGetInfo('default_settings');
        $widget_settings = (array)$ifieldwidget->fieldWidgetGetInfo('default_settings');
        
        if ($this->_field) {
            $form['name'] = array(
                '#type' => 'item',
                '#title' => __('Field name', 'sabai'),
                '#markup' => $this->_field->name,
            );
            $form['field_name'] = array(
                '#type' => 'hidden',
                '#value' => $this->_field->name,
            );
        } else {
            $form['name'] = array(
                '#type' => 'textfield',
                '#title' => __('Field name', 'sabai'),
                '#description' => __('Enter a machine readable name for this form field. Only lowercase alphanumeric characters and underscores are allowed.', 'sabai'),
                '#max_length' => 255,
                '#required' => true,
                '#weight' => 2,
                '#size' => 20,
                '#regex' => '/^[a-z0-9_]+$/',
                '#element_validate' => array(array($this, 'validateName')),
                '#field_prefix' => 'field_',
            );
        }
        
        // Define fieldsets
        $form['basic'] = array(
            '#type' => 'fieldset',
            '#title' => __('Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 10,
            '#collapsed' => false,
        );
        $form['visibility'] = array(
            '#type' => 'fieldset',
            '#title' => __('Visibility Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 20,
            '#collapsed' => true,
        );
        $form['advanced'] = array(
            '#type' => 'fieldset',
            '#title' => __('Advanced Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 30,
            '#collapsed' => true,
        );
        
        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_title')) {
            $form['basic']['title'] = array(
                '#type' => 'textfield',
                '#title' => __('Label', 'sabai'),
                '#max_length' => 255,
                '#default_value' => null,
                '#weight' => 4,
            );
        }
        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_description')) {
            $form['basic']['description'] = array(
                '#type' => 'textarea',
                '#title' => __('Description', 'sabai'),
                '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
                '#rows' => 3,
                '#default_value' => null,
                '#weight' => 6,
            );
        }
        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_required')) {
            $form['basic']['required'] = array(
                '#type' => 'checkbox',
                '#title' => __('Required', 'sabai'),
                '#default_value' => $ifieldwidget->fieldWidgetGetInfo('default_required') ? true : null,
                '#weight' => 8,
            );
        }
        if ($ifieldwidget->fieldWidgetGetInfo('enable_edit_disabled')) {
            $form['basic']['disabled'] = array(
                '#type' => 'checkbox',
                '#title' => __('Disabled', 'sabai'),
                '#default_value' => $ifieldwidget->fieldWidgetGetInfo('default_disabled') ? true : null,
                '#weight' => 9,
            );
        }

        // Add an option to make this field repeatable if the selected widget supports the feature
        if (!$ifieldwidget->fieldWidgetGetInfo('accept_multiple')) {
            if ($ifieldwidget->fieldWidgetGetInfo('repeatable')) {
                if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_max_num_items')) {
                    $form['basic']['max_num_items'] = array(
                        '#type' => 'select',
                        '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                        '#title' => __('Maximum number of values', 'sabai'),
                        '#description' => __('Maximum number of values users can enter for this field. The "Unlimited" option will display an "Add another item" link so the users can add as many values as they like.', 'sabai'),
                        '#default_value' => $this->_getMaxNumItemsDefault($ifieldtype, 1),
                        '#weight' => 60,
                    );
                } else {
                    $form['basic']['max_num_items'] = array(
                        '#type' => 'hidden',
                        '#value' => $this->_getMaxNumItemsDefault($ifieldtype, 1),
                    );
                }
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => 1,
                );
            }
        } else {
            if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_max_num_items')) {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'select',
                    '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                    '#title' => __('Maximum number of values', 'sabai'),
                    '#description' => __('Maximum number of values users can enter for this field.', 'sabai'),
                    '#default_value' => $this->_getMaxNumItemsDefault($ifieldtype, 0),
                    '#weight' => 60,
                );
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => $this->_getMaxNumItemsDefault($ifieldtype, 0),
                );
            }
        }

        if (!$this->_field) {
            if ($settings_form = (array)@$ifieldtype->fieldTypeGetSettingsForm($settings, array('settings'))) {
                // Add field specific settings form
                $form['basic']['settings'] = array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                    '#tree_allow_override' => false,
                    '#weight' => 40,
                );
                $form['basic']['settings'] += $settings_form;
            }
        }

        if ($widget_settings_form = (array)@$ifieldwidget->fieldWidgetGetSettingsForm($this->_fieldType, $settings, $widget_settings, array('widget_settings'))) {
            $form['basic']['widget_settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            ) + $widget_settings_form;
        }
        
        $roles = $this->System_Roles(
            'title',
            $this->_bundle->entitytype_name !== 'content' // no guest role if the bundle is not the content entity type
                || @$this->_bundle->info['content_guest_author'] === false // no guest role if the bundle does not support guest authors
        );
        $form['visibility']['user_roles'] = array(
            '#type' => 'checkboxes',
            '#description' => __('Select user roles to which this field is visible when submitting the form.', 'sabai'),
            '#options' => $roles,
            '#default_value' => (null !== $default_user_roles = $ifieldwidget->fieldWidgetGetInfo('default_user_roles')) ? $default_user_roles : array_keys($roles),
            '#weight' => 1,
        );
        
        $form['advanced']['admin_title'] = array(
            '#type' => 'textfield',
            '#title' => __('Admin label', 'sabai'),
            '#description' => __('The admin label will be used in place of the field label anywhere in the admin where the name of the field is displayed.', 'sabai'),
            '#max_length' => 255,
            '#default_value' => null,
            '#weight' => 80,
        );

        // Add a field for setting the default value if the widget supports default values
        if ($default_value_form = $ifieldwidget->fieldWidgetGetEditDefaultValueForm($this->_fieldType, $settings, $widget_settings, array('default_value'))) {
            $form['advanced']['default_value'] = array(
                '#title' => __('Default value', 'sabai'),
                '#weight' => 70,
            ) + $default_value_form;
        }

        $form['field_type'] = array(
            '#type' => 'hidden',
            '#value' => $this->_fieldType,
        );
        
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );

        $form['#field_type'] = $this->_fieldType;

        return $form;
    }
    
    private function _getMaxNumItemsOptions($ifieldtype)
    {
        if ($max_num_items_options = $ifieldtype->fieldTypeGetInfo('max_num_items_options')) {
            return array_combine($max_num_items_options, $max_num_items_options);
        }
        return array(__('Unlimited', 'sabai')) + array_combine(range(1, 10), range(1, 10));
    }
        
    private function _getMaxNumItemsDefault($ifieldtype, $default)
    {
        if (null !== $max_num_items_default = $ifieldtype->fieldTypeGetInfo('max_num_items_default')) {
            return $max_num_items_default;
        }
        return $default;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!isset($this->_fieldWidget)) {
            // Widget selection form was submitted

            if (isset($form->values['field_widget'])) {
                $form->storage['field_widget'] = $form->values['field_widget'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }
        
        // Get available field types
        $field_types = $this->Field_Types();
        $field_info = array('type' => $this->_fieldType);
        // Is it an existing field?
        if ($this->_field) {
            $field_name = $this->_field->name;
            $field_info['settings'] = $this->_field->settings;
        } else {
            $field_name = 'field_' . $form->values['name'];
            if (isset($form->settings['basic']['settings']) && empty($form->settings['basic']['settings']['#disabled'])) {
                $field_info['settings'] = $form->values['settings'];
            }
        }
        
        if (isset($form->settings['basic']['title'])) {
            $field_info['title'] = $form->values['title'];
        } else {
            $field_info['title'] = '';
        }
        if (isset($form->settings['advanced']['admin_title'])) {
            if (!isset($form->values['admin_title']) || !strlen($form->values['admin_title'])) {
                if (isset($field_info['title']) && strlen($field_info['title'])) {
                    $field_info['admin_title'] = $field_info['title'];
                } else {
                    $field_info['admin_title'] = $field_types[$this->_fieldType]['label'];
                }
            } else {
                $field_info['admin_title'] = $form->values['admin_title'];
            }
            
        }
        if (isset($form->settings['basic']['description'])) {
            $field_info['description'] = $form->values['description'];
        }
        if (isset($form->settings['basic']['required'])) {
            $field_info['required'] = !empty($form->values['required']);
        }
        if (isset($form->settings['basic']['disabled'])) {
            $field_info['disabled'] = !empty($form->values['disabled']);
        }
        if (isset($form->settings['basic']['max_num_items'])) {
            $field_info['max_num_items'] = $form->values['max_num_items'];
        }
        $field_info['widget'] = $this->_fieldWidget->name;
        if (isset($form->settings['basic']['widget_settings'])) {
            $field_info['widget_settings'] = (array)@$form->values['widget_settings'];
        }
        if (isset($form->settings['advanced']['default_value'])) {
            $field_info['default_value'] = (array)@$form->values['default_value'];
        }
        if (isset($form->settings['visibility']['user_roles'])) {
            $field_info['data']['user_roles'] = (array)@$form->values['user_roles'];
        }
        $field_info['weight'] = 99;
        
        $field = $this->getAddon('Entity')->createEntityField($this->_bundle, $field_name, $field_info, Sabai_Addon_Entity::FIELD_REALM_ALL);
        
        $context->setSuccess($this->_bundle->getPath() . '/fields')
            ->setSuccessAttributes(array(
                'id' => $field->id,
                'title' => Sabai::h($field->getFieldTitle()),
                'description' => $field->getFieldDescription(),
                'type' => $field->getFieldType(),
                'type_normalized' => str_replace('_', '-', $field->getFieldType()),
                'admin_title' => Sabai::h($field->getFieldAdminTitle()),
                'name' => $field->getFieldName(),
                'required' => $field->getFieldRequired() ? 1 : 0,
                'disabled' => $field->getFieldDisabled() ? 1 : 0,
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewWidget($field),
            ));
        
        $this->doEvent('FieldUISubmitFieldSuccess', array($field, /*$isEdit*/ false));
    }

    public function validateName(Sabai_Addon_Form_Form $form, &$value, $element)
    {
        // Make sure the field name is unique
        $field_name = 'field_' . $value;
        $repository = $this->getModel('FieldConfig', 'Entity')->name_is($field_name);
        if ($repository->count() > 0) {
            $form->setError(__('The field name is already in use by another field.', 'sabai'), $element);
        }
    }
}
