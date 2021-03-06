<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_Field_Model_Base_TypeGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'field_type';
    }

    public function getFields()
    {
        return array('type_name' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'type_addon' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'type_created' => SabaiFramework_Model::KEY_TYPE_INT, 'type_updated' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'type_name';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sfield_type WHERE type_name = %s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($id)
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sfield_type WHERE type_name IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map(array($this->_db, 'escapeString'), $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$sfield_type field_type WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['type_created'] = time();
        $values['type_updated'] = 0;
        return sprintf('INSERT INTO %sfield_type(type_name, type_addon, type_created, type_updated) VALUES(%s, %s, %d, %d)', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['type_name']), $this->_db->escapeString($values['type_addon']), $values['type_created'], $values['type_updated']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['type_updated'];
        $values['type_updated'] = time();
        return sprintf('UPDATE %sfield_type SET type_addon = %s, type_updated = %d WHERE type_name = %s AND type_updated = %d', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['type_addon']), $values['type_updated'], $this->_db->escapeString($id), $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$sfield_type WHERE type_name = %2$s', $this->_db->getResourcePrefix(), $this->_db->escapeString($id));
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['type_updated'] = 'type_updated=' . time();
        return sprintf('UPDATE %sfield_type field_type SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE field_type FROM %1$sfield_type field_type WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$sfield_type field_type WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }
}