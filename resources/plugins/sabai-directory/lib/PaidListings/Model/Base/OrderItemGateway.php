<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_PaidListings_Model_Base_OrderItemGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'paidlistings_orderitem';
    }

    public function getFields()
    {
        return array('orderitem_status' => SabaiFramework_Model::KEY_TYPE_INT, 'orderitem_id' => SabaiFramework_Model::KEY_TYPE_INT, 'orderitem_created' => SabaiFramework_Model::KEY_TYPE_INT, 'orderitem_updated' => SabaiFramework_Model::KEY_TYPE_INT, 'orderitem_feature_name' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'orderitem_order_id' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'orderitem_id';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %spaidlistings_orderitem WHERE orderitem_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %spaidlistings_orderitem WHERE orderitem_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$spaidlistings_orderitem paidlistings_orderitem WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['orderitem_created'] = time();
        $values['orderitem_updated'] = 0;
        return sprintf('INSERT INTO %spaidlistings_orderitem(orderitem_status, orderitem_created, orderitem_updated, orderitem_feature_name, orderitem_order_id) VALUES(%d, %d, %d, %s, %d)', $this->_db->getResourcePrefix(), $values['orderitem_status'], $values['orderitem_created'], $values['orderitem_updated'], $this->_db->escapeString($values['orderitem_feature_name']), $values['orderitem_order_id']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['orderitem_updated'];
        $values['orderitem_updated'] = time();
        return sprintf('UPDATE %spaidlistings_orderitem SET orderitem_status = %d, orderitem_updated = %d, orderitem_feature_name = %s, orderitem_order_id = %d WHERE orderitem_id = %d AND orderitem_updated = %d', $this->_db->getResourcePrefix(), $values['orderitem_status'], $values['orderitem_updated'], $this->_db->escapeString($values['orderitem_feature_name']), $values['orderitem_order_id'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$spaidlistings_orderitem WHERE orderitem_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['orderitem_updated'] = 'orderitem_updated=' . time();
        return sprintf('UPDATE %spaidlistings_orderitem paidlistings_orderitem SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE paidlistings_orderitem, table1, table2 FROM %1$spaidlistings_orderitem paidlistings_orderitem LEFT JOIN %1$spaidlistings_orderitemmeta table1 ON paidlistings_orderitem.orderitem_id = table1.orderitemmeta_orderitem_id LEFT JOIN %1$spaidlistings_orderlog table2 ON paidlistings_orderitem.orderitem_id = table2.orderlog_orderitem_id WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$spaidlistings_orderitem paidlistings_orderitem WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _afterInsert1($id, array $new)
    {
    }

    protected function _afterDelete1($id, array $old)
    {
    }

    protected function _afterUpdate1($id, array $new, array $old)
    {
    }

    protected function _beforeDelete1($id, array $old)
    {
        $this->_db->exec(sprintf('DELETE table0 FROM %1$spaidlistings_orderitemmeta table0 WHERE table0.orderitemmeta_orderitem_id = %2$d', $this->_db->getResourcePrefix(), $id));
    }

    protected function _beforeDelete2($id, array $old)
    {
        $this->_db->exec(sprintf('DELETE table0 FROM %1$spaidlistings_orderlog table0 WHERE table0.orderlog_orderitem_id = %2$d', $this->_db->getResourcePrefix(), $id));
    }

    protected function _afterInsert($id, array $new)
    {
        $this->_afterInsert1($id, $new);
    }

    protected function _afterUpdate($id, array $new, array $old)
    {
        $this->_afterUpdate1($id, $new, $old);
    }

    protected function _beforeDelete($id, array $old)
    {
        $this->_beforeDelete1($id, $old);
        $this->_beforeDelete2($id, $old);
    }

    protected function _afterDelete($id, array $old)
    {
        $this->_afterDelete1($id, $old);
    }
}