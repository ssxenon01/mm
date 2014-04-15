<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_Form_Model_Base_FieldGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'form_field';
    }

    public function getFields()
    {
        return array('field_type' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'field_addon' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'field_system' => SabaiFramework_Model::KEY_TYPE_BOOL, 'field_created' => SabaiFramework_Model::KEY_TYPE_INT, 'field_updated' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'field_type';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sform_field WHERE field_type = %s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($id)
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sform_field WHERE field_type IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map(array($this->_db, 'escapeString'), $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$sform_field form_field WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['field_created'] = time();
        $values['field_updated'] = 0;
        return sprintf('INSERT INTO %sform_field(field_type, field_addon, field_system, field_created, field_updated) VALUES(%s, %s, %u, %d, %d)', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['field_type']), $this->_db->escapeString($values['field_addon']), $this->_db->escapeBool($values['field_system']), $values['field_created'], $values['field_updated']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['field_updated'];
        $values['field_updated'] = time();
        return sprintf('UPDATE %sform_field SET field_addon = %s, field_system = %u, field_updated = %d WHERE field_type = %s AND field_updated = %d', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['field_addon']), $this->_db->escapeBool($values['field_system']), $values['field_updated'], $this->_db->escapeString($id), $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$sform_field WHERE field_type = %2$s', $this->_db->getResourcePrefix(), $this->_db->escapeString($id));
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['field_updated'] = 'field_updated=' . time();
        return sprintf('UPDATE %sform_field form_field SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE form_field FROM %1$sform_field form_field WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$sform_field form_field WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }
}