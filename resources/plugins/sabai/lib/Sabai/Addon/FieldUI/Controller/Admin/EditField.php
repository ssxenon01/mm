<?php
class Sabai_Addon_FieldUI_Controller_Admin_EditField extends Sabai_Addon_Form_Controller
{
    private $_field, $_bundle;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';

        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        $form['#enable_storage'] = true;
        $form['#bundle'] = $this->_bundle->name;

        // Get available field types
        $field_types = $this->Field_Types();

        if ((!$field_id = $context->getRequest()->asInt('field_id'))
            || (!$this->_field = $this->getModel('Field', 'Entity')->fetchById($field_id))
        ) {
            return false;
        } else {
            if (empty($field_types[$this->_field->getFieldType()]['widgets'])) {
                // no supported widgets for this field type
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        // Set options
        $this->_submitButtons = array(array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary'));
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            jQuery(SABAI).trigger("sabai.fieldui.field.updated", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            jQuery(SABAI).trigger(\'sabai.fieldui.field.cancelled\', {trigger: jQuery(this), target: target});
        }';

        $ifieldtype = $this->Field_TypeImpl($this->_field->getFieldType());
        try {
            $ifieldwidget = $this->Field_WidgetImpl($this->_field->getFieldWidget());
        } catch (Sabai_IException $e) {
            $default_widget = $field_types[$this->_field->getFieldType()]['default_widget'];
            if ($default_widget == $this->_field->getFieldWidget()) {
                // the default widget is the one that does not exist
                throw $e;
            }
            // Change widget to the default widget
            $this->_field->setFieldWidget($default_widget)->commit();
            $ifieldwidget = $this->Field_WidgetImpl($default_widget);
        }
        $settings = $this->_field->getFieldSettings() + (array)$ifieldtype->fieldTypeGetInfo('default_settings');
        $widget_settings = $this->_field->getFieldWidgetSettings() + (array)$ifieldwidget->fieldWidgetGetInfo('default_settings');
        
        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_field->id,
        );
        
        
        // Define fieldsets
        $form['basic'] = array(
            '#type' => 'fieldset',
            '#title' => __('Basic Settings', 'sabai'),
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
        
        // Display field type and link to switch to another widget
        $edit_widget_url = $this->Url($this->_bundle->getPath() . '/fields/edit_widget');
        $edit_widget_url['params'] = array('field_id' => $this->_field->id, 'ele_id' => $context->getRequest()->asStr('ele_id')) + $edit_widget_url['params'];
        $form['field'] = array(
            '#type' => 'item',
            '#field_prefix' => __('Form element type:', 'sabai'),
            '#value' => $this->LinkToRemote($ifieldwidget->fieldWidgetGetInfo('label'), $context->getContainer(), $edit_widget_url, array('scroll' => true)),
            '#weight' => 1,
        );

        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_title')) {
            $form['basic']['title'] = array(
                '#type' => 'textfield',
                '#title' => __('Label', 'sabai'),
                '#max_length' => 255,
                '#default_value' => $this->_field->getFieldTitle(),
                '#weight' => 4,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['title'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldTitle(),
            );
        }
        $form['advanced']['admin_title'] = array(
            '#type' => 'textfield',
            '#title' => __('Admin label', 'sabai'),
            '#description' => __('The admin label will be used in place of the field label anywhere in the admin where the name of the field is displayed.', 'sabai'),
            '#max_length' => 255,
            '#default_value' => $this->_field->getFieldAdminTitle(),
            '#weight' => 1,
        );
        
        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_description')) {
            $form['basic']['description'] = array(
                '#type' => 'textarea',
                '#title' => __('Description', 'sabai'),
                '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
                '#rows' => 3,
                '#default_value' => $this->_field->getFieldDescription(),
                '#weight' => 6,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['description'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldDescription(),
            );
        }
        if (!$ifieldwidget->fieldWidgetGetInfo('disable_edit_required')) {
            $form['basic']['required'] = array(
                '#type' => 'checkbox',
                '#title' => __('Required', 'sabai'),
                '#default_value' => $this->_field->getFieldRequired(),
                '#weight' => 8,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['required'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldRequired(),
            );
        }
        if ($ifieldwidget->fieldWidgetGetInfo('enable_edit_disabled')) {
            $form['basic']['disabled'] = array(
                '#type' => 'checkbox',
                '#title' => __('Disabled', 'sabai'),
                '#default_value' => $this->_field->getFieldDisabled(),
                '#weight' => 9,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['disabled'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldDisabled(),
            );
        }

        // Add an option to make this field repeatable if the selected widget supports the feature
        if (!$ifieldwidget->fieldWidgetGetInfo('accept_multiple')) {
            if ($ifieldwidget->fieldWidgetGetInfo('repeatable')) {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'select',
                    '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                    '#title' => __('Maximum number of values', 'sabai'),
                    '#description' => __('Maximum number of values users can enter for this field. The "Unlimited" option will display an "Add another item" link so the users can add as many values as they like.', 'sabai'),
                    '#default_value' => $this->_field->id ? $this->_field->getFieldMaxNumItems() : $this->_getMaxNumItemsDefault($ifieldtype, 1),
                    '#weight' => 60,
                );
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => 1,
                );
            }
        } else {
            $form['basic']['max_num_items'] = array(
                '#type' => 'select',
                '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                '#title' => __('Maximum number of values', 'sabai'),
                '#description' => __('Maximum number of values users can enter for this field.', 'sabai'),
                '#default_value' => $this->_field->id ? $this->_field->getFieldMaxNumItems() : $this->_getMaxNumItemsDefault($ifieldtype, 0),
                '#weight' => 60,
            );
        }

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

        if ($this->_field->isPropertyField()) {
            unset($form['basic']['required'], $form['basic']['disabled'], $form['basic']['max_num_items'], $form['basic']['settings']);
        } else {
            if ($this->_field->fieldconfig_id) {
                //$form['basic']['settings']['#disabled'] = true;
            }
        }
        if ($widget_settings_form = (array)@$ifieldwidget->fieldWidgetGetSettingsForm($this->_field->getFieldType(), $settings, $widget_settings, array('widget_settings'))) {
            $form['basic']['widget_settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            ) + $widget_settings_form;
        }

        // Add a field for setting the default value if the widget supports default values
        $this->_field->setFieldSettings($settings)->setFieldWidgetSettings($widget_settings); // make sure all settings are available
        if ($default_value_form = $ifieldwidget->fieldWidgetGetEditDefaultValueForm($this->_field->getFieldType(), $settings, $widget_settings, array('default_value'))) {
            $form['advanced']['default_value'] = array(
                '#title' => __('Default value', 'sabai'),
                '#weight' => 2,
            ) + $default_value_form;
            if (!isset($form['advanced']['default_value']['#default_value'])) {
                $default_value = $this->_field->getFieldDefaultValue();
                $form['advanced']['default_value']['#default_value'] = $ifieldwidget->fieldWidgetGetInfo('accept_multiple') ? $default_value : $default_value[0];
            }
        }

        $form['field_type'] = array(
            '#type' => 'hidden',
            '#value' => $this->_field->getFieldType(),
        );
        
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );
        
        if ($this->_field->isCustomField()) {
            $roles = $this->System_Roles(
                'title',
                $this->_bundle->entitytype_name !== 'content' // no guest role if the bundle is not the content entity type
                    || @$this->_bundle->info['content_guest_author'] === false // no guest role if the bundle does not support guest authors
            );
            $form['visibility']['user_roles'] = array(
                '#type' => 'checkboxes',
                '#description' => __('Select user roles to which this field is visible when submitting the form.', 'sabai'),
                '#options' => $roles,
                '#default_value' => $this->_field->hasFieldData('user_roles') ? $this->_field->getFieldData('user_roles') : array_keys($roles),
                '#weight' => 1,
            );
        }
        
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
        if (!isset($this->_field)) {
            // Widget selection form was submitted

            if (isset($form->values['field_widget'])) {
                $form->storage['field_widget'] = $form->values['field_widget'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }

        // Field edit form submitted
        
        $field_is_new = $this->_field->id ? false : true;
        
        $field_info = array('type' => $this->_field->getFieldType());
        if (isset($form->settings['basic']['title'])) {
            $field_info['title'] = $form->values['title'];
        }
        if (isset($form->settings['advanced']['admin_title'])) {
            $field_info['admin_title'] = $form->values['admin_title'];
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
        if (isset($form->settings['basic']['settings']) && empty($form->settings['basic']['settings']['#disabled'])) {
            $field_info['settings'] = $form->values['settings'];
        }
        $field_info['widget'] = $this->_field->getFieldWidget();
        if (isset($form->settings['basic']['widget_settings'])) {
            $field_info['widget_settings'] = (array)@$form->values['widget_settings'];
        }
        if (isset($form->settings['basic']['default_value'])) {
            $field_info['default_value'] = (array)@$form->values['default_value'];
        }
        if (isset($form->settings['visibility']['user_roles'])) {
            $field_info['data']['user_roles'] = (array)@$form->values['user_roles'];
        }
        $field_info['weight'] = $this->_field->getFieldWeight();
        
        if ($this->_field->isPropertyField()) {
            $field = $this->getAddon('Entity')->createEntityPropertyField(
                $this->_bundle,
                $this->_field->FieldConfig,
                $field_info,
                true, // commit
                true // overwrite
            );
        } else {
            $field = $this->getAddon('Entity')->createEntityField(
                $this->_bundle,
                $this->_field->FieldConfig,
                $field_info,
                Sabai_Addon_Entity::FIELD_REALM_ALL,
                true // overwrite
            );
        }

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
                'is_new' => $field_is_new,
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewWidget($field),
            ));
        
        $this->doEvent('FieldUISubmitFieldSuccess', array($field, /*$isEdit*/ !$field_is_new));
    }

    public function validateName(Sabai_Addon_Form_Form $form, $value, $element)
    {
        // Make sure the field name is unique
        $field_name = 'field_' . $value;
        $repository = $this->getModel('FieldConfig', 'Entity')->name_is($field_name);
        // Skip counting self
        if ($this->_field->fieldconfig_id) $repository->id_isNot($this->_field->fieldconfig_id);
        if ($repository->count() > 0) {
            $form->setError(__('The field name is already in use by another field.', 'sabai'), $element);
        }
    }
}