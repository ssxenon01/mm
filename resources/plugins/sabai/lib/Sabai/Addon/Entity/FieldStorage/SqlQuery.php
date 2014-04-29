<?php
class Sabai_Addon_Entity_FieldStorage_SqlQuery implements SabaiFramework_Criteria_Visitor
{
    private $_tableName, $_tableIdKey, $_tableColumns, $_tableJoins, $_fieldColumnTypes, $_db, $_fieldQuery, $_parsed = false,
        $_criteria, $_joins, $_countJoins, $_sorts, $_group, $_tables = array(), $_sortTables = array(), $_extraFields;

    public function __construct(array $entityTypeInfo, array $fieldColumnTypes, SabaiFramework_DB $db, Sabai_Addon_Entity_FieldQuery $fieldQuery)
    {
        $this->_tableName = $entityTypeInfo['table_name'];
        $this->_tableIdKey = $entityTypeInfo['table_id_key'];
        $this->_tableColumns = $entityTypeInfo['properties'];
        $this->_fieldColumnTypes = $fieldColumnTypes;
        $this->_db = $db;
        $this->_fieldQuery = $fieldQuery;
        if (!empty($entityTypeInfo['table_joins'])) {
            $joins = array();
            foreach ($entityTypeInfo['table_joins'] as $join_table_name => $join_table) {
                $joins[] = sprintf('LEFT JOIN %1$s %2$s ON %2$s.%3$s', $join_table_name, $join_table['alias'], $join_table['on']);
            }
            $this->_tableJoins = implode(' ', $joins);
        } else {
            $this->_tableJoins = '';
        }
    }

    public function getEntityCount()
    {
        $this->_parseFieldQuery();
        
        if ($this->_group) {
            $sql = sprintf(
                'SELECT %6$s, COUNT(DISTINCT(%1$s)) FROM %2$s entity %3$s %4$s WHERE %5$s GROUP BY %6$s',
                $this->_tableIdKey,
                $this->_tableName,
                $this->_tableJoins,
                $this->_countJoins,
                $this->_criteria,
                $this->_group
            );
            $rs = $this->_db->query($sql);
            $ret = array();
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }

            return $ret;
        }
        
        $sql = sprintf(
            'SELECT COUNT(DISTINCT(%s)) FROM %s entity %s %s WHERE %s',
            $this->_tableIdKey,
            $this->_tableName,
            $this->_tableJoins,
            $this->_countJoins,
            $this->_criteria
        );

