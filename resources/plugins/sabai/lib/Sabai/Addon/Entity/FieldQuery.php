<?php
class Sabai_Addon_Entity_FieldQuery implements Sabai_Addon_Field_IQuery
{
    private $_criteria, $_criteriaOperator, $_criteriaIndex, $_extraFields = array(), $_sorts = array(), $_group;

    public function __construct($operator = 'AND')
    {
        $this->_criteriaIndex = 0;
        $this->_criteria = array(new SabaiFramework_Criteria_Composite());
        $this->_criteriaOperator = array($operator === 'OR' ? 'OR' : 'AND');
    }

    public function getCriteria()
    {
        return $this->_criteria[0];
    }

    public function getExtraFields()
    {
        return $this->_extraFields;
    }

    public function getSorts()
    {
        return $this->_sorts;
    }
    
    public function getGroup()
    {
        return $this->_group;
    }

    public function addExtraField($fieldName, $sql)
    {
        $this->_extraFields[$fieldName] = $sql;

        return $this;
    }

    public function startCriteriaGroup($inGroupOperator = 'AND')
    {
        ++$this->_criteriaIndex;
        $this->_criteria[$this->_criteriaIndex] = new SabaiFramework_Criteria_Composite();
        $this->_criteriaOperator[$this->_criteriaIndex] = $inGroupOperator === 'OR' ? 'OR' : 'AND';

        return $this;
    }

    public function finishCriteriaGroup($operator = null)
    {
        $criteria = $this->_criteria[$this->_criteriaIndex];
        unset($this->_criteria[$this->_criteriaIndex], $this->_criteriaOperator[$this->_criteriaIndex]);
        --$this->_criteriaIndex;
        if (!isset($operator)) return $this->addCriteria($criteria);

        if ($operator === 'OR') {
            $this->_criteria[$this->_criteriaIndex]->addOr($criteria);
        } else {
            $this->_criteria[$this->_criteriaIndex]->addAnd($criteria);
        }

        return $this;
    }

    public function addCriteria(SabaiFramework_Criteria $criteria)
    {
        if ($this->_criteriaOperator[$this->_criteriaIndex] === 'OR') {
            $this->_criteria[$this->_criteriaIndex]->addOr($criteria);
        } else {
            $this->_criteria[$this->_criteriaIndex]->addAnd($criteria);
        }

        return $this;
    }

    public function addSort(Sabai_Addon_Field_IField $field, $column, $order = 'ASC')
    {
        if ($property = $field->isPropertyField()) {
            return $this->sortByProperty($column, $order);
        }
        return $this->sortByField($field, $order, $column);
    }
    
    public function setGroup(Sabai_Addon_Field_IField $field, $column)
    {
        if ($property = $field->isPropertyField()) {
            return $this->groupByProperty($column);
        }
        return $this->groupByField($field, $column);
    }
    
    public function sortByField($field, $order = 'ASC', $column = 'value')
    {
        $this->_sorts[] = array(
            'field_name' => $field instanceof Sabai_Addon_Field_IField ? $field->getFieldName() : $field,
            'column' => $column,
            'order' => $order === 'DESC' ? 'DESC' : 'ASC',
        );

        return $this;
    }
    
    public function sortByProperty($property, $order = 'ASC')
    {
        $this->_sorts[] = array(
            'column' => $property,
            'is_property' => true,
            'order' => $order === 'DESC' ? 'DESC' : 'ASC',
        );

        return $this;
    }
    
    public function sortByRandom()
    {
        $this->_sorts[] = array(
            'is_random' => true,
        );

        return $this;
    }
    
    public function sortByExtraField($fieldName, $order = 'ASC')
    {
        $this->_sorts[] = array(
            'field_name' => $fieldName,
            'is_extra_field' => true,
            'order' => $order === 'DESC' ? 'DESC' : 'ASC',
        );

        return $this;
    }
    
    public function groupByField($field, $column = 'value')
    {
        $this->_group = array(
            'field_name' => $field instanceof Sabai_Addon_Field_IField ? $field->getFieldName() : $field,
            'column' => $column,
            'is_property' => false,
        );

        return $this;
    }
    
    public function groupByProperty($property)
    {
        $this->_group = array(
            'column' => $property,
            'is_property' => true,
        );

        return $this;
    }
    
