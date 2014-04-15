<?php
abstract class Sabai_Addon_Entity_Controller_ViewEntity extends Sabai_Controller
{
    protected $_template;
    
    protected function _doExecute(Sabai_Context $context)
    {
        // Load entity
        $entity = $this->_getEntity($context);
        $this->Entity_LoadFields($entity);
        // Set context
        $context->clearTabs()
            ->addTemplate(isset($this->_template) ? $this->_template : $this->Entity_Bundle($entity)->type . '_single_full')
            ->setAttributes(array_shift($this->Entity_Render($entity)));
    }
    
    /**
     *@return Sabai_Addon_Entity_IEntity $entity 
     */
    abstract protected function _getEntity(Sabai_Context $context);
}