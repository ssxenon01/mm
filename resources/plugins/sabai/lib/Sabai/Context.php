<?php
class Sabai_Context extends SabaiFramework_Application_HttpContext
{
    const FLASH_ERROR = 'error', FLASH_WARNING = 'warning', FLASH_SUCCESS = 'success',
        ERROR_BAD_REQUEST = 400, ERROR_UNAUTHORIZED = 401, ERROR_FORBIDDEN = 403,
        ERROR_NOT_FOUND = 404, ERROR_METHOD_NOT_ALLOWED = 405, ERROR_NOT_ACCEPTABLE = 405,
        ERROR_INTERNAL_SERVER_ERROR = 500, ERROR_NOT_IMPLEMENTED = 501, ERROR_SERVICE_UNAVAILABLE = 503,
        REDIRECT_PERMANENT = 301, REDIRECT_TEMPORARY = 302;

    private $_parent, $_successUrl, $_errorType, $_errorMessage, $_errorUrl, $_redirectType, $_container,
        $_flash = array(), $_flashEnabled = true, $_successAttributes = array(),
        $_info = array(), $_title, $_htmlHeadTitle, $_menus = array(), $_inlineTabs = array(),
        $_tabs = array(), $_tabInfo = array(), $_tabMenus = array(),
        $_currentTabSet = 0, $_currentTab = array(), $_templates = array(), $_templateDirs = array(),
        $_url, $_summary;
    private static $_isAdmin = false;

    public function setIsAdmin($flag = true)
    {
        self::$_isAdmin = (bool)$flag;

        return $this;
    }

    public function isAdmin()
    {
        return self::$_isAdmin;
    }

    public function setParent(Sabai_Context $parent)
    {
        $this->setContentType($parent->getContentType())
            ->setAttributes($parent->getAttributes())
            ->setTemplateDirs($parent->getTemplateDirs());
        $this->_parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->_parent;
    }
        
    public function setUrl(SabaiFramework_Application_Url $url)
    {
        $this->_url = $url;
        return $this;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    public function setSummary($summary)
    {
        $this->_summary = $summary;
        return $this;
    }
    
    public function getSummary()
    {
        return $this->_summary;
    }

    public function getTemplates()
    {
        return $this->_templates;
    }

    public function hasTemplate()
    {
        return !empty($this->_templates);
    }
    
    public function addTemplate($template)
    {
        foreach ((array)$template as $_template) {
            $this->_templates[] = $_template;
        }
        
        return $this;
    }
    
    public function getTemplateDirs()
    {
        return $this->_templateDirs;
    }
            
    public function setTemplateDirs(array $templateDirs)
    {
        $this->_templateDirs = $templateDirs;
        
        return $this;
    }
    
    public function addTemplateDir($templateDir)
    {
        $this->_templateDirs[] = $templateDir;
        
        return $this;
    }
    
    public function setSuccess($url = null)
    {
        if (isset($url)) {
            $this->_successUrl = $url;
        }

        return parent::setSuccess();
    }
    
    public function setSuccessUrl($url)
    {
        $this->_successUrl = $url;
        
        return $this;
    }

    public function getSuccessUrl()
    {
        return $this->_successUrl;
    }

    public function setSuccessAttributes(array $values)
    {
        $this->_successAttributes = $values;
        
        return $this;
    }

    public function getSuccessAttributes()
    {
        return $this->_successAttributes;
    }

    public function getRedirectType()
    {
        return $this->_redirectType;
    }

    public function setRedirect($url, $type = self::REDIRECT_TEMPORARY)
    {
        $this->_redirectType = $type;

        return parent::setRedirect($url);
    }

    public function getErrorType()
    {
        return $this->_errorType;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    public function getErrorUrl()
    {
        return $this->_errorUrl;
    }
    
    public function setErrorUrl($url)
    {
        $this->_errorUrl = $url;
        
        return $this;
    }

    public function setError($message = null, $url = null, $type = self::ERROR_INTERNAL_SERVER_ERROR)
    {
        $this->_errorMessage = $message;
        if (isset($url)) {
            $this->_errorUrl = $url;
        }
        $this->_errorType = $type;

        return parent::setError();
    }

    public function setBadRequestError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_BAD_REQUEST);
    }

