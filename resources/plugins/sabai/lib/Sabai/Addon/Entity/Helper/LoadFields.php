<?php
class Sabai_Addon_Entity_Helper_LoadFields extends Sabai_Helper
{    
    public function help(Sabai $application, $entityType, array $entities = null, $fieldStorage = null, $force = false, $cache = true)
    {
        if ($entityType instanceof Sabai_Addon_Entity_IEntity) {
            if ($entityType->isFieldsLoaded() && !$force) {
                return;
            }
            $entities = array($entityType->getId() => $entityType);
            $entityType = $entityType->getType();
        }
        if (!$force) {
            $entities_loaded = $application->Entity_FieldCacheImpl()->entityFieldCacheLoad($entityType, $entities);
            $entities_to_load = array_diff_key($entities, array_flip($entities_loaded));
        } else {
            $entities_to_load = $entities;
        }
        if (!empty($entities_to_load)) {
            $this->_loadEntityFields($application, $entityType, $entities_to_load, $fieldStorage);
            if ($cache) {
                $application->Entity_FieldCacheImpl()->entityFieldCacheSave($entityType, $entities_to_load);
            }
        }
    }

    protected function _loadEntityFields(Sabai $application, $entityType, array $entities, $fieldStorage = null)
    {
        $entities_by_bundle = $field_values_by_bundle = $field_types_by_bundle = array();
        foreach ($entities as $entity_id => $entity) {     
            $entities_by_bundle[$entity->getBundleName()][$entity_id] = $entity;
        }
        $bundles = $application->Entity_BundleCollection(array_keys($entities_by_bundle))->with('Fields', 'FieldConfig');
        if (isset($fieldStorage)) {
            // Single field storage, probably called via fetchEntities()
            foreach ($bundles as $bundle) {
                $fields = array();
                foreach ($bundle->Fields as $field) {
                    if (!$field->FieldConfig) continue;
                    
                    $fields[$field->getFieldName()] = $field->getFieldType();
                }
                $field_values_by_bundle[$bundle->name] = $application->Entity_FieldStorageImpl($fieldStorage)
                    ->entityFieldStorageFetchValues($entityType, array_keys($entities_by_bundle[$bundle->name]), array_keys($fields));
                $field_types_by_bundle[$bundle->name] = $fields;
            }
        } else {
            $fields_by_storage = array();
            foreach ($bundles as $bundle) {
                foreach ($bundle->Fields as $field) {
                    if (!$field->FieldConfig) continue;
                    
                    $fields_by_storage[$field->getFieldStorage()][$bundle->name][$field->getFieldName()] = $field->getFieldType();
                }
            }
            foreach ($fields_by_storage as $field_storage => $bundle_fields) {
                foreach ($bundle_fields as $bundle_name => $fields) {
                    $field_values_by_bundle[$bundle_name] = $application->Entity_FieldStorageImpl($field_storage)
                        ->entityFieldStorageFetchValues($entityType, array_keys($entities_by_bundle[$bundle_name]), array_keys($fields));
                    $field_types_by_bundle[$bundle_name] = $fields;
                }
            }
        }
        // Load field values
        foreach ($bundles as $bundle) {
            foreach ($entities_by_bundle[$bundle->name] as $entity_id => $entity) {
                $entity_field_values = array();
                foreach ($bundle->Fields as $field) {
                    if ($field->isPropertyField()) continue; // do not call fieldTypeOnLoad() on property fields
                    
                    // Check whether or not the value for this field is cacheable
                    $ifield_type = $application->Field_TypeImpl($field->getFieldType());
                    if (false === @$ifield_type->fieldTypeGetInfo('cacheable')) continue;

                    $value = @$field_values_by_bundle[$bundle->name][$entity_id][$field->getFieldName()];
                    if (null !== $value) { // value may be null if no matching field entries
                        // Let the field type addon for each field to work on values on load
                        $ifield_type->fieldTypeOnLoad($field, $value);
                        $entity_field_values[$field->getFieldName()] = $value;
                    } else {
                        $entity_field_values[$field->getFieldName()] = null;
                    }
                }
                // Allow other add-ons to filter entity field values 
                $entity_field_values = $application->Filter('EntityLoadFieldValues', $entity_field_values, array($entity, $bundle));
                $entity->initFields($entity_field_values, $field_types_by_bundle[$bundle->name]);
            }
        }
    }
}
