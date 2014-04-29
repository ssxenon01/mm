<?php
abstract class Sabai_Addon_Entity_Controller_ListEntities extends Sabai_Controller
{
    protected $_paginate = true, $_perPage = 20, $_defaultPage = 1, $_defaultSort, $_displayMode = 'summary', $_template, $_sortContainer, $_scrollTo;
    
    protected function _doExecute(Sabai_Context $context)
    {
        // Init 
        $bundle = $this->_getBundle($context);
        if (false === $bundle) {
            $context->setError();
            return;
        }
        if ($sorts = $this->_getSorts($context)) {
            $sort_keys = array_keys($sorts);
            $default_sort = isset($this->_defaultSort) && isset($sorts[$this->_defaultSort]) ? $this->_defaultSort : array_shift($sort_keys);
            $current_sort = $context->getRequest()->asStr('sort', $default_sort, $sort_keys);
        } else {
            $current_sort = null;
        }
        // Generate sort links
        $url_params = isset($current_sort)
            ? $this->_getUrlParams($context, $bundle) + array('sort' => $current_sort)
            : $this->_getUrlParams($context, $bundle);
        if (!empty($sorts)) {
            foreach ($sorts as $key => $sort) {
                $options = array();
                if (!is_array($sort)) {
                    $sort = array('label' => $sort);
                    $attr = array();
                } else {
                    $attr = isset($sort['title']) ? array('title' => $sort['title']) : array();
                }
                if (isset($this->_sortContainer)) {
                    $attr['data-container'] = $this->_sortContainer; // this attribute is required for tooltips
                    $container = $this->_sortContainer;
                } else {
                    $container = $context->getContainer();
                }
                if (isset($this->_scrollTo)) {
                    $options['scroll'] = $this->_scrollTo;
                }
                $options['active'] = $key === $current_sort;
                $sorts[$key] = $this->LinkToRemote(
                    $sort['label'],
                    $container,
                    $this->Url($context->getRoute(), array('sort' => $key) + $url_params),
                    $options,
                    $attr
                );
            }
        }
        // Set template
        if (!isset($this->_template)) {
            $this->_template = $bundle ? $bundle->type . '_list' : $context->bundle->type . '_list';
        }
        // Fetch entities
        if ($entities = $this->_getEntities($context, $current_sort, $bundle)) {
            $entities = $this->Entity_Render($this->_getEntityType($context), $entities, $bundle ? $bundle->name : null, $this->_displayMode);
        }
        // Assign context
        $context->addTemplate($this->_template)
            ->setAttributes(array(
                'entities' => $entities,
                'url_params' => $url_params,
                'sorts' => $sorts,
                'current_sort' => $current_sort,
                'links' => $this->_getLinks($context, $current_sort, $bundle),
            ));
    }
    
    protected function _getEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->_paginate ? $this->_paginateEntities($context, $sort, $bundle) : $this->_fetchEntities($context, $sort, $bundle);
    }
    
    protected function _paginateEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if ((!$context->paginator = $this->_paginate($context, $sort, $bundle))
            || !$context->paginator->getElementCount()
        ) {
            return array();
        }
        return $context->paginator->getElements();
    }
    
    protected function _paginate(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        // Create query
        if (!$query = $this->_createQuery($context, $sort, $bundle)) {
            return;
        }
        return $query->paginate($this->_perPage)->setCurrentPage($context->getRequest()->asInt(Sabai::$p, $this->_defaultPage));
    }
    
    protected function _fetchEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return ($query = $this->_createQuery($context, $sort, $bundle)) ? $query->fetch() : array();
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        
    }
    
    /**
     *@return string 
     */
    abstract protected function _getEntityType(Sabai_Context $context);
    /**
     *@return Sabai_Addon_Entity_Model_Bundle or null
     */
    abstract protected function _getBundle(Sabai_Context $context);
    /**
     *@return array 
     */
    abstract protected function _getSorts(Sabai_Context $context);
    /**
     *@return array 
     */
    abstract protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null);
    /**
     *@return array 
     */
    abstract protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null);
}