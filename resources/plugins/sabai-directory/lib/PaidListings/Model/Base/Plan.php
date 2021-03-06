<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_PaidListings_Model_Base_Plan extends SabaiFramework_Model_Entity
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('Plan', $model);
        $this->_vars = array('plan_name' => null, 'plan_description' => null, 'plan_type' => null, 'plan_price' => 0, 'plan_features' => null, 'plan_active' => true, 'plan_weight' => 0, 'plan_id' => 0, 'plan_created' => 0, 'plan_updated' => 0);
    }

    public function __clone()
    {
        $this->_vars = array('plan_id' => 0, 'plan_created' => 0, 'plan_updated' => 0) + $this->_vars;
    }

    public function __toString()
    {
        return $this->__get('name');
    }

    public function addOrder(Sabai_Addon_PaidListings_Model_Order $entity)
    {
        $entity->Plan = $this;

        return $this;
    }

    public function removeOrder(Sabai_Addon_PaidListings_Model_Order $entity)
    {
        $this->removeOrderById($entity->id);

        return $this;
    }

    public function removeOrderById($id)
    {
        $this->_removeEntityById('order_id', 'Order', $id);

        return $this;
    }

    public function createOrder()
    {
        return $this->_createEntity('Order');
    }

    public function removeOrders()
    {
        $this->_removeEntities('Order');

        return $this;
    }

    public function __get($name)
    {
        if ($name === 'name')
            return $this->_vars['plan_name'];
        elseif ($name === 'description')
            return $this->_vars['plan_description'];
        elseif ($name === 'type')
            return $this->_vars['plan_type'];
        elseif ($name === 'price')
            return $this->_vars['plan_price'];
        elseif ($name === 'features')
            return $this->_vars['plan_features'];
        elseif ($name === 'active')
            return $this->_vars['plan_active'];
        elseif ($name === 'weight')
            return $this->_vars['plan_weight'];
        elseif ($name === 'id')
            return $this->_vars['plan_id'];
        elseif ($name === 'created')
            return $this->_vars['plan_created'];
        elseif ($name === 'updated')
            return $this->_vars['plan_updated'];
        elseif ($name === 'Orders')
            return $this->_fetchEntities('Order', 'Orders');
        else
            return $this->fetchObject($name);
    }

    public function __set($name, $value)
    {
        if ($name === 'name')
            $this->_setVar('plan_name', $value);
        elseif ($name === 'description')
            $this->_setVar('plan_description', $value);
        elseif ($name === 'type')
            $this->_setVar('plan_type', $value);
        elseif ($name === 'price')
            $this->_setVar('plan_price', $value);
        elseif ($name === 'features')
            $this->_setVar('plan_features', $value);
        elseif ($name === 'active')
            $this->_setVar('plan_active', $value);
        elseif ($name === 'weight')
            $this->_setVar('plan_weight', $value);
        elseif ($name === 'id')
            $this->_setVar('plan_id', $value);
        elseif ($name === 'Orders') {
            $this->removeOrders();
            foreach (array_keys($value) as $i) $this->addOrder($value[$i]);
        }
        else
            $this->assignObject($name, $value);
    }

    protected function _initVar($name, $value)
    {
        if ($name === 'plan_price')
            $this->_vars['plan_price'] = (float)$value;
        elseif ($name === 'plan_features')
            $this->_vars['plan_features'] = @unserialize($value);
        elseif ($name === 'plan_active')
            $this->_vars['plan_active'] = (bool)$value;
        elseif ($name === 'plan_weight')
            $this->_vars['plan_weight'] = (int)$value;
        elseif ($name === 'plan_id')
            $this->_vars['plan_id'] = (int)$value;
        elseif ($name === 'plan_created')
            $this->_vars['plan_created'] = (int)$value;
        elseif ($name === 'plan_updated')
            $this->_vars['plan_updated'] = (int)$value;
        else
            $this->_vars[$name] = $value;
    }
}

abstract class Sabai_Addon_PaidListings_Model_Base_PlanRepository extends SabaiFramework_Model_EntityRepository
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('Plan', $model);
    }

    protected function _getCollectionByRowset(SabaiFramework_DB_Rowset $rs)
    {
        return new Sabai_Addon_PaidListings_Model_Base_PlansByRowset($rs, $this->_model->create('Plan'), $this->_model);
    }

    public function createCollection(array $entities = array())
    {
        return new Sabai_Addon_PaidListings_Model_Base_Plans($this->_model, $entities);
    }
}

class Sabai_Addon_PaidListings_Model_Base_PlansByRowset extends SabaiFramework_Model_EntityCollection_Rowset
{
    public function __construct(SabaiFramework_DB_Rowset $rs, Sabai_Addon_PaidListings_Model_Plan $emptyEntity, SabaiFramework_Model $model)
    {
        parent::__construct('Plans', $rs, $emptyEntity, $model);
    }

    protected function _loadRow(SabaiFramework_Model_Entity $entity, array $row)
    {
        $entity->initVars($row);
    }
}

class Sabai_Addon_PaidListings_Model_Base_Plans extends SabaiFramework_Model_EntityCollection_Array
{
    public function __construct(SabaiFramework_Model $model, array $entities = array())
    {
        parent::__construct($model, 'Plans', $entities);
    }
}