    public function addIsCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIs($column, $value);
        }
        return $this->fieldIs($field, $value, $column);
    }

    public function addIsNotCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsNot($column, $value);
        }
        return $this->fieldIsNot($field, $value, $column);
    }

    public function addIsNullCriteria(Sabai_Addon_Field_IField $field, $column)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsNull($column);
        }
        return $this->fieldIsNull($field, $column);
    }

    public function addIsNotNullCriteria(Sabai_Addon_Field_IField $field, $column)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsNotNull($column);      
        }
        return $this->fieldIsNotNull($field, $column);
    }

    public function addInCriteria(Sabai_Addon_Field_IField $field, $column, array $values)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsIn($column, $values);
        }
        return $this->fieldIsIn($field, $values, $column);
    }

    public function addNotInCriteria(Sabai_Addon_Field_IField $field, $column, array $values)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsNotIn($column, $values);
        }
        return $this->fieldIsNotIn($field, $values, $column);
    }

    public function addIsOrGreaterThanCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsOrGreaterThan($column, $value);
        }
        return $this->fieldIsOrGreaterThan($field, $value, $column);
    }

    public function addIsOrSmallerThanCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsOrSmallerThan($column, $value);
        }
        return $this->fieldIsOrSmallerThan($field, $value, $column);
    }

    public function addIsGreaterThanCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsGreaterThan($column, $value);
        }
        return $this->fieldIsGreaterThan($field, $value, $column);
    }

    public function addIsSmallerThanCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyIsSmallerThan($column, $value);
        }
        return $this->fieldIsSmallerThan($field, $value, $column);
    }

    public function addStartsWithCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyStartsWith($column, $value);
        }
        return $this->fieldStartsWith($field, $value, $column);
    }

    public function addEndsWithCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyEndsWith($column, $value);
        }
        return $this->fieldEndsWith($field, $value, $column);
    }

    public function addContainsCriteria(Sabai_Addon_Field_IField $field, $column, $value)
    {
        if ($property = $field->isPropertyField()) {
            return $this->propertyContains($column, $value);
        }
        return $this->fieldContains($field, $value, $column);
    }
    
    public function fieldIs($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_Is($this->_fieldToArray($field, $column), $value));
    }

    public function fieldIsNot($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNot($this->_fieldToArray($field, $column), $value));
    }

    public function fieldIsNull($field, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNull($this->_fieldToArray($field, $column)));
    }

    public function fieldIsNotNull($field, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNotNull($this->_fieldToArray($field, $column)));
    }

    public function fieldIsIn($field, array $values, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_In($this->_fieldToArray($field, $column), $values));
    }

    public function fieldIsNotIn($field, array $values, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_NotIn($this->_fieldToArray($field, $column), $values));
    }

    public function fieldIsOrGreaterThan($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsOrGreaterThan($this->_fieldToArray($field, $column), $value));
    }

    public function fieldIsOrSmallerThan($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsOrSmallerThan($this->_fieldToArray($field, $column), $value));
    }

    public function fieldIsGreaterThan($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsGreaterThan($this->_fieldToArray($field, $column), $value));
    }

    public function fieldIsSmallerThan($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsSmallerThan($this->_fieldToArray($field, $column), $value));
    }

    public function fieldStartsWith($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_StartsWith($this->_fieldToArray($field, $column), $value));
    }

    public function fieldEndsWith($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_EndsWith($this->_fieldToArray($field, $column), $value));
    }

    public function fieldContains($field, $value, $column = 'value')
    {
        return $this->addCriteria(new SabaiFramework_Criteria_Contains($this->_fieldToArray($field, $column), $value));
    }
    
    private function _fieldToArray($field, $column)
    {
        return array(
            'field_name' => $field instanceof Sabai_Addon_Field_IField ? $field->getFieldName() : $field,
            'column' => $column,
            'is_property' => false,
        );
    }
    
    public function propertyIs($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_Is(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyIsNot($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNot(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyIsNull($property)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNull(array('column' => $property, 'is_property' => true)));
    }

    public function propertyIsNotNull($property)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsNotNull(array('column' => $property, 'is_property' => true)));
    }

    public function propertyIsIn($property, array $values)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_In(array('column' => $property, 'is_property' => true), $values));
    }

    public function propertyIsNotIn($property, array $values)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_NotIn(array('column' => $property, 'is_property' => true), $values));
    }

    public function propertyIsOrGreaterThan($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsOrGreaterThan(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyIsOrSmallerThan($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsOrSmallerThan(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyIsGreaterThan($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsGreaterThan(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyIsSmallerThan($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_IsSmallerThan(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertytartsWith($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_StartsWith(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyEndsWith($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_EndsWith(array('column' => $property, 'is_property' => true), $value));
    }

    public function propertyContains($property, $value)
    {
        return $this->addCriteria(new SabaiFramework_Criteria_Contains(array('column' => $property, 'is_property' => true), $value));
    }
}