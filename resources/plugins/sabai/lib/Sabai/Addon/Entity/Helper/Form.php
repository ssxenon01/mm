<?php
class Sabai_Addon_Entity_Helper_Form extends Sabai_Helper
{    
    public function help(Sabai $application, $bundleName, array $values = null, $admin = false, $wrap = false)
    {
        if ($bundleName instanceof Sabai_Addon_Entity_IEntity) {
            $bundle = $application->Entity_Bundle($bundleName);
            if ($bundleName->getId()) {
                $entity = $bundleName;
            }
        } elseif ($bundleName instanceof Sabai_Addon_Entity_Model_Bundle) {
            $bundle = $bundleName;   
        } else {
            if (!$bundle = $application->Entity_Bundle($bundleName)) {
                throw new Sabai_RuntimeException('Invalid bundle: ' . $bundleName);
            }
        }
        $entity_type = $bundle->entitytype_name;
        
        // Fetch user roles for later use
        if (!$application->getUser()->isAnonymous()) {
            $current_user_roles = $application->getPlatform()->getUserRolesByUser($application->getUser()->getIdentity());
        } else {
            $current_user_roles = array('_guest_');
        }
        $super_user_roles = array_keys($application->SuperUserRoles());

        // Construct form settings
        $form = array('#inherits' => array('entity_form'), '#entity_type' => $entity_type, '#bundle' => $bundle);
        if ($wrap) {
            $form[$wrap] = array('#tree' => true);
        }
        // Load field values if an existing entity has been passed
        if (isset($entity)) {
            $application->Entity_LoadFields($entity_type, array($entity->getId() => $entity), null, true, false);
            $form['#entity'] = $entity;
        }
        if (isset($values)) {
            // The values are supplied here to check if fields have been added dynamically before submit. 
            // Form fields will be populated after the form submission is validated, so do not populate them here.
            $do_not_populate_fields = true;
        }
        
        $fields = array();
        foreach ($bundle->Fields->with('FieldConfig') as $field) {
            if (!$field->getFieldWidget() || $field->getFieldDisabled()) continue;
             
            // Any user role restriction to view this field?
            if (!$user_roles = (array)$field->getFieldData('user_roles')) {
                // No user roles defined
                if ($field->isCustomField()) {
                    continue;
                }
            } else {
                $has_role = false;
                foreach ($user_roles as $role) {
                    // Admin roles must check differently because WP network admin does not have a role
                    if (in_array($role, $super_user_roles)) {
                        if ($application->getUser()->isAdministrator()) {
                            $has_role = true;
                            break;
                        }
                    } else {
                        if (in_array($role, $current_user_roles)) {
                            $has_role = true;
                            break;
                        }
                    }
                }
                if (!$has_role) {
                    continue; // the current user does not have a role that is permitted to view this field
                }
            }
            $fields[$field->getFieldName()] = $field;
        }

        foreach ($application->doFilter('EntityFormFields', $fields, array(isset($entity) ? $entity : $bundleName, $admin)) as $field_name => $field) {
            $ifieldwidget = $application->Field_WidgetImpl($field->getFieldWidget());
            $field_value = null;
            if ($admin) {
                $field_title = $field->getFieldAdminTitle();
                if (!strlen($field_title)) {
                    $field_title = $field->getFieldTitle();
                }
            } else {
                $field_title = $field->getFieldTitle();
            }
            $form_ele = array(
                '#tree' => true,
                '#title' => $field_title,
                '#description' => $field->getFieldDescription(),
                '#weight' => $field->getFieldWeight(),
                '#required' => $field->getFieldRequired(),
                '#collapsible' => false,
            );
            if (isset($values[$field_name])) {
                if (is_array($values[$field_name])
                    && array_key_exists(0, $values[$field_name])
                ) {
                    $field_value = $values[$field_name];
                }
            } elseif (isset($entity)) {
                if (!$field->isPropertyField()) {
                    $field_value = $entity->getFieldValue($field_name);
                } else {
                    $field_value = $entity->getProperty($field_name);
                    if ($field_value !== null) {
                        $field_value = array($field_value);
                    }
                }
            }
                
            if (!$ifieldwidget->fieldWidgetGetInfo('accept_multiple')) {
                if ($repeatable = $ifieldwidget->fieldWidgetGetInfo('repeatable')) {
                    $repeatable = (array)$repeatable;
                    if (!isset($repeatable['group_fields']) || $repeatable['group_fields'] !== false) {
                        $form_ele['#class'] = 'sabai-form-group';
                    }
                    if (!empty($field_value)) {
                        $field_element_count = count($field_value);
                        for ($i = 0; $i < $field_element_count; ++$i) {
                            if (!$form_ele[$i] = $this->_getEntityFormElement($application, $bundle, $field, $i, empty($do_not_populate_fields) ? array_shift($field_value) : null, !isset($entity))) {
                                continue 2;
                            }
                        }
                    } else {
                        if (!$form_ele[0] = $this->_getEntityFormElement($application, $bundle, $field, 0, null, !isset($entity))) {
                            continue;
                        }
                        $field_element_count = 1;
                    }
                    if (0 === $max_num_values = $field->getFieldMaxNumItems()) {
                        $form_ele['add'] = $this->_getEntityFormAddElementLink($field, $repeatable);
                    } else {
                        if ($field_element_count > $max_num_values) {
                            $form_ele = array_slice($form_ele, 0, $max_num_values);
                        } elseif ($field_element_count < $max_num_values) {
                            for ($i = $field_element_count; $i < $max_num_values; $i++) {
                                if (!$form_ele[$i] = $this->_getEntityFormElement($application, $bundle, $field, $i, null, !isset($entity))) {
                                    continue 2;
                                }
                            }
                        }
                    }
                } else {
                    if (!$_form_ele = $this->_getEntityFormElement($application, $bundle, $field, 0, isset($field_value) && empty($do_not_populate_fields) ? array_shift($field_value) : null, !isset($entity))) {
                        continue;
                    }
                    if (isset($_form_ele['#type'])) {
                        switch ($_form_ele['#type']) {
                            case 'hidden':
                                continue;
                            case 'markup':
                            case 'sectionbreak':
                                // prevent the form element from being rendered as a fieldset
                                $form_ele = array('#weight' => $field->getFieldWeight()) + $_form_ele;
                                break;
                            default:
                                $form_ele[0] = $_form_ele;
                        }
                    } else {
                        $form_ele[0] = $_form_ele;
                    }
                }
            } else {
                if (!$_form_ele = $this->_getEntityFormElement($application, $bundle, $field, null, empty($do_not_populate_fields) ? $field_value : null, !isset($entity))) {
                    continue;
                }
                $form_ele = $_form_ele + $form_ele;
                $form_ele['#required'] = $field->getFieldRequired();
            }
            if (isset($form_ele[0])) {
                // Make only the first element required if multiple input fields 
                $form_ele[0]['#required'] = $field->getFieldRequired();
                // Remove container labels if any defined by the element
                if (array_key_exists('#title', $form_ele[0])) {
                    $form_ele['#title'] = $form_ele[0]['#title'];
                    unset($form_ele[0]['#title']);
                }
                if (array_key_exists('#description', $form_ele[0])) {
                    $form_ele['#description'] = $form_ele[0]['#description'];
                    unset($form_ele[0]['#description']);
                }
            }
            if ($wrap) {
                $form[$wrap][$field_name] = $form_ele;
            } else {
                $form[$field_name] = $form_ele;
            }
        }

        return $form;
    }

    private function _getEntityFormElement(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Field_IField $field, $key = null, $value = null, $setDefaultValue = true)
    {
        $iwidget = $application->Field_WidgetImpl($field->getFieldWidget());
        // Set bundle if instance of Sabai_Addon_Entity_IFieldWidget
        if ($iwidget instanceof Sabai_Addon_Entity_IFieldWidget) {
            $iwidget->entityFieldWidgetSetBundle($bundle);
        }
        // Init widget settings
        $widget_settings = $field->getFieldWidgetSettings() + (array)$iwidget->fieldWidgetGetInfo('default_settings');
        $parents = isset($key) ? array($field->getFieldName(), $key) : array($field->getFieldName());
        if (!$ele = $iwidget->fieldWidgetGetForm($field, $widget_settings, $value, $parents)) {
            // do not display this form element
            return false;
        }
        $class = 'sabai-entity-field ' . str_replace('_', '-', 'sabai-entity-field-type-' . $field->getFieldType() . ' sabai-entity-field-name-' . $field->getFieldName());
        if (!isset($ele['#class'])) {
            $ele['#class'] = $class;
        } else {
            $ele['#class'] .= ' ' . $class;
        }
        if ($setDefaultValue) {
            if (!isset($ele['#default_value'])) {
                $default_value = $field->getFieldDefaultValue();
                $ele['#default_value'] = $iwidget->fieldWidgetGetInfo('accept_multiple') ? $default_value : $default_value[0];
            }
        } else {
            if ($value === null) { // set default value to null if no entity value
                $ele['#default_value'] = null;
            }
        }
        // Make the field not required by default. This will be overriden by the actual setting if needed.
        $ele['#required'] = false;

        return $ele;
    }

    private function _getEntityFormAddElementLink(Sabai_Addon_Field_IField $field, array $options)
    {
        return array(
            '#type' => 'item',
            '#markup' => sprintf(
                '<a href="#" onclick="%s" class="sabai-btn sabai-btn-mini"><i class="sabai-icon-plus"></i> %s</a>',
                sprintf(
                    'return SABAI.cloneField(jQuery(this).parent().parent(), \'%s\', %s);',
                    $field->getFieldName(),
                    isset($options['callback']) ? $options['callback'] : 'null'
                ),
                isset($options['label']) ? Sabai::h($options['label']) : __('Add More', 'sabai')
            ),
        );
    }
}
