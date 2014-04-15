<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_System_Model_Base_AdminrouteGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'system_adminroute';
    }

    public function getFields()
    {
        return array('adminroute_path' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_method' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_format' => SabaiFramework_Model::KEY_TYPE_TEXT, 'adminroute_controller' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_controller_addon' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_forward' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_addon' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_type' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_class' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_access_callback' => SabaiFramework_Model::KEY_TYPE_BOOL, 'adminroute_title_callback' => SabaiFramework_Model::KEY_TYPE_BOOL, 'adminroute_callback_path' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_callback_addon' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_title' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'adminroute_description' => SabaiFramework_Model::KEY_TYPE_TEXT, 'adminroute_weight' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_depth' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_ajax' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_priority' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_data' => SabaiFramework_Model::KEY_TYPE_TEXT, 'adminroute_id' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_created' => SabaiFramework_Model::KEY_TYPE_INT, 'adminroute_updated' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'adminroute_id';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %ssystem_adminroute WHERE adminroute_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %ssystem_adminroute WHERE adminroute_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$ssystem_adminroute system_adminroute WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['adminroute_created'] = time();
        $values['adminroute_updated'] = 0;
        return sprintf('INSERT INTO %ssystem_adminroute(adminroute_path, adminroute_method, adminroute_format, adminroute_controller, adminroute_controller_addon, adminroute_forward, adminroute_addon, adminroute_type, adminroute_class, adminroute_access_callback, adminroute_title_callback, adminroute_callback_path, adminroute_callback_addon, adminroute_title, adminroute_description, adminroute_weight, adminroute_depth, adminroute_ajax, adminroute_priority, adminroute_data, adminroute_created, adminroute_updated) VALUES(%s, %s, %s, %s, %s, %s, %s, %d, %s, %u, %u, %s, %s, %s, %s, %d, %d, %d, %d, %s, %d, %d)', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['adminroute_path']), $this->_db->escapeString($values['adminroute_method']), $this->_db->escapeString(serialize($values['adminroute_format'])), $this->_db->escapeString($values['adminroute_controller']), $this->_db->escapeString($values['adminroute_controller_addon']), $this->_db->escapeString($values['adminroute_forward']), $this->_db->escapeString($values['adminroute_addon']), $values['adminroute_type'], $this->_db->escapeString($values['adminroute_class']), $this->_db->escapeBool($values['adminroute_access_callback']), $this->_db->escapeBool($values['adminroute_title_callback']), $this->_db->escapeString($values['adminroute_callback_path']), $this->_db->escapeString($values['adminroute_callback_addon']), $this->_db->escapeString($values['adminroute_title']), $this->_db->escapeString($values['adminroute_description']), $values['adminroute_weight'], $values['adminroute_depth'], $values['adminroute_ajax'], $values['adminroute_priority'], $this->_db->escapeString(serialize($values['adminroute_data'])), $values['adminroute_created'], $values['adminroute_updated']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['adminroute_updated'];
        $values['adminroute_updated'] = time();
        return sprintf('UPDATE %ssystem_adminroute SET adminroute_path = %s, adminroute_method = %s, adminroute_format = %s, adminroute_controller = %s, adminroute_controller_addon = %s, adminroute_forward = %s, adminroute_addon = %s, adminroute_type = %d, adminroute_class = %s, adminroute_access_callback = %u, adminroute_title_callback = %u, adminroute_callback_path = %s, adminroute_callback_addon = %s, adminroute_title = %s, adminroute_description = %s, adminroute_weight = %d, adminroute_depth = %d, adminroute_ajax = %d, adminroute_priority = %d, adminroute_data = %s, adminroute_updated = %d WHERE adminroute_id = %d AND adminroute_updated = %d', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['adminroute_path']), $this->_db->escapeString($values['adminroute_method']), $this->_db->escapeString(serialize($values['adminroute_format'])), $this->_db->escapeString($values['adminroute_controller']), $this->_db->escapeString($values['adminroute_controller_addon']), $this->_db->escapeString($values['adminroute_forward']), $this->_db->escapeString($values['adminroute_addon']), $values['adminroute_type'], $this->_db->escapeString($values['adminroute_class']), $this->_db->escapeBool($values['adminroute_access_callback']), $this->_db->escapeBool($values['adminroute_title_callback']), $this->_db->escapeString($values['adminroute_callback_path']), $this->_db->escapeString($values['adminroute_callback_addon']), $this->_db->escapeString($values['adminroute_title']), $this->_db->escapeString($values['adminroute_description']), $values['adminroute_weight'], $values['adminroute_depth'], $values['adminroute_ajax'], $values['adminroute_priority'], $this->_db->escapeString(serialize($values['adminroute_data'])), $values['adminroute_updated'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$ssystem_adminroute WHERE adminroute_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['adminroute_updated'] = 'adminroute_updated=' . time();
        return sprintf('UPDATE %ssystem_adminroute system_adminroute SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE system_adminroute FROM %1$ssystem_adminroute system_adminroute WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$ssystem_adminroute system_adminroute WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }
}