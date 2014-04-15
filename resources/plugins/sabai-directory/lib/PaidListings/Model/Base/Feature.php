<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_PaidListings_Model_Base_Feature extends SabaiFramework_Model_Entity
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('Feature', $model);
        $this->_vars = array('id' => null, 'feature_name' => null, 'feature_addon' => null, 'feature_settings' => null, 'feature_created' => 0, 'feature_updated' => 0);
    }

    public function __clone()
    {
        $this->_vars = array('id' => null, 'feature_name' => null, 'feature_created' => 0, 'feature_updated' => 0) + $this->_vars;
    }

    public function __toString()
    {
        return $this->__get('name');
    }

    public function initVars(array $arr)
    {
        parent::initVars($arr);
        $this->_vars['id'] = $this->_vars['feature_name'];
    }

    public function addOrderItem(Sabai_Addon_PaidListings_Model_OrderItem $entity)
    {
        $entity->Feature = $this;

        return $this;
    }

    public function removeOrderItem(Sabai_Addon_PaidListings_Model_OrderItem $entity)
    {
        $this->removeOrderItemById($entity->id);

        return $this;
    }

    public function removeOrderItemById($id)
    {
        $this->_removeEntityById('orderitem_id', 'OrderItem', $id);

        return $this;
    }

    public function createOrderItem()
    {
        return $this->_createEntity('OrderItem');
    }

    public function removeOrderItems()
    {
        $this->_removeEntities('OrderItem');

        return $this;
    }

    public function __get($name)
    {
        if ($name === 'id')
            return $this->_vars['id'];
        elseif ($name === 'name')
            return $this->_vars['feature_name'];
        elseif ($name === 'addon')
            return $this->_vars['feature_addon'];
        elseif ($name === 'settings')
            return $this->_vars['feature_settings'];
        elseif ($name === 'created')
            return $this->_vars['feature_created'];
        elseif ($name === 'updated')
            return $this->_vars['feature_updated'];
        elseif ($name === 'OrderItems')
            return $this->_fetchEntities('OrderItem', 'OrderItems');
        else
            return $this->fetchObject($name);
    }

    public function __set($name, $value)
    {
        if ($name === 'id')
            $this->_setVar('id', $value);
        elseif ($name === 'name')
            $this->_setVar('feature_name', $value);
        elseif ($name === 'addon')
            $this->_setVar('feature_addon', $value);
        elseif ($name === 'settings')
            $this->_setVar('feature_settings', $value);
        elseif ($name === 'OrderItems') {
            $this->removeOrderItems();
            foreach (array_keys($value) as $i) $this->addOrderItem($value[$i]);
        }
        else
            $this->assignObject($name, $value);
    }

    protected function _initVar($name, $value)
    {
        if ($name === 'feature_settings')
            $this->_vars['feature_settings'] = @unserialize($value);
        elseif ($name === 'feature_created')
            $this->_vars['feature_created'] = (int)$value;
        elseif ($name === 'feature_updated')
            $this->_vars['feature_updated'] = (int)$value;
        else
            $this->_vars[$name] = $value;
    }
}

abstract class Sabai_Addon_PaidListings_Model_Base_FeatureRepository extends SabaiFramework_Model_EntityRepository
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('Feature', $model);
    }

    protected function _getCollectionByRowset(SabaiFramework_DB_Rowset $rs)
    {
        return new Sabai_Addon_PaidListings_Model_Base_FeaturesByRowset($rs, $this->_model->create('Feature'), $this->_model);
    }

    public function createCollection(array $entities = array())
    {
        return new Sabai_Addon_PaidListings_Model_Base_Features($this->_model, $entities);
    }
}

class Sabai_Addon_PaidListings_Model_Base_FeaturesByRowset extends SabaiFramework_Model_EntityCollection_Rowset
{
    public function __construct(SabaiFramework_DB_Rowset $rs, Sabai_Addon_PaidListings_Model_Feature $emptyEntity, SabaiFramework_Model $model)
    {
        parent::__construct('Features', $rs, $emptyEntity, $model);
    }

    protected function _loadRow(SabaiFramework_Model_Entity $entity, array $row)
    {
        $entity->initVars($row);
    }
}

class Sabai_Addon_PaidListings_Model_Base_Features extends SabaiFramework_Model_EntityCollection_Array
{
    public function __construct(SabaiFramework_Model $model, array $entities = array())
    {
        parent::__construct($model, 'Features', $entities);
    }
}