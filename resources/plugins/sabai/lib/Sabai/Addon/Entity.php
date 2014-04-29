<?php
class Sabai_Addon_Entity extends Sabai_Addon
    implements Sabai_Addon_Entity_IFieldStorages,
               Sabai_Addon_Entity_IFieldCache,
               Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.2.30', PACKAGE = 'sabai';
    const FIELD_REALM_ALL = 0, FIELD_REALM_ENTITY_TYPE_DEFAULT = 1, FIELD_REALM_BUNDLE_DEFAULT = 2;

    private static $_reservedBundleNames = array('users', 'my', 'flagged', 'add', 'comments', 'vote');
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        return array(
            '/settings/entity' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'callback_path' => 'settings'
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'settings':
                return __('File Settings', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */

    /* Start implementation of Sabai_Addon_Entity_IFieldStorages */

    public function entityGetFieldStorageNames()
    {
        return array('sql');
    }

    public function entityGetFieldStorage($storageName)
    {
        switch ($storageName) {
            case 'sql':
                return new Sabai_Addon_Entity_FieldStorage_Sql($this->_application, $storageName);
        }
    }

    /* End implementation of Sabai_Addon_Entity_IFieldStorages */

    public function onEntityITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->entityGetTypeNames()) return;

        $this->_createEntityTypes($addon, $names);
        
        $this->_application->getHelperBroker()->resetHelper('Entity_TypeImpl');
    }

    public function onEntityITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteEntityTypes($addon);
        
        $this->_application->getHelperBroker()->resetHelper('Entity_TypeImpl');
    }

    public function onEntityITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->entityGetTypeNames()) {
            $this->_deleteEntityTypes($addon);
        } else {
            $current_entity_types = array();
            foreach ($this->getModel('EntityType')->addon_is($addon->getName())->fetch() as $current_entity_type) {
                if (in_array($current_entity_type->name, $names)) {
                    $current_entity_types[$current_entity_type->name] = $current_entity_type;
                }
            }
            $this->_updateEntityTypes($addon, $current_entity_types);
            $this->_createEntityTypes($addon, array_diff($names, array_keys($current_entity_types)));
        }
        
        $this->_application->getHelperBroker()->resetHelper('Entity_TypeImpl');
    }

    private function _createEntityTypes(Sabai_Addon $addon, array $names)
    {
        $model = $this->getModel();
        $bundles = $entity_types = array();
        foreach ($names as $name) {
            // Entity type name must start with an alphabet followed by optional alphanumeric characters
            if (!preg_match('/^[a-z][a-z0-9]*$/', $name)) {
                continue;
            }
            $info = $addon->entityGetType($name)->entityTypeGetInfo();
            $entity_type = $model->create('EntityType')
                ->markNew()
                ->set('name', $name)
                ->set('addon', $addon->getName());
            // Create entity type specific fields if any
            if (!empty($info['properties'])) {
                foreach ($info['properties'] as $property_name => $property_info) {
                    $this->_createEntityPropertyFieldConfig($entity_type, $property_name, $property_info);
                }
            }
            if (!empty($info['bundles'])) $bundles[$name] = $info['bundles'];
            $entity_types[] = $entity_type;
        }
        $model->commit();

        $this->_application->doEvent('EntityCreateEntityTypesSuccess', array($entity_types));

        // Create bundles associated with the entity type if any
        if (!empty($bundles)) {
            $new_fields = array();
            foreach ($bundles as $entity_type_name => $entity_type_bundles) {
                $this->createEntityBundles($addon, $entity_type_name, $entity_type_bundles, $new_fields);
            }
            // Update fields
            if (!empty($new_fields)) $this->createFieldStorage($new_fields);
        }
    }

    private function _updateEntityTypes(Sabai_Addon $addon, $entityTypes)
    {
        $bundles = $new_fields_by_entity_type = array();
        foreach ($entityTypes as $entity_type) {
            $info = $addon->entityGetType($entity_type->name)->entityTypeGetInfo();
            // Update property fields
            $current_fields = $this->getModel('FieldConfig')
                ->entitytypeName_is($entity_type->name)
                ->property_isNot('')
                ->fetch();
            if (!empty($info['properties'])) {
                $fields_already_installed = array();
                foreach ($current_fields as $current_field) {
                    if (!isset($info['properties'][$current_field->property])) {
                        $current_field->markRemoved();
                    } else {
                        if (isset($info['properties'][$current_field->property]['settings'])) {
                            // Only update settings
                            $current_field->settings = $info['properties'][$current_field->property]['settings'];
                        }
                        $fields_already_installed[] = $current_field->property;
                    }
                }
                // Create newly added fields
                foreach (array_diff(array_keys($info['properties']), $fields_already_installed) as $property_name) {
                    if ($new_field = $this->_createEntityPropertyFieldConfig($entity_type, $property_name, $info['properties'][$property_name])) {
                        $new_fields_by_entity_type[$entity_type->name][$new_field->name] = array($new_field, $info['properties'][$property_name]);
                    }
                }
            } else {
                foreach ($current_fields as $current_field) {
                    $current_field->markRemoved();
                }
            }
            if (!empty($info['bundles'])) $bundles[$entity_type->name] = $info['bundles'];
        }
        $this->getModel()->commit();

        $this->_application->doEvent('EntityUpdateEntityTypesSuccess', array($entityTypes));

        // Update bundles associated with the entity type if any
        if (!empty($bundles)) {
            foreach ($bundles as $entity_type_name => $entity_type_bundles) {
                $bundles[$entity_type_name] = $this->updateEntityBundles($addon, $entity_type_name, $entity_type_bundles);
            }
        }

        // Add new entity type fields to current active bundles
        if (!empty($new_fields_by_entity_type)) {
            $this->_application->Field_Types(false); // reload field types
            foreach ($new_fields_by_entity_type as $entity_type_name => $new_entity_type_fields) {
                foreach ($this->getModel('Bundle')->entitytypeName_is($entity_type_name)->fetch() as $bundle) {
                    foreach ($new_entity_type_fields as $field_name => $field_data) {
                        $this->_createEntityPropertyField($bundle, $field_data[0], $field_data[1]);
                    }
                }
            }
            $this->getModel()->commit();
        }
    }

    private function _deleteEntityTypes(Sabai_Addon $addon)
    {
        $removed_fields = array();
        $entity_types = $this->getModel('EntityType')->addon_is($addon->getName())->fetch();
        foreach ($entity_types as $entity_type) {
            $entity_type->markRemoved();
            $this->deleteEntityBundles($addon, $entity_type->name, null, $removed_fields);
        }
        $this->getModel()->commit();
        $this->_application->doEvent('EntityDeleteEntityTypesSuccess', array($entity_types));
        // Update fields
        if (!empty($removed_fields)) $this->deleteFieldStorage($removed_fields);
    }

    private function _createEntityPropertyFieldConfig(Sabai_Addon_Entity_Model_EntityType $entityType, $propertyName, array $propertyInfo)
    {
        return $this->getModel()
            ->create('FieldConfig')
            ->markNew()
            ->set('property', $propertyName)
            ->set('name', strtolower($entityType . '_' . $propertyName))
            ->set('type', $propertyInfo['type'])
            ->set('system', self::FIELD_REALM_ENTITY_TYPE_DEFAULT)
            ->set('storage', isset($propertyInfo['storage']) ? $propertyInfo['storage'] : 'sql')
            ->set('EntityType', $entityType)
            ->set('settings', (array)@$propertyInfo['settings']);
    }

    public function createEntityBundles(Sabai_Addon $addon, $entityType, array $bundles, array &$newFields = null)
    {
        if (!isset($newFields)) $newFields = array();
        $created_bundles = array();
        foreach ($bundles as $bundle_name => $bundle_info) {
            if (!$bundle = $this->_createEntityBundle($addon, $entityType, $bundle_name, $bundle_info, $newFields)) {
                unset($bundles[$bundle_name]);
                continue;
            }
            $created_bundles[$bundle_name] = $bundle;
        }
        
        if (empty($created_bundles)) return array();
        
        // Update fields
        if (!empty($newFields)) $this->createFieldStorage($newFields);
        
        if (!empty($created_bundles)) {
            $this->_application->doEvent('EntityCreateBundlesSuccess', array($entityType, $created_bundles));
        }

        return $created_bundles;
    }

    public function updateEntityBundles(Sabai_Addon $addon, $entityType, array $bundles, array &$newFields = null, array &$removedFields = null, array &$updatedFields = null)
    {
        if (!isset($newFields)) $newFields = array();
        if (!isset($removedFields)) $removedFields = array();
        if (!isset($updatedFields)) $updatedFields = array();
        $current_bundles = $new_bundles = $deleted_bundles = array();
        foreach ($this->getModel('Bundle')->entitytypeName_is($entityType)->addon_is($addon->getName())->fetch() as $current_bundle) {
            if (isset($bundles[$current_bundle->name])) {
                $this->_updateEntityBundle($current_bundle, $bundles[$current_bundle->name], $newFields, $removedFields, $updatedFields);
                $current_bundles[$current_bundle->name] = $current_bundle;
            } else {
                $this->_deleteEntityBundle($current_bundle, $removedFields);
                $deleted_bundles[$current_bundle->name] = $current_bundle;
            }
        }
        foreach (array_diff(array_keys($bundles), array_keys($current_bundles)) as $name) {
            $bundle = $this->_createEntityBundle($addon, $entityType, $name, $bundles[$name], $newFields);
            $new_bundles[$bundle->name] = $bundle;
        }

        // Update fields
        if (!empty($newFields)) {
            $this->createFieldStorage($newFields);
        }
        if (!empty($removedFields)) {
            $this->deleteFieldStorage($removedFields);
            $this->_application->doEvent('EntityDeleteFieldConfigsSuccess', array($removedFields));
        }
        if (!empty($updatedFields)) {
            $this->updateFieldStorage($updatedFields);
        }
        
        if (!empty($current_bundles)) {
            $this->_application->doEvent('EntityUpdateBundlesSuccess', array($entityType, $current_bundles));
        }
        if (!empty($deleted_bundles)) {
            $this->_application->doEvent('EntityDeleteBundlesSuccess', array($entityType, $deleted_bundles));
        }
        if (!empty($new_bundles)) {
            $this->_application->doEvent('EntityCreateBundlesSuccess', array($entityType, $new_bundles));
        }

        return $current_bundles + $new_bundles;
    }

    public function deleteEntityBundles(Sabai_Addon $addon, $entityType, array $bundles = null, array &$removedFields = null)
    {
        if (!isset($removedFields)) $removedFields = array();
        if (!isset($bundles)) {
            $bundles = $this->getModel('Bundle')
                ->entitytypeName_is($entityType)
                ->addon_is($addon->getName())
                ->fetch()
                ->with('Fields', 'FieldConfig');
        }
        $deleted_bundles = $field_names = array();
        foreach ($bundles as $bundle) {
            $this->_deleteEntityBundle($bundle, $removedFields);
            $deleted_bundles[$bundle->name] = $bundle;
            // collect field names
            foreach ($bundle->Fields as $field) {
                $field_names[$field->getFieldName()] = $field->getFieldName();
            }
        }

        // Update fields
        if (!empty($removedFields)) {
            $this->deleteFieldStorage($removedFields);
            $this->_application->doEvent('EntityDeleteFieldConfigsSuccess', array($removedFields));
        }
        
        // Delete field data of deleted bundles
        $field_names = array_diff_key($field_names, $removedFields); // exclude removed fields
        $this->_application->Entity_FieldStorageImpl('sql')
            ->entityFieldStoragePurgeValuesByBundle($entityType, array_keys($deleted_bundles), $field_names);

        if (!empty($deleted_bundles)) {
            $this->_application->doEvent('EntityDeleteBundlesSuccess', array($entityType, $deleted_bundles));
        }

        return $deleted_bundles;
    }

    private function _createEntityBundle(Sabai_Addon $addon, $entityType, $name, array $info, array &$newFields)
    {
        // Bundle name must start with an alphabet followed by optional alphanumeric characters
        if (!preg_match('/^[a-z][a-z0-9_]*[a-z0-9]$/', $name)) return;

        // Some names are reserved
        if (in_array($name, self::$_reservedBundleNames)
            || strcasecmp($name, $entityType) === 0
        ) {
            return;
        }

        // Get the model
        $model = $this->getModel();

        // Make sure content bundle with the same name does not exist
        if ($model->Bundle->entitytypeName_is($entityType)->name_is($name)->count()) return;

        // Create entity bundle
        $label = isset($info['label']) ? $info['label'] : $name;
        if (!isset($label['label_menu'])) {
            $label['label_menu'] = $label;
        }
        $_info = array_diff_key($info, array_flip(array('label', 'label_singular', 'system', 'fields', 'properties')));
        $bundle = $model->create('Bundle')
            ->markNew()
            ->set('entitytype_name', $entityType)
            ->set('system', isset($info['system']) ? !empty($info['system']) : true)
            ->set('name', $name)
            ->set('type', $info['type'])
            ->set('path', isset($info['path']) && strlen($info['path']) ? $info['path'] : '/' . str_replace('_', '/', $name))
            ->set('addon', $addon->getName())
            ->set('label', $label)
            ->set('label_singular', isset($info['label_singular']) ? $info['label_singular'] : $label)
            ->set('info', $_info);

        // Create entity type property fields
        $this->_assignEntityPropertyFields($bundle, $info);

        // Add extra fields associated with the bundle if any
        foreach ((array)@$info['fields'] as $field_name => $field_info) {
            if ($field = $this->_createEntityField($bundle, $field_name, $field_info, self::FIELD_REALM_BUNDLE_DEFAULT)) {
                if (!$field->FieldConfig->id) {
                    $newFields[$field->getFieldName()] = $field->FieldConfig;
                }
            }
        }
      
        $this->_application->doEvent('EntityCreateBundle', array($bundle, $info, &$newFields));
        
        $this->getModel()->commit();

        return $bundle;
    }
        
    public function createEntityField(Sabai_Addon_Entity_Model_Bundle $bundle, $fieldName, array $fieldInfo, $realm = self::FIELD_REALM_ALL, $overwrite = false)
    {
        $updatedFields = array();
        if (!$field = $this->_createEntityField($bundle, $fieldName, $fieldInfo, $realm, $overwrite, $updatedFields)) {
            return;
        }
        $is_new = $field->FieldConfig->id ? false : true;
        $this->getModel()->commit();

        if ($is_new) {
            $this->createFieldStorage(array($field->FieldConfig));
        } else {
            // Update field storage if schema has changed
            if (!empty($updatedFields)) {
                $this->updateFieldStorage($updatedFields);
            }
        }
        
        return $field;
    }
    
    public function createEntityPropertyField(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Model_FieldConfig $fieldConfig, array $fieldInfo, $commit = true, $overwrite = false)
    {
        if (!$field = $this->_createEntityPropertyField($bundle, $fieldConfig, $fieldInfo, $overwrite)) {
            return;
        }
        if ($commit) {
            $this->getModel()->commit();
        }
        
        return $field;
    }
    
    private function _createEntityPropertyField(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Model_FieldConfig $fieldConfig, array $fieldInfo, $overwrite = false)
    {
        if (!isset($fieldInfo['type'])) {
            return;
        }

        $field_types = $this->_application->Field_Types();
        if (!$field_type_info = @$field_types[$fieldInfo['type']]) {
            // the field type does not exist
            return;
        }
        
        $widget = isset($fieldInfo['widget']) ? $fieldInfo['widget'] : $field_types[$fieldInfo['type']]['default_widget'];
        // Fetch field
        if (!$field = $this->getModel('Field')->bundleId_is($bundle->id)->fieldconfigId_is($fieldConfig->id)->fetchOne()) {
            // Create field
            $field = $bundle->createField()->markNew()->set('FieldConfig', $fieldConfig);
        }
        // Set custom field data
        if (!empty($fieldInfo['data'])) {
            foreach ($fieldInfo['data'] as $data_k => $data_v) {
                $field->setFieldData($data_k, $data_v);
            }
        }
        // Set default field data
        if (!$field->id // newfield
            || $overwrite // overwrite?
            || !$this->_application->isAddonLoaded('FieldUI') // FieldUI add-on is not installed, meaning the field has not yet been customized
        ) {
            $field_title = isset($fieldInfo['title']) ? $fieldInfo['title'] : $field_type_info['label'];
            $field->setFieldTitle($field_title)
                ->setFieldDescription((string)@$fieldInfo['description'])
                ->setFieldWeight((int)@$fieldInfo['weight'])
                ->setFieldMaxNumItems(1)
                ->setFieldWidget($widget)
                ->setFieldWidgetSettings((array)@$fieldInfo['widget_settings'])
                ->setFieldDefaultValue(@$fieldInfo['default_value'])
                ->setFieldAdminTitle(isset($fieldInfo['admin_title']) ? $fieldInfo['admin_title'] : $field_title);
        } else {
            // Set field widget if there isn't any set and the default widget is defined
            if ($widget) {
                if (!$field->getFieldWidget()) {
                    $field->setFieldWidget($widget);
                }
            } else {
                // Widget for this field no longer exists
                if ($field->getFieldWidget()) {
                    $field->setFieldWidget(null)->setFieldWidgetSettings(array());
                }
            }
        }
        if (isset($fieldInfo['required'])) {
            $field->setFieldRequired(!empty($fieldInfo['required']));
        }
        if (isset($fieldInfo['disabled'])) {
            $field->setFieldDisabled(!empty($fieldInfo['disabled']));
        }
        
        return $field;
    }

    private function _createEntityField(Sabai_Addon_Entity_Model_Bundle $bundle, $fieldName, array $fieldInfo, $realm = self::FIELD_REALM_ALL, $overwrite = false, array &$updatedFields = array())
    {
        if (!isset($fieldInfo['type'])) {
            return;
        }

        $field_types = $this->_application->Field_Types();
        if (!$field_type_info = @$field_types[$fieldInfo['type']]) {
            // the field type does not exist
            return;
        } elseif (isset($field_type_info['entity_types'])
            && !in_array($bundle->EntityType->name, $field_type_info['entity_types'])
        ) {
            // the field type does not support the entity type of the bundle
            return;
        }

        $field_settings = isset($fieldInfo['settings']) ? $fieldInfo['settings'] : array();
        $field_schema = $this->_application->Field_TypeImpl($fieldInfo['type'])->fieldTypeGetSchema($field_settings);
        if (!is_object($fieldName)) {
            $fieldName = strtolower(trim($fieldName));
            if (strlen($fieldName) === 0) return;

            if (!$field_config = $this->getModel('FieldConfig')->name_is($fieldName)->fetchOne()) {
                $field_config = $this->getModel()
                    ->create('FieldConfig')
                    ->markNew()
                    ->set('name', $fieldName)
                    ->set('system', $realm)
                    ->set('storage', isset($fieldInfo['storage']) ? $fieldInfo['storage'] : 'sql')
                    ->set('settings', $field_settings);
                if ($realm !== self::FIELD_REALM_ALL) {
                    $field_config->set('Bundle', $bundle)->set('EntityType', $bundle->EntityType);
                }
            } else {
                $is_update = true;
            }
        } else {
            $is_update = true;
            $field_config = $fieldName;
        }
        if (!empty($is_update)) {
            if ($overwrite) {
                $field_config->settings = $field_settings;
            } else {
                $field_config->settings += $field_settings;
            }
            if ($field_config->schema !== $field_schema) {
                // Notify that field schema has changed
                $updatedFields[$field_config->storage]['old'][$field_config->name] = $field_config->schema;
                $updatedFields[$field_config->storage]['new'][$field_config->name] = $field_schema;
            }
        }
        $field_config->schema = $field_schema;
        $field_config->type = $fieldInfo['type'];
        
        $widget = isset($fieldInfo['widget']) ? (string)$fieldInfo['widget'] : $field_types[$fieldInfo['type']]['default_widget'];
        if ($widget
            && !isset($fieldInfo['max_num_items'])
            && !$this->_application->Field_WidgetImpl($widget)->fieldWidgetGetInfo('accept_multiple')
        ) {
            $fieldInfo['max_num_items'] = 1;
        }
        
        if (!$field_config->id
            || (!$field = $this->getModel('Field')->bundleId_is($bundle->id)->fieldconfigId_is($field_config->id)->fetchOne())
        ) {
            // Create field
            $field = $bundle->createField()->markNew();
        }
        // Make sure the new field config is set
        $field->FieldConfig = $field_config;
        // Update custom field data
        if (!empty($fieldInfo['data'])) {
            foreach ($fieldInfo['data'] as $data_k => $data_v) {
                $field->setFieldData($data_k, $data_v);
            }
        }
        // Update default field data
        if (!$field->id // newfield
            || $overwrite // overwrite?
            || !$this->_application->isAddonLoaded('FieldUI') // FieldUI add-on is not installed, meaning the field has not yet been customized
        ) {
            $field_title = isset($fieldInfo['title']) ? $fieldInfo['title'] : $field_type_info['label'];
            $field->setFieldTitle($field_title)
                ->setFieldDescription((string)@$fieldInfo['description'])
                ->setFieldWeight((int)@$fieldInfo['weight'])
                ->setFieldMaxNumItems(isset($fieldInfo['max_num_items']) ? $fieldInfo['max_num_items'] : 0)
                ->setFieldWidget($widget)
                ->setFieldWidgetSettings((array)@$fieldInfo['widget_settings'])
                ->setFieldDefaultValue(@$fieldInfo['default_value'])
                ->setFieldAdminTitle(isset($fieldInfo['admin_title']) ? $fieldInfo['admin_title'] : $field_title)
                ->setFieldRequired(!empty($fieldInfo['required']))
                ->setFieldDisabled(!empty($fieldInfo['disabled']));
        } else {
            // Set field widget if there isn't any set and the default widget is defined
            if ($widget) {
                if (!$field->getFieldWidget()) {
                    $field->setFieldWidget($widget);
                }
            } else {
                // this field does not have any available widget
                $field->setFieldWidget('');
            }
        }

        return $field;
    }
    
    private function _assignEntityPropertyFields(Sabai_Addon_Entity_Model_Bundle $bundle, array $info, $overwrite = false)
    {
        $property_fields = $this->getModel('FieldConfig')->entitytypeName_is($bundle->entitytype_name)->bundleId_is(0)->fetch();
        if (count($property_fields)) {
            $entity_type_info = $this->_application->Entity_TypeImpl($bundle->entitytype_name, false)->entityTypeGetInfo();
            foreach ($property_fields as $property_field) {
                if (!isset($entity_type_info['properties'][$property_field->property])) {
                    // Delete stale data
                    $property_field->markRemoved()->commit();
                    continue;
                }
                $property_field_settings = $entity_type_info['properties'][$property_field->property];
                // Each bunde can set custom field settings but not overwrite the default
                if (!empty($info['properties'][$property_field->property])) {
                    $property_field_settings += $info['properties'][$property_field->property];
                    // Except for title which can be overwritten
                    if (isset($info['properties'][$property_field->property]['title'])) {
                        $property_field_settings['title'] = $info['properties'][$property_field->property]['title'];
                    }
                }
                $this->_createEntityPropertyField($bundle, $property_field, $property_field_settings, $overwrite);
            }
        }
    }

    private function _updateEntityBundle(Sabai_Addon_Entity_Model_Bundle $bundle, array $info, array &$newFields, array &$removedFields, array &$updatedFields)
    {
        if (!isset($info['label_menu'])) {
            $info['label_menu'] = $info['label'];
        }
        $_info = array_diff_key($info, array_flip(array('label', 'label_singular', 'system', 'fields', 'properties', 'path', 'type')));
        $bundle->set('label', $info['label'])
            ->set('label_singular', isset($info['label_singular']) ? $info['label_singular'] : $info['label'])
            ->set('info', $_info);
        // Update info
        $_info = array_diff_key($info, array_flip(array('label', 'label_singular', 'system', 'fields', 'properties', 'path', 'type')));
        $bundle->set('info', $_info);
        // Has path/type been changed?
        if ($bundle->getPath() != $info['path']) {
            $bundle->set('path', $info['path']);
        }
        // Update type, for backward compat
        $bundle->set('type', $info['type']);
        // Update entity type property fields
        $this->_assignEntityPropertyFields($bundle, $info);
        // Update bundle specific fields
        $current_fields = $this->getModel('FieldConfig')
            ->system_is(self::FIELD_REALM_BUNDLE_DEFAULT)
            ->bundleId_is($bundle->id)
            ->fetch()
            ->with('Fields');
        if (!empty($info['fields'])) {
            $fields_already_installed = array();
            foreach ($current_fields as $current_field) {
                if (!isset($info['fields'][$current_field->name])) {
                    $current_field->markRemoved();
                    $removedFields[$current_field->name] = $current_field;

                    continue;
                }
                if (!$this->_createEntityField($bundle, $current_field, $info['fields'][$current_field->name], self::FIELD_REALM_ALL, false, $updatedFields)) {
                    // Remove field if update failed
                    $current_field->markRemoved();
                    $removedFields[$current_field->name] = $current_field;

                    continue;
                }
                
                $fields_already_installed[] = $current_field->name;
            }
            // Create newly added fields
            foreach (array_diff(array_keys($info['fields']), $fields_already_installed) as $field_name) {
                if ($field = $this->_createEntityField($bundle, $field_name, $info['fields'][$field_name], self::FIELD_REALM_BUNDLE_DEFAULT)) {
                    if (!$field->FieldConfig->id) {
                        $newFields[$field->FieldConfig->name] = $field->FieldConfig;
                    }
                }
            }
        } else {
            foreach ($current_fields as $current_field) {
                $current_field->markRemoved();
                $removedFields[$current_field->name] = $current_field;
            }
        }        
        
        $this->_application->doEvent('EntityUpdateBundle', array($bundle, $info, &$newFields, &$removedFields));

        $this->getModel()->commit();
        
        return $bundle;
    }

    private function _deleteEntityBundle($bundle, array &$removedFields)
    {
        $bundle->markRemoved();

        $fields = $this->getModel('FieldConfig')
            ->bundleId_is($bundle->id)
            ->fetch()
            ->with('Fields');
        foreach ($fields as $field) {
            $field->markRemoved();
            $removedFields[$field->name] = $field;
        }
        
        $this->_application->doEvent('EntityDeleteBundle', array($bundle, &$removedFields));
        
        $this->getModel()->commit();

        return $bundle;
    }

    public function createEntity($bundleName, array $values, array $extraEventArgs = array(), Sabai_UserIdentity $identity = null)
    {     
        if ($bundleName instanceof Sabai_Addon_Entity_Model_Bundle) {
            $bundle = $bundleName;
        } else {
            if (!$bundle = $this->_application->Entity_Bundle($bundleName)) {
                throw new Sabai_RuntimeException('Invalid bundle: ' . $bundleName);
            }
        }
        // Notify that an entity is being created
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, &$values, &$extraEventArgs), 'BeforeCreate');
        // Extract field values for saving
        $values = $this->_extractFieldValues($bundle, $fields = $bundle->Fields->with('FieldConfig'), $values);
        // Notify that an entity is being created, with extracted fields values
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, &$values, &$extraEventArgs), 'Create');
        // Save entity
        $entity = $this->_saveEntity($bundle, $values, $fields, null, $identity);
        // Notify that an entity has been created
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $entity, $values, &$extraEventArgs), 'AfterCreate');
        // Load entity fields
        $this->_application->Entity_LoadFields($entity, null, null, true);
        // Notify that an entity has been saved
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $entity, $values, $extraEventArgs), 'Create', 'Success');

        return $entity;
    }

    public function updateEntity(Sabai_Addon_Entity_Entity $entity, array $values, array $extraEventArgs = array())
    {
        if (!$bundle = $this->_application->Entity_Bundle($entity)) {
            throw new Sabai_RuntimeException('Invalid bundle.');
        }
        if ($entity->isFromCache()) {
            // this entity was loaded from cache, so load again from storage to make sure the current values are available
            if (!$entity = $this->_application->Entity_TypeImpl($bundle->entitytype_name)->entityTypeGetEntityById($entity->getId())) {
                throw new Sabai_RuntimeException('Invalid entity.');
            }
        }
        // Make sure all the fields are loaded
        $this->_application->Entity_LoadFields($entity);
        // Notify that an entity is being updated
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $entity, &$values, &$extraEventArgs), 'BeforeUpdate');
        // Extract modified field values for saving
        $values = $this->_extractFieldValues($bundle, $fields = $bundle->Fields->with('FieldConfig'), $values, $entity);
        // Notify that an entity is being updated, with extracted field values
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $entity, &$values, &$extraEventArgs), 'Update');
        // Save entity
        $updated_entity = $this->_saveEntity($bundle, $values, $fields, $entity);
        // Notify that an entity has been updated
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $updated_entity, $entity, $values, &$extraEventArgs), 'AfterUpdate');
        // Clear cached entity fields
        $this->_application->Entity_FieldCacheImpl()->entityFieldCacheRemove($updated_entity->getType(), array($updated_entity->getId()));
        // Field values may have changed, so reload entity
        $this->_application->Entity_LoadFields($updated_entity, null, null, true);
        // Notify that an entity has been saved
        $this->_invokeEntityEvents($bundle->entitytype_name, $bundle->type, array($bundle, $updated_entity, $entity, $values, $extraEventArgs), 'Update', 'Success');
        
        return $updated_entity;
    }
    
    private function _extractFieldValues(Sabai_Addon_Entity_Model_Bundle $bundle, $fields, array $fieldValues, Sabai_Addon_Entity_IEntity $entity = null)
    {     
        // Extract field values to save
        $ret = array();
        foreach ($fields as $field) {
            if (null === $field_value = @$fieldValues[$field->getFieldName()]) {
                continue;
            }

            if (!$field->isPropertyField()) {
                // Always pass value as array
                if (!is_array($field_value) || !array_key_exists(0, $field_value)) {
                    $field_value = array($field_value);
                }
                // Get current value if this is an update
                $current_field_value = isset($entity) ? $entity->getFieldValue($field->getFieldName()) : null;
                // Let the field type addon for the field to work on values before saving to the storage
                $field_value = $this->_application->Field_TypeImpl($field->getFieldType())
                    ->fieldTypeOnSave($field, $field_value, $current_field_value);
                if (!is_array($field_value)) {
                    continue;
                }
                // Is the maximum number of items for this field limited?
                $max_num_values = $field->getFieldMaxNumItems();
                if (!is_numeric($max_num_values)) {
                    $max_num_values = 10; // defaults to 10
                }
                if ($max_num_values && count($field_value) > $max_num_values) {
                    $field_value = array_slice($field_value, 0, $max_num_values);
                }
                // If this is an update, make sure that the new value is different from the existing one
                if (isset($entity)) {
                    $current_field_value = @$entity->getFieldValue($field->getFieldName());
                    if ($current_field_value !== null
                        && !$this->_application->Field_TypeImpl($field->getFieldType())->fieldTypeIsModified($field, $field_value, $current_field_value)
                    ) {
                        // the value hasn't changed, so skip this field
                        continue;
                    }
                }
                $ret[$field->getFieldName()] = $field_value;
            } else {
                if (is_array($field_value) && array_key_exists(0, $field_value)) {
                    $field_value = $field_value[0];
                }
                // If this is an update, make sure that the new value is different from the existing one
                if (isset($entity)) {
                    if (!$entity->isPropertyModified($field->getFieldName(), $field_value)) {
                        // the value hasn't changed, so skip this field
                        continue;
                    }
                }
                $ret[$field->getFieldName()] = $field_value;
            }
        }
        
        return $ret;
    }

    private function _saveEntity(Sabai_Addon_Entity_Model_Bundle $bundle, array $fieldValues, $fields = null, Sabai_Addon_Entity_IEntity $entity = null, Sabai_UserIdentity $identity = null)
    {     
        // Extract field values to save
        $field_values_by_storage = $properties = array();
        if (!isset($fields)) {
            $fields = $bundle->Fields->with('FieldConfig');
        }
        foreach ($fields as $field) {
            if (!isset($fieldValues[$field->getFieldName()])) {
                continue;
            }

            if (!$field->isPropertyField()) {
                $field_values_by_storage[$field->getFieldStorage()][$field->getFieldName()] = $fieldValues[$field->getFieldName()];
            } else {
                $properties[$field->getFieldName()] = $fieldValues[$field->getFieldName()];
            }
        }

        // Save entity
        $entity_type_impl = $this->_application->Entity_TypeImpl($bundle->entitytype_name);
        if (!isset($entity)) {
            if (!isset($identity)) {
                $identity = $this->_application->getUser()->getIdentity();
            }
            $ret = $entity_type_impl->entityTypeCreateEntity($bundle, $properties, $identity);
        } else {
            $ret = $entity_type_impl->entityTypeUpdateEntity($entity, $bundle, $properties);
        }

        // Save fields
        foreach (array_keys($field_values_by_storage) as $field_storage) {
            $this->_application->Entity_FieldStorageImpl($field_storage)
                ->entityFieldStorageSaveValues($ret, $field_values_by_storage[$field_storage]);
        }
        
        return $ret;
    }

    /**
     * Delete entities
     * @param type $entityType
     * @param array $entities An array of Sabai_Addon_Entity_IEntity objects indexed by entity IDs
     */
    public function deleteEntities($entityType, array $entities, array $extraEventArgs = array())
    {
        if (empty($entities)) return;
        
        // Load field values from storage so that all field values can be accessed by other addons upon delete event
        $this->_application->Entity_LoadFields($entityType, $entities, null, true, false);
        
        $this->_application->Entity_TypeImpl($entityType)->entityTypeDeleteEntities($entities);

        // Delete fields
        $entities_by_bundle = array();
        foreach ($entities as $entity) {
            $entities_by_bundle[$entity->getBundleName()][$entity->getId()] = $entity;
        }
        $bundles = $this->_application->Entity_BundleCollection(array_keys($entities_by_bundle))->with('Fields', 'FieldConfig');
        $fields_by_storage = $bundles_arr = array();
        foreach ($bundles as $bundle) {
            foreach ($bundle->Fields as $field) {
                $fields_by_storage[$field->getFieldStorage()][$bundle->name][$field->getFieldName()] = $field->getFieldType();
            }
            $bundles_arr[$bundle->name] = $bundle;
        }
        foreach ($fields_by_storage as $field_storage => $bundle_fields) {
            foreach ($bundle_fields as $bundle_name => $fields) {
                $this->_application->Entity_FieldStorageImpl($field_storage)
                    ->entityFieldStoragePurgeValues($entityType, array_keys($entities_by_bundle[$bundle_name]), array_keys($fields));
            }
        }

        // Clear cached entity fields
        $this->_application->Entity_FieldCacheImpl()->entityFieldCacheRemove($entityType, array_keys($entities));

        // Notify entities have been deleted
        foreach ($entities as $entity) {
            $bundle = $bundles_arr[$entity->getBundleName()];
            $this->_invokeEntityEvents($entityType, $bundle->type, array($bundle, $entity, array_keys($entities), $extraEventArgs), 'Delete', 'Success');
        }
        
        foreach ($entities_by_bundle as $bundle_name => $entities) {
            $bundle = $bundles_arr[$bundle_name];
            $this->_invokeEntityEvents($entityType, $bundle->type, array($bundle, $entities, $extraEventArgs), 'BulkDelete', 'Success');
        }
    }
    
    private function _invokeEntityEvents($entityType, $bundleType, array $params, $prefix, $suffix = '')
    {
        $prefix = 'Entity' . $prefix;
        $suffix = 'Entity' . $suffix;
        $bundle_type = $this->_application->Camelize($bundleType);
        $this->_application->doEvent($prefix . $suffix, $params)
            ->doEvent($prefix . ucfirst($entityType) . $suffix, $params)
            ->doEvent($prefix . ucfirst($entityType) . $bundle_type . $suffix, $params);
    }

    public function fetchEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 0, $offset = 0, $loadEntityFields = true)
    {
        // It is not possible to fetch entities from multiple field storages
        $field_storage = $this->_config['field_storage'];

        $entities = $this->_application->Entity_FieldStorageImpl($field_storage)
            ->entityFieldStorageQuery($entityType, $fieldQuery, $limit, $offset);
        if (empty($entities)) {
            return array();
        }

        foreach ($this->_application->Entity_TypeImpl($entityType)->entityTypeGetEntitiesByIds(array_keys($entities)) as $entity) {
            if (count($entities[$entity->getId()]) > 1) {
                // Set extra fields queried as entity data
                $entity->data = $entities[$entity->getId()];
            }
            $entities[$entity->getId()] = $entity;
        }
        if ($loadEntityFields) {
            $this->_application->Entity_LoadFields($entityType, $entities, $field_storage);
        }

        return $entities;
    }

    public function countEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery)
    {
        // It is not possible to fetch entities from multiple field storages
        $field_storage = $this->getConfig('field_storage');

        return $this->_application->Entity_FieldStorageImpl($field_storage)
            ->entityFieldStorageQueryCount($entityType, $fieldQuery);
    }

    public function paginateEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 20, $loadEntityFields = true)
    {
        return new SabaiFramework_Paginator_Custom(
            array($this, 'countEntities'),
            array($this, 'fetchEntities'),
            $limit,
            array($loadEntityFields),
            array($entityType, $fieldQuery),
            array()
        );
    }

    public function createFieldStorage(array $fieldConfigs)
    {
        $fields = array();
        // Fetch new schema for each field
        foreach ($fieldConfigs as $field_config) {
            $fields[$field_config->storage][$field_config->name] = $field_config->schema;
        }                                               
        // Create storage
        foreach ($fields as $storage => $_fields) {
            $this->_application->Entity_FieldStorageImpl($storage)->entityFieldStorageCreate($_fields);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }
    
    public function deleteFieldStorage(array $fieldConfigs)
    {
        $fields = array();
        // Fetch current schema for each field
        foreach ($fieldConfigs as $field_config) {
            $fields[$field_config->storage][$field_config->name] = $field_config->schema;
        }                                         
        // Delete storage
        foreach ($fields as $storage => $_fields) {
            $this->_application->Entity_FieldStorageImpl($storage)->entityFieldStorageDelete($_fields);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }
    
    public function updateFieldStorage(array $updatedFields)
    {
        foreach ($updatedFields as $field_storage => $field_schema) {
            $this->_application->Entity_FieldStorageImpl($field_storage)->entityFieldStorageUpdate($field_schema['new'], $field_schema['old']);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }

    public function onEntityIFieldStoragesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->entityGetFieldStorageNames()) return;

        $this->_createFieldStorageModels($addon, $names);
        $this->_application->getHelperBroker()->resetHelper('Entity_FieldStorageImpl');
    }

    public function onEntityIFieldStoragesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteFieldStorageModels($addon->getName());
        $this->_application->getHelperBroker()->resetHelper('Entity_FieldStorageImpl');
    }

    public function onEntityIFieldStoragesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->entityGetFieldStorageNames()) {
            $this->_deleteFieldStorageModels($addon->getName());
        } else {
            $already_installed = array();
            foreach ($this->getModel('FieldStorage')->addon_is($addon->getName())->fetch() as $current) {
                if (!in_array($current->name, $names)) {
                    $current->markRemoved();
                } else {
                    $already_installed[] = $current->name;
                }
            }
            $this->_createFieldStorageModels($addon, array_diff($names, $already_installed));
        }
        $this->_application->getHelperBroker()->resetHelper('Entity_FieldStorageImpl');
    }

    private function _createFieldStorageModels(Sabai_Addon $addon, $names)
    {
        foreach ($names as $name) {
            $entity = $this->getModel()->create('FieldStorage')->markNew();
            $entity->name = $name;
            $entity->addon = $addon->getName();
        }
        $this->getModel()->commit();
    }

    private function _deleteFieldStorageModels($addon)
    {
        foreach ($this->getModel('FieldStorage')->addon_is($addon->getName())->fetch() as $entity) {
            $entity->markRemoved();
        }
        $this->getModel()->commit();
    }

    public function getDefaultConfig()
    {
        return array(
            'field_storage' => 'sql',
        );
    }

    /* Start implementation of Sabai_Addon_Entity_IFieldCache */

    public function entityFieldCacheSave($entityType, array $entities)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in(array_keys($entities));
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
        foreach ($entities as $entity_id => $entity) {
            $fieldcache = $model->create('FieldCache');
            $fieldcache->entity_id = $entity_id;
            $fieldcache->entitytype_name = $entityType;
            $fieldcache->fields = array($entity->getFieldValues(), $entity->getFieldTypes());
            $fieldcache->bundle_id = $this->_application->Entity_Bundle($entity)->id;
            $fieldcache->markNew();
        }
        $model->commit();
    }

    public function entityFieldCacheLoad($entityType, array $entities)
    {
        $ret = array();
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in(array_keys($entities));
        $rs = $model->getGateway('FieldCache')->selectByCriteria($criteria, array('fieldcache_entity_id', 'fieldcache_fields'));
        while ($row = $rs->fetchRow()) {
            $fields = unserialize($row[1]);
            if (!isset($fields[1])) {
                continue; // older version don't have field types cached so skip it to force reload cache
            }
            $entities[$row[0]]->initFields($fields[0], $fields[1]);
            $ret[] = $row[0];
        }
        return $ret;
    }

    public function entityFieldCacheRemove($entityType, array $entityIds)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in($entityIds);
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
    }

    public function entityFieldCacheClean($entityType = null)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache');
        if (isset($entityType)) {
            $criteria->entitytypeName_is($entityType);
        }
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
    }

    /* End implementation of Sabai_Addon_Entity_IFieldCache */

    public function onFieldTypeDeleted($fieldType)
    {
        $field_configs = array();
        foreach ($this->getModel('FieldConfig')->type_is($fieldType->name)->fetch()->with('Fields') as $field_config) {
            $field_config->markRemoved();
            $field_configs[$field_config->name] = $field_config;
        }
        $this->getModel()->commit();
        $this->deleteFieldStorage($field_configs);
        $this->_application->doEvent('EntityDeleteFieldConfigsSuccess', array($field_configs));
    }
    
    public function uninstall(ArrayObject $log)
    {
        // Remove tables created by custom fields
        
        $fields = array();
        foreach ($this->getModel('FieldConfig')->fetch() as $field) {
            if ($field->property) continue;
            
            $fields[] = $field;
        }

        // Remove field tables
        if (!empty($fields)) {
            $this->deleteFieldStorage($fields);
        }
        
        parent::uninstall($log);
    }
    
    public function onSystemClearCache()
    {
        // Clear all field cache
        $this->_application->Entity_FieldCacheImpl()->entityFieldCacheClean();
    }
}
