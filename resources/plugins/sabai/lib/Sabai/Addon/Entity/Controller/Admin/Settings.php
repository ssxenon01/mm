<?php
class Sabai_Addon_Entity_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        // Init form
        $form = array(
            '#bundle' => $context->bundle,
            'field_configs' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'name' => 'Name',
                    'property' => 'Property',
                    'entity_type' => 'Entity Type',
                    'bundle' => 'Bundle',
                    'fields' => 'Fields',
                    'table_fields' => 'Table Fields',
                    'table_indexes' => 'Table Indexes',
                    'table_constraints' => 'Table Constraints',
                ),
                '#options' => array(),
                '#options_disabled' => array(),
                '#multiple' => true,
            ),
        );
        
        // Set submit buttons
        $this->_submitButtons = array(array('#value' => __('Delete', 'sabai')));
        
        $schema = $this->getDBSchema();
        foreach ($this->getModel('FieldConfig')->fetch(0, 0, 'name', 'ASC')->with('Fields', 'Bundle')->with('Bundle') as $field_config) {
            $fields = array();
            foreach ($field_config->Fields as $field) {
                $fields[] = sprintf('%s (%s)', $field->getFieldTitle(), $field->Bundle->name);
            }
            $field_schema = $this->Field_TypeImpl($field_config->type)->fieldTypeGetSchema(array());
            $table_fields = $table_indexes = $table_constraints = '';
            if ($field_schema) {
                $table_name = Sabai_Addon_Entity_FieldStorage_Sql::getFieldDataTableName($this->getDB()->getResourcePrefix(), $field_config->name);
                $table_fields = serialize($schema->listTableFields($table_name));
                $table_indexes = serialize($schema->listTableIndexes($table_name));
                $table_constraints = serialize($schema->listTableConstraints($table_name));
            }
            $form['field_configs']['#options'][$field_config->id] = array(
                'name' => $field_config->name,
                'property' => $field_config->property,
                'entity_type' => $field_config->entitytype_name,
                'bundle' => $field_config->Bundle ? sprintf('%s (%s)', $field_config->Bundle->name, $field_config->Bundle->type) : '',
                'fields' => implode(', ', $fields),
                'table_fields' => $table_fields,
                'table_indexes' => $table_indexes,
                'table_constraints' => $table_constraints,
            );
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['field_configs'])) {
            $removed_fields = array();
            foreach ($this->getModel('FieldConfig')->id_in($form->values['field_configs'])->fetch()->with('Fields') as $field) {
                $field->markRemoved();
                $removed_fields[] = $field;
            }
            // Commit and delete field storage of removed fields
            if (!empty($removed_fields)) {
                $this->getModel()->commit();
                $this->getAddon()->deleteFieldStorage($removed_fields);
                $this->_application->doEvent('EntityDeleteFieldConfigsSuccess', array($removed_fields));
            }
        }
        $context->setSuccess();
    }
}