        return $this->_db->query($sql)->fetchSingle();
    }

    public function getEntityIds($limit, $offset)
    {
        $this->_parseFieldQuery();
        
        $sql = sprintf(
            'SELECT DISTINCT entity.%s AS id %s FROM %s entity %s %s WHERE %s %s',
            $this->_tableIdKey,
            isset($this->_extraFields) ? ', ' . $this->_extraFields : '',
            $this->_tableName,
            $this->_tableJoins,
            $this->_joins,
            $this->_criteria,
            $this->_sorts
        );
        $rs = $this->_db->query($sql, $limit, $offset);
        $ret = array();
        while ($row = $rs->fetchAssoc()) {
            $ret[$row['id']] = $row;
        }

        return $ret;
    }

    private function _parseFieldQuery()
    {
        if ($this->_parsed) return;

        // Criteria
        $_criteria = array();
        $criteria = $this->_fieldQuery->getCriteria();
        $criteria->acceptVisitor($this, $_criteria);
        $this->_criteria = implode(' ', $_criteria);
        
        // Extra fields
        if ($extra_fields = $this->_fieldQuery->getExtraFields()) {
            foreach ($extra_fields as $as => $sql) {
                $extra_fields[$as] = $sql . ' AS ' . $as;
            }
            $this->_extraFields = implode(', ', $extra_fields);
        }

        // Sorts
        if ($sorts = $this->_fieldQuery->getSorts()) {
            $_sorts = array();
            foreach ($sorts as $sort) {
                if (!empty($sort['is_property'])) {
                    $_sorts[] = $this->_getPropertyColumn($sort['column']) . ' ' . $sort['order'];
                } elseif (!empty($sort['is_extra_field'])) {
                    $_sorts[] = $sort['field_name'] . ' ' . $sort['order'];
                } elseif (!empty($sort['is_random'])) {
                    $_sorts[] = 'RAND()';
                } else {
                    $table_alias = $sort['field_name'];
                    if (!isset($this->_tables[$table_alias])) {
                        $this->_tables[$table_alias] = $this->_sortTables[$table_alias] = $table_alias;
                    }
                    $_sorts[] = $table_alias . '.' . $sort['column'] . ' ' . $sort['order'];
                }
            }
            $this->_sorts = 'ORDER BY ' . implode(', ', $_sorts);
        } else {
            $this->_sorts = '';
        }
           
        // Group
        if ($group = $this->_fieldQuery->getGroup()) {
            if ($group['is_property']) {
                $this->_group = $this->_getPropertyColumn($group['column']);
            } else {
                $table_alias = $group['field_name'];
                if (!isset($this->_tables[$table_alias])) {
                    $this->_tables[$table_alias] = $table_alias;
                }
                $this->_group = $table_alias . '.' . $group['column'];
            }
        }

        // Table joins
        if (!empty($this->_tables)) {
            $table_prefix = $this->_db->getResourcePrefix();
            foreach ($this->_tables as $table_alias) {
                $_joins[$table_alias] = 'LEFT JOIN ' . Sabai_Addon_Entity_FieldStorage_Sql::getFieldDataTableName($table_prefix, $table_alias)
                    . ' ' . $table_alias . ' ON ' . $table_alias . '.entity_id = entity.' . $this->_tableIdKey;
            }
            if (!empty($this->_sortTables)) {
                $this->_joins = implode(' ', $_joins);
                // For the count query, remove table joins that are used for sorting purpose only
                $this->_countJoins = implode(' ', array_diff_key($_joins, $this->_sortTables));
            } else {
                $this->_joins = $this->_countJoins = implode(' ', $_joins);
            }
        } else {
            $this->_joins = $this->_countJoins = '';
        }

        $this->_parsed = true;
    }

    /* Start implementation of SabaiFramework_Criteria_Visitor */

    public function visitCriteriaEmpty(SabaiFramework_Criteria_Empty $criteria, &$criterions)
    {
        $criterions[] = '1=1';
    }

    public function visitCriteriaComposite(SabaiFramework_Criteria_Composite $criteria, &$criterions)
    {
        if ($criteria->isEmpty()) {
            $criterions[] = '1=1';
            return;
        }
        $elements = $criteria->getElements();
        $count = count($elements);
        $conditions = $criteria->getConditions();
        $criterions[] = '(';
        $elements[0]->acceptVisitor($this, $criterions);
        for ($i = 1; $i < $count; $i++) {
            $criterions[] = $conditions[$i];
            $elements[$i]->acceptVisitor($this, $criterions);
        }
        $criterions[] = ')';
    }

    public function visitCriteriaCompositeNot(SabaiFramework_Criteria_CompositeNot $criteria, &$criterions)
    {
        $criterions[] = 'NOT';
        $criterions[] = $this->visitCriteriaComposite($criteria, $criterions);
    }

    private function _visitCriteriaValue(SabaiFramework_Criteria_Value $criteria, &$criterions, $operator)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
            $data_type = $this->_tableColumns[$target['column']]['column_type'];
        } else {
            $field_name = $target['field_name'];
            $this->_tables[$field_name] = $field_name;
            $criterions[] = $field_name . '.' . $target['column'];
            $data_type = $this->_fieldColumnTypes[$field_name][$target['column']];
        }
        $criterions[] = $operator;
        $criterions[] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, $criteria->getValue(), $data_type);
    }

    public function visitCriteriaIs(SabaiFramework_Criteria_Is $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '=');
    }

    public function visitCriteriaIsNot(SabaiFramework_Criteria_IsNot $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '!=');
    }

    public function visitCriteriaIsSmallerThan(SabaiFramework_Criteria_IsSmallerThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '<');
    }

    public function visitCriteriaIsGreaterThan(SabaiFramework_Criteria_IsGreaterThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '>');
    }

    public function visitCriteriaIsOrSmallerThan(SabaiFramework_Criteria_IsOrSmallerThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '<=');
    }

    public function visitCriteriaIsOrGreaterThan(SabaiFramework_Criteria_IsOrGreaterThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '>=');
    }

    public function visitCriteriaIsNull(SabaiFramework_Criteria_IsNull $criteria, &$criterions)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
        } else {
            $table_alias = $target['field_name'];
            $this->_tables[$table_alias] = $table_alias;
            $criterions[] = $table_alias . '.' . $target['column'];
        }
        $criterions[] = 'IS NULL';
    }

    public function visitCriteriaIsNotNull(SabaiFramework_Criteria_IsNotNull $criteria, &$criterions)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
        } else {
            $table_alias = $target['field_name'];
            $this->_tables[$table_alias] = $table_alias;
            $criterions[] = $table_alias . '.' . $target['column'];
        }
        $criterions[] = 'IS NOT NULL';
    }

    private function _visitCriteriaArray(SabaiFramework_Criteria_Array $criteria, &$criterions, $format)
    {
        $target = $criteria->getField();
        $values = $criteria->getArray();
        if (!empty($values)) {
            $data_type = $target['is_property']
                ? $this->_tableColumns[$target['column']]['column_type']
                : $this->_fieldColumnTypes[$target['field_name']][$target['column']];
            foreach (array_keys($values) as $k) {
                $values[$k] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, $values[$k], $data_type);
            }
            if ($target['is_property']) {
                $criterions[] = sprintf($format, $this->_getPropertyColumn($target['column']), implode(',', $values));
            } else {
                $this->_tables[$target['field_name']] = $target['field_name'];
                $criterions[] = sprintf($format, $target['field_name'] . '.' . $target['column'], implode(',', $values));
            }
        }
    }

    public function visitCriteriaIn(SabaiFramework_Criteria_In $criteria, &$criterions)
    {
        $this->_visitCriteriaArray($criteria, $criterions, '%s IN (%s)');
    }

    public function visitCriteriaNotIn(SabaiFramework_Criteria_NotIn $criteria, &$criterions)
    {
        $this->_visitCriteriaArray($criteria, $criterions, '%s NOT IN (%s)');
    }

    private function _visitCriteriaString(SabaiFramework_Criteria_String $criteria, &$criterions, $format)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
            $data_type = $this->_tableColumns[$target['column']]['column_type'];
        } else {
            $field_name = $target['field_name'];
            $this->_tables[$field_name] = $field_name;
            $criterions[] = $field_name . '.' . $target['column'];
            $data_type = $this->_fieldColumnTypes[$field_name][$target['column']];
        }
        $criterions[] = 'LIKE';
        $criterions[] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, sprintf($format, $criteria->getString()), $data_type);
    }

    public function visitCriteriaStartsWith(SabaiFramework_Criteria_StartsWith $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%s%%');
    }

    public function visitCriteriaEndsWith(SabaiFramework_Criteria_EndsWith $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%%%s');
    }

    public function visitCriteriaContains(SabaiFramework_Criteria_Contains $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%%%s%%');
    }

    private function _visitCriteriaField(SabaiFramework_Criteria_Field $criteria, &$criterions, $operator)
    {
        $criterions[] = '1=1';
    }

    public function visitCriteriaIsField(SabaiFramework_Criteria_IsField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '=');
    }

    public function visitCriteriaIsNotField(SabaiFramework_Criteria_IsNotField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '!=');
    }

    public function visitCriteriaIsSmallerThanField(SabaiFramework_Criteria_IsSmallerThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '<');
    }

    public function visitCriteriaIsGreaterThanField(SabaiFramework_Criteria_IsGreaterThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '>');
    }

    public function visitCriteriaIsOrSmallerThanField(SabaiFramework_Criteria_IsOrSmallerThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '<=');
    }

    public function visitCriteriaIsOrGreaterThanField(SabaiFramework_Criteria_IsOrGreaterThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '>=');
    }

    /* End implementation of SabaiFramework_Criteria_Visitor */
    
    private function _getPropertyColumn($column)
    {
        return isset($this->_tableColumns[$column]['column_real']) ? $this->_tableColumns[$column]['column_real'] : 'entity.' . $column;
    }
}