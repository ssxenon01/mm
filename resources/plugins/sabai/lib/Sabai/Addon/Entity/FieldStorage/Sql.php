<?php
class Sabai_Addon_Entity_FieldStorage_Sql extends Sabai_Addon_Entity_FieldStorage_AbstractFieldStorage
{
    private $_queries = array();

    protected function _entityFieldStorageGetInfo()
    {
        return array('label' => 'SQL');
    }

    public function entityFieldStorageSaveValues(Sabai_Addon_Entity_IEntity $entity, array $fieldValues)
    {
        $db = $this->_application->getDB();
        $db->begin();
        $column_types = $this->_application->Entity_FieldColumnTypes();
        foreach ($fieldValues as $field_name => $field_values) {
            
            // Skip if no schema defined for this field
            if (empty($column_types[$field_name])) continue;

            // Delete all current values of the entity
            try {
                $db->exec(sprintf(
                    'DELETE FROM %s WHERE entity_type = %s AND entity_id = %d',
                    self::getFieldDataTableName($db->getResourcePrefix(), $field_name),
                    $db->escapeString($entity->getType()),
                    $entity->getId()
                ));
            } catch (SabaiFramework_Exception $e) {
                $db->rollback();
                throw $e;
            }

            // Insert values
            foreach ($field_values as $weight => $field_value) {
                if (!is_array($field_value)) {
                    continue;
                }
                $values = array();
                foreach (array_intersect_key($field_value, $column_types[$field_name]) as $column => $value) {
                    $values[$column] = self::escapeFieldValue($db, $value, $column_types[$field_name][$column]);
                }
                try {
                    $sql = sprintf(
                        'INSERT INTO %s (entity_type, bundle_id, entity_id, weight%s) VALUES (%s, %d, %d, %d%s)',
                        self::getFieldDataTableName($db->getResourcePrefix(), $field_name),
                        empty($values) ? '' : ', ' . implode(', ', array_keys($values)),
                        $db->escapeString($entity->getType()),
                        $this->_application->Entity_Bundle($entity)->id,
                        $entity->getId(),
                        $weight,
                        empty($values) ? '' : ', ' . implode(', ', $values)
                    );
                    $db->exec($sql);
                } catch (SabaiFramework_Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            }
        }
        $db->commit();
    }

    public function entityFieldStorageFetchValues($entityType, array $entityIds, array $fields)
    {
        $values = array();
        $db = $this->_application->getDB();
        $column_types = $this->_application->Entity_FieldColumnTypes();
        foreach ($fields as $field_name) {
            // Skip if no schema defined for this field
            if (empty($column_types[$field_name])) continue;

            $rs = $db->query(sprintf(
                'SELECT entity_id, %s FROM %s WHERE entity_type = %s AND entity_id IN (%s) ORDER BY weight ASC',
                implode(', ', array_keys($column_types[$field_name])),
                self::getFieldDataTableName($db->getResourcePrefix(), $field_name),
                $db->escapeString($entityType),
                implode(',', array_map('intval', $entityIds))
            ));
            while ($row = $rs->fetchAssoc()) {
                $entity_id = $row['entity_id'];
                unset($row['entity_id']);
                foreach ($column_types[$field_name] as $column => $column_type) {
                    switch ($column_type) {
                        case Sabai_Addon_Field::COLUMN_TYPE_INTEGER:
                            $row[$column] = intval($row[$column]);
                            break;
                        case Sabai_Addon_Field::COLUMN_TYPE_FLOAT:
                        case Sabai_Addon_Field::COLUMN_TYPE_DECIMAL:
                            $row[$column] = str_replace(',', '.', floatval($row[$column]));
                            break;
                        case Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN:
                            $row[$column] = (bool)$row[$column];
                            break;
                    }
                }
                $values[$entity_id][$field_name][] = $row;
            }
        }

        return $values;
    }

    public function entityFieldStoragePurgeValues($entityType, array $entityIds, array $fields)
    {
        $db = $this->_application->getDB();
        $db->begin();
        $column_types = $this->_application->Entity_FieldColumnTypes();
        foreach ($fields as $field_name) {
            // Skip if no schema defined for this field
            if (empty($column_types[$field_name])) continue;

            // Delete all values of the entity
            try {
                $db->exec(sprintf(
                    'DELETE FROM %s WHERE entity_type = %s AND entity_id IN (%s)',
                    self::getFieldDataTableName($db->getResourcePrefix(), $field_name),
                    $db->escapeString($entityType),
                    implode(',', array_map('intval', $entityIds))
                ));
            } catch (SabaiFramework_Exception $e) {
                $db->rollback();
                $this->_application->LogError($e);
            }
        }
        $db->commit();
    }
    
    public function entityFieldStoragePurgeValuesByBundle($entityType, array $bundleIds, array $fields)
    {
        $db = $this->_application->getDB();
        $db->begin();
        $column_types = $this->_application->Entity_FieldColumnTypes();
        foreach ($fields as $field_name) {
            // Skip if no schema defined for this field
            if (empty($column_types[$field_name])) continue;

            // Delete all values of the entity
            try {
                $db->exec(sprintf(
                    'DELETE FROM %s WHERE entity_type = %s AND bundle_id IN (%s)',
                    self::getFieldDataTableName($db->getResourcePrefix(), $field_name),
                    $db->escapeString($entityType),
                    implode(',', array_map('intval', $bundleIds))
                ));
            } catch (SabaiFramework_Exception $e) {
                $db->rollback();
                $this->_application->LogError($e);
            }
        }
        $db->commit();
    }

    public function entityFieldStorageCreate(array $fields)
    {
        if (!$schema = $this->_getSchema($fields)) {
            return;
        }
        $this->_application->getDBSchema()->create($schema);
    }
    
    public function entityFieldStorageUpdate(array $fields, array $oldFields)
    {
        $schema = !empty($fields) ? $this->_getSchema($fields) : false;
        $old_schema = isset($oldFields) ? $this->_getSchema($oldFields) : false;
        if ($schema && $old_schema && $schema !== $old_schema) {
            $this->_application->getDBSchema()->update($schema, $old_schema);
        }
    }
    
    public function entityFieldStorageDelete(array $fields)
    {
        if (!$schema = $this->_getSchema($fields)) {
            return;
        }
        try {
            $this->_application->getDBSchema()->drop($schema);
        } catch (SabaiFramework_DB_SchemaException $e) {
            $this->_application->LogError($e);
        }
    }

    public function entityFieldStorageQueryCount($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery)
    {
        return $this->_createQuery($entityType, $fieldQuery)->getEntityCount();
    }

    /**
     * Fetch entity IDs by criteria
     * @param Sabai_Addon_Entity_FieldQuery $fieldQuery
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function entityFieldStorageQuery($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 20, $offset = 0)
    {
        return $this->_createQuery($entityType, $fieldQuery)->getEntityIds($limit, $offset);
    }

    private function _createQuery($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery)
    {
        $object_hash = spl_object_hash($fieldQuery);
        if (!isset($this->_queries[$object_hash])) {
            $this->_queries[$object_hash] = new Sabai_Addon_Entity_FieldStorage_SqlQuery(
                $this->_application->Entity_Type($entityType),
                $this->_application->Entity_FieldColumnTypes(),
                $this->_application->getDB(),
                $fieldQuery
            );
        }

        return $this->_queries[$object_hash];
    }

    private function _getSchema(array $fields)
    {
        $default_columns = array(
            'entity_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'unsigned' => true,
                'length' => 40,
                'was' => 'entity_type',
                'default' => '',
            ),
            'bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'notnull' => true,
                'unsigned' => true,
                'was' => 'bundle_id',
                'default' => 0,
            ),
            'entity_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'notnull' => true,
                'unsigned' => true,
                'was' => 'entity_id',
                'default' => 0,
            ),
            //'revision_id' => array(
            //    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
            //    'notnull' => true,
            //    'unsigned' => true,
            //    'was' => 'revision_id',
            //    'default' => 0,
            //),
            'weight' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'notnull' => true,
                'unsigned' => true,
                'was' => 'weight',
                'default' => 0,
            ),
        );
        $default_indexes = array(
            'primary' => array(
                'fields' => array(
                    'entity_type' => array('sorting' => 'ascending'),
                    'entity_id' => array('sorting' => 'ascending'),
                    'weight' => array('sorting' => 'ascending'),
                ),
                'primary' => true,
                'was' => 'primary',
            ),
            'bundle_id' => array(
                'fields' => array('bundle_id' => array('sorting' => 'ascending')),
                'was' => 'bundle_id',
            ),
            'entity_id' => array(
                'fields' => array('entity_id' => array('sorting' => 'ascending')),
                'was' => 'entity_id',
            ),
            'weight' => array(
                'fields' => array('weight' => array('sorting' => 'ascending')),
                'was' => 'weight',
            ),
        );
        $tables = array();
        $db = $this->_application->getDB();
        foreach ($fields as $field_name => $field_schema) {
            if (empty($field_schema['columns'])) continue;
            
            $columns = $default_columns + $field_schema['columns'];
            $indexes = $default_indexes + (array)@$field_schema['indexes'];
            $tables[self::getFieldDataTableName($db->getResourcePrefix(), $field_name)] = array(
                'comment' => sprintf('Field data table for %s', $field_name),
                'fields' => $columns,
                'indexes' => $indexes,
                'initialization' => array(),
                'constraints' => array(),
            );
            //$tables[self::getFieldRevisionTableName($db->getResourcePrefix(), $field_name)] = array(
            //    'comment' => sprintf('Field revision table for %s', $field_name),
            //    'fields' => $columns,
            //    'indexes' => $indexes + array('revision_id' => array('fields' => array('revision_id' => array('sorting' => 'ascending')))),
            //    'initialization' => array(),
            //    'constraints' => array(),
            //);
        }

        if (empty($tables)) return false;

        return array(
            'name' => $db->getConnection()->getResourceName(),
            'create' => false,
            'overwrite' => false,
            'charset' => '',
            'description' => '',
            'tables' => $tables,
        );
    }

    public static function getFieldDataTableName($prefix, $fieldName)
    {
        $table_name = $prefix . 'entity_field_' . $fieldName;
        if (strlen($table_name) > 64) {
            $table_name = $prefix . 'entity_field_' . md5($fieldName);
            if (strlen($table_name) > 64) {
                throw new Sabai_RuntimeException('Table name is too long');
            }
        }
        return $table_name;
    }

    public static function getFieldRevisionTableName($prefix, $fieldName)
    {
        $table_name = $prefix . 'entity_field_r_' . $fieldName;
        if (strlen($table_name) > 64) {
            $table_name = $prefix . 'entity_field_r_' . md5($fieldName);
            if (strlen($table_name) > 64) {
                throw new Sabai_RuntimeException('Table name is too long');
            }
        }
        return $table_name;
    }

    public static function escapeFieldValue(SabaiFramework_DB $db, $value, $dataType)
    {
        switch ($dataType) {
            case Sabai_Addon_Field::COLUMN_TYPE_INTEGER:
                return intval($value);
            case Sabai_Addon_Field::COLUMN_TYPE_FLOAT:
            case Sabai_Addon_Field::COLUMN_TYPE_DECIMAL:
                return str_replace(',', '.', floatval($value));
            case Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN:
                return $db->escapeBool($value);
            default:
                return $db->escapeString($value);
        }
    }
}