    public function setUnauthorizedError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_UNAUTHORIZED);
    }

    public function setForbiddenError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_FORBIDDEN);
    }

    public function setNotFoundError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_NOT_FOUND);
    }

    public function setMethodNotAllowedError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_METHOD_NOT_ALLOWED);
    }

    public function setNotAcceptableError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_NOT_ACCEPTABLE);
    }

    public function setInternalServerError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_INTERNAL_SERVER_ERROR);
    }

    public function setNotImplementedError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_NOT_IMPLEMENTED);
    }

    public function setServiceUnavailableError($url = null)
    {
        return $this->setError(null, $url, self::ERROR_SERVICE_UNAVAILABLE);
    }

    public function getContainer()
    {
        if (!isset($this->_container)) {
            if ((!$this->_container = $this->getRequest()->isAjax()) || true === $this->_container) {
                $this->_container = '#sabai-content';
            }
        }

        return $this->_container;
    }

    public function setContainer($id)
    {
        $this->_container = $id;

        return $this;
    }

    public function addFlash($message, $level = self::FLASH_SUCCESS)
    {
        $this->_flash[] = array('msg' => $message, 'level' => $level);

        return $this;
    }

    public function getFlash()
    {
        return $this->_flash;
    }

    public function hasFlash()
    {
        return !empty($this->_flash);
    }

    public function clearFlash()
    {
        $this->_flash = array();
        
        return $this;
    }

    public function setFlashEnabled($flag = true)
    {
        $this->_flashEnabled = $flag;

        return $this;
    }

    public function isFlashEnabled()
    {
        return $this->_flashEnabled;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function setInfo($title, SabaiFramework_Application_Url $url = null, $ajax = true)
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_info[(string)$url] = array(
                'title' => $title,
                'url' => $url,
            );
        } else {
            $this->_tabInfo[$this->_currentTabSet][(string)$url] = array(
                'title' => $title,
                'url' => $url,
                'ajax' => $ajax,
            );
        }

        return $this;
    }

    public function popInfo()
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            return array_pop($this->_info);
        }

        return array_pop($this->_tabInfo[$this->_currentTabSet]);
    }

    public function getTitle()
    {
        if (!isset($this->_title)) {
            if (($page_info = $this->_info)
                && ($page_info_last = array_pop($page_info))
            ) {
                $this->_title = $page_info_last['title'];
            } else {
                $this->_title = '';
            }
        }

        return $this->_title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }
    
    public function getHtmlHeadTitle()
    {
        return isset($this->_htmlHeadTitle) ? $this->_htmlHeadTitle : $this->getTitle();
    }
    
    public function setHtmlHeadTitle($title)
    {
        $this->_htmlHeadTitle = $title;

        return $this;
    }

    public function getMenus()
    {
        return $this->_menus;
    }

    public function setMenus(array $menus, $page = false)
    {
        if ($page || empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_menus = $menus;
        } else {
            $this->_tabMenus[$this->_currentTabSet] = $menus;
        }

        return $this;
    }

    public function addMenu($menu, $page = false)
    {
        if ($page || empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_menus[] = $menu;
        } else {
            $this->_tabMenus[$this->_currentTabSet][] = $menu;
        }

        return $this;
    }

    public function popMenus($page = false)
    {
        if ($page || empty($this->_tabs) || empty($this->_currentTab)) {
            unset($this->_menus);
        }

        unset($this->_tabMenus[$this->_currentTabSet]);
    }

    public function getInlineTabs()
    {
        return $this->_inlineTabs;
    }

    public function setInlineTabs(array $tabs)
    {
        $this->_inlineTabs = $tabs;

        return $this;
    }

    public function getTabs()
    {
        return $this->_tabs;
    }

    public function getTabInfo()
    {
        return $this->_tabInfo;
    }

    public function getTabMenus()
    {
        return $this->_tabMenus;
    }

    public function pushTabs(array $tabs)
    {
        $this->_tabs[++$this->_currentTabSet] = $tabs;

        return $this;
    }

    public function popTabs()
    {
        unset(
            $this->_tabs[$this->_currentTabSet],
            $this->_currentTab[$this->_currentTabSet],
            $this->_tabInfo[$this->_currentTabSet],
            $this->_tabMenus[$this->_currentTabSet]
        );
        --$this->_currentTabSet;

        return $this;
    }

    public function setCurrentTab($tabName)
    {
        if (isset($this->_tabs[$this->_currentTabSet][$tabName])) {
            $this->_currentTab[$this->_currentTabSet] = $tabName;
        }

        return $this;
    }

    public function getCurrentTab()
    {
        return $this->_currentTab;
    }
    
    public function clearTabs()
    {
        foreach (array_keys($this->_currentTab) as $tab_set) {
            if (empty($this->_tabInfo[$tab_set])) continue;
            foreach ($this->_tabInfo[$tab_set] as $_tab_info) {                
                if (!isset($this->_info[(string)$_tab_info['url']])) {
                    $this->_info[(string)$_tab_info['url']] = array('title' => $_tab_info['title'], 'url' => $_tab_info['url']);
                }
            }
        }
        if (isset($this->_tabMenus[$this->_currentTabSet])) {
            $this->_menus = $this->_tabMenus[$this->_currentTabSet];
        }
        $this->_currentTab = $this->_tabs = $this->_tabMenu = $this->_tabInfo = array();
        $this->_currentTabSet = 0;
        
        return $this;
    }
}
