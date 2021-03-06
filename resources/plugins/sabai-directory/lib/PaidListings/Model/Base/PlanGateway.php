<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_PaidListings_Model_Base_PlanGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'paidlistings_plan';
    }

    public function getFields()
    {
        return array('plan_name' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'plan_description' => SabaiFramework_Model::KEY_TYPE_TEXT, 'plan_type' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'plan_price' => SabaiFramework_Model::KEY_TYPE_DECIMAL, 'plan_features' => SabaiFramework_Model::KEY_TYPE_TEXT, 'plan_active' => SabaiFramework_Model::KEY_TYPE_BOOL, 'plan_weight' => SabaiFramework_Model::KEY_TYPE_INT, 'plan_id' => SabaiFramework_Model::KEY_TYPE_INT, 'plan_created' => SabaiFramework_Model::KEY_TYPE_INT, 'plan_updated' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'plan_id';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %spaidlistings_plan WHERE plan_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %spaidlistings_plan WHERE plan_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$spaidlistings_plan paidlistings_plan WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['plan_created'] = time();
        $values['plan_updated'] = 0;
        return sprintf('INSERT INTO %spaidlistings_plan(plan_name, plan_description, plan_type, plan_price, plan_features, plan_active, plan_weight, plan_created, plan_updated) VALUES(%s, %s, %s, %F, %s, %u, %d, %d, %d)', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['plan_name']), $this->_db->escapeString($values['plan_description']), $this->_db->escapeString($values['plan_type']), $values['plan_price'], $this->_db->escapeString(serialize($values['plan_features'])), $this->_db->escapeBool($values['plan_active']), $values['plan_weight'], $values['plan_created'], $values['plan_updated']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['plan_updated'];
        $values['plan_updated'] = time();
        return sprintf('UPDATE %spaidlistings_plan SET plan_name = %s, plan_description = %s, plan_type = %s, plan_price = %F, plan_features = %s, plan_active = %u, plan_weight = %d, plan_updated = %d WHERE plan_id = %d AND plan_updated = %d', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['plan_name']), $this->_db->escapeString($values['plan_description']), $this->_db->escapeString($values['plan_type']), $values['plan_price'], $this->_db->escapeString(serialize($values['plan_features'])), $this->_db->escapeBool($values['plan_active']), $values['plan_weight'], $values['plan_updated'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$spaidlistings_plan WHERE plan_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['plan_updated'] = 'plan_updated=' . time();
        return sprintf('UPDATE %spaidlistings_plan paidlistings_plan SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE paidlistings_plan FROM %1$spaidlistings_plan paidlistings_plan WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$spaidlistings_plan paidlistings_plan WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }
}