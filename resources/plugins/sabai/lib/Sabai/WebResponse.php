<?php
class Sabai_WebResponse extends Sabai_Response
{
    private $_layoutHtmlTemplate, $_inlineLayoutHtmlTemplate, $_flash = array(), $_htmlHead = array(),
        $_js, $_jsRaw, $_jsFiles = array(), $_jsIndex = 5,
        $_css = array(), $_cssFiles = array(), $_cssIndex = 5, $_cssFileIndices = array();

    public function send(SabaiFramework_Application_Context $context)
    {
        $this->_application->doEvent('SabaiWebResponseSend', array($context, $this));

        parent::send($context);
    }

    public function setInlineLayoutHtmlTemplate($template)
    {
        $this->_inlineLayoutHtmlTemplate = $template;

        return $this;
    }

    public function setLayoutHtmlTemplate($template)
    {
        $this->_layoutHtmlTemplate = $template;

        return $this;
    }

    public function setFlash(array $flash)
    {
        $this->_flash = $flash;

        return $this;
    }

    protected function _sendSuccess(SabaiFramework_Application_Context $context)
    {
        $success_url = (string)$this->_getSuccessUrl($context);
        if ($context->getRequest()->isAjax()) {
            if (!$success_url) {
                $context->setFlashEnabled(false);
            }
            if ($attributes = $context->getSuccessAttributes()) {
                foreach (array_keys($attributes) as $k) {
                    if ($attributes[$k] instanceof SabaiFramework_Application_Url) {
                        $attributes[$k]['separator'] = '&';
                        $attributes[$k] = (string)$attributes[$k];
                    }
                }
            }
            // Send success response as json
            $this->sendStatusHeader(278, 'Success');
            $this->sendHeader('Content-type', 'application/json; charset=' . $context->getCharset());
            echo json_encode(array_merge(
                $attributes,
                array(
                    'url' => $success_url,
                    'messages' => $context->getFlash(),
                )
            ));

            return;
        }

        // Redirect
        $this->sendHeader('Location', $success_url);
    }
    
    public function getError(SabaiFramework_Application_Context $context)
    {
        $url = $context->getErrorUrl();
        $messages = array();
        switch ($context->getErrorType()) {
            case Sabai_Context::ERROR_BAD_REQUEST:
                $messages[] = __('Your browser sent a request that this server could not understand.', 'sabai');
                break;

            case Sabai_Context::ERROR_UNAUTHORIZED:
                if (!$url) {
                    // Get requested URL
                    $url = $context->getRequest()->url();
                }
                $url = $this->_application->LoginUrl((string)$this->_application->Url($url));
                if ($context->getRequest()->isAjax()
                    || strpos($context->getContainer(), '#sabai-embed') === 0
                ) {
                    $messages[] = sprintf(__('You must <a href="%s">login</a> to perform the requested action.', 'sabai'), $url);
                    $url = null;
                }
                break;

            case Sabai_Context::ERROR_FORBIDDEN:
                $messages[] = __('Your request may not be processed.', 'sabai');
                break;

            case Sabai_Context::ERROR_NOT_FOUND:
                $messages[] = __('The requested page was not found.', 'sabai');
                break;

            case Sabai_Context::ERROR_METHOD_NOT_ALLOWED:
                $messages[] = __('The requested method is not allowed.', 'sabai');
                break;

            case Sabai_Context::ERROR_NOT_ACCEPTABLE:
                $messages[] = __('The requested page is not acceptable by the browser.', 'sabai');
                break;

            case Sabai_Context::ERROR_NOT_IMPLEMENTED:
                $messages[] = __('The requested method is not implemented.', 'sabai');
                break;

            case Sabai_Context::ERROR_SERVICE_UNAVAILABLE:
                $messages[] = __('The server is currently unable to handle the request. Please try again later.', 'sabai');
                break;

            case Sabai_Context::ERROR_INTERNAL_SERVER_ERROR:
            default:
                $messages[] = __('The server encountered an error processing your request.', 'sabai');
        }

        // Append detailed error message if any set
        if ($error = $context->getErrorMessage()) {
            $messages[] = $error;
        }
        
        // Always convert URL to SabaiFramework_Application_Url
        if (isset($url)) {
            $url = $this->_application->Url($url);
        }
        
        return array('url' => $url, 'messages' => $messages);
    }

    protected function _sendError(SabaiFramework_Application_Context $context)
    {
        $error = $this->getError($context);

        if ($context->getRequest()->isAjax()) {
            // Save error message as flash if redirection URL is set
            if (isset($error['url']) && !empty($error['messages'])) {
                foreach ($error['messages'] as $message) {
                    $context->addFlash($message, Sabai_Context::FLASH_ERROR);
                }
            }

            // Send error response as json
            $this->sendStatusHeader($context->getErrorType());
            $this->sendHeader('Content-type', 'application/json; charset=' . $context->getCharset());
            echo json_encode(array(
                'messages' => $error['messages'],
                'url' => (string)$error['url'],
            ));

            return;
        }

        if (!isset($error['url'])) {
            if ((string)$context->getRoute() === '/') {
                // An error occurred on the top page. Throw an exception to prevent redirection loop.
                throw new RuntimeException(__('The server encountered an error processing your request.', 'sabai'));
            }
            $error['url'] = $this->_application->Url(); // redirect to the top page
        }

        foreach ($error['messages'] as $message) {
            $context->addFlash($message, Sabai_Context::FLASH_ERROR);
        }
        $this->sendHeader('Location', $error['url']);
    }

    protected function _sendView(SabaiFramework_Application_Context $context)
    {
        $template = new Sabai_Template(
            $this->_application,
            $context->getTemplateDirs(),
            $this->_getGlobalTemplateVars($context)
        );
        
        $this->_application->doEvent('SabaiWebResponseRender', array($context, $this, $template));
        // Invoke controller specific event
        if (!$event = $context->getRoute()->getControllerEventName()) {
            $event = $context->isAdmin()
                ? $context->getRoute()->getAddon() . 'Admin' . $context->getRoute()->getControllerName()
                : $context->getRoute()->getAddon() . $context->getRoute()->getControllerName();
        }
        $this->_application->doEvent('SabaiWebResponseRender' . $event, array($context, $this, $template));

        // Make sure a template file exists, otherwise return 404 error
        if (!$context->hasTemplate()) {
            $context->setNotFoundError();
            $this->_sendError($context);

            return;
        }
        
        switch ($context->getContentType()) {
            case 'xml':
                if (!headers_sent()) {
                    $this->sendStatusHeader(200);
                    $this->sendHeader('Content-Type', sprintf('text/xml; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printXml($context, $template);
                return;

            case 'json':
                if (!headers_sent()) {
                    $this->sendStatusHeader(200);
                    $this->sendHeader('Content-Type', sprintf('application/json; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printJson($context, $template);
                return;

            default:
                if (!headers_sent()) {
                    $this->sendStatusHeader(200);
                    $this->sendHeader('Content-Type', sprintf('text/html; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printHtml($context, $template);
        }
    }

    private function _printXml(Sabai_Context $context, Sabai_Template $template)
    {
        $this->_application->doEvent('SabaiWebResponseRenderXml', array($context, $this, $template));

        echo '<?xml version="1.0" encoding="' . Sabai::h($context->getCharset()) . '"?>';
        
        $template->renderTemplate(array_reverse($context->getTemplates()), $context->getAttributes(), '.xml');
    }

    private function _printJson(Sabai_Context $context, Sabai_Template $template)
    {
        $this->_application->doEvent('SabaiWebResponseRenderJson', array($context, $this, $template));
        
        $template->renderTemplate(array_reverse($context->getTemplates()), $context->getAttributes(), '.json');
    }

    private function _printHtml(Sabai_Context $context, Sabai_Template $template)
    {
        $this->_application->doEvent('SabaiWebResponseRenderHtml', array($context, $this, $template));

        // No layout if the requested content is an HTML fragment
        if (!isset($this->_inlineLayoutHtmlTemplate) && !isset($this->_layoutHtmlTemplate)) {
            // No layout templates, so output content directly
            $template->renderTemplate(array_reverse($context->getTemplates()), $context->getAttributes());
            return;
        }
        // Fetch content
        ob_start();
        $template->renderTemplate(array_reverse($context->getTemplates()), $context->getAttributes());
        $content = ob_get_clean();

        $this->_application->doEvent('SabaiWebResponseRenderHtmlLayout', array($context, $this, &$content));
        
        $vars = $this->_getInlineLayoutTemplateVars($context, $content) + $template->getVars();
        
        // Add inline layout?
        if (isset($this->_inlineLayoutHtmlTemplate)) {
            if (!isset($this->_layoutHtmlTemplate)) {
                // No layout template, so output content directly
                $this->_include($this->_inlineLayoutHtmlTemplate, $vars);
                return;
            }
            // Fetch content with inline layout
            ob_start();
            $this->_include($this->_inlineLayoutHtmlTemplate, $vars);
            $content = ob_get_clean();
        }

        $this->_include($this->_layoutHtmlTemplate, $this->_getLayoutTemplateVars($context, $content) + $vars);
    }

    private function _getInlineLayoutTemplateVars(Sabai_Context $context, $content)
    {
        // Init inline tabs
        if ($inline_tabs = $context->getInlineTabs()) {
            // Set the first tab as current if no valid current tab specified
            if ((!$inline_tab_current = $context->getRequest()->asStr(Sabai_Request::$inlineTabParam, false))
                || !isset($inline_tabs[$inline_tab_current])
            ) {
                $inline_tab_names = array_keys($inline_tabs);
                $inline_tab_current = array_shift($inline_tab_names);
            }
        } else {
            $inline_tab_current = null;
        }
        
        $page_menu = $context->getMenus();

        return array(
            'CONTENT' => $content,
            'PAGE_TITLE' => $context->getTitle(),
            'PAGE_MENU' => $page_menu,
            'PAGE_BREADCRUMBS' => $context->getInfo(),
            'TAB_CURRENT' => $context->getCurrentTab(),
            'TABS' => $context->getTabs(),
            'TAB_MENU' => $context->getTabMenus(),
            'TAB_BREADCRUMBS' => $context->getTabInfo(),
            'INLINE_TABS' => $inline_tabs,
            'INLINE_TAB_CURRENT' => $inline_tab_current,
        );
    }

    private function _getLayoutTemplateVars(Sabai_Context $context, $content)
    {
        $prefix = $context->isAdmin() ? 'sabai-admin' : 'sabai-main';
        $classes = array();
        $route = rtrim($context->getRoute(), '/');
        do {
            $classes[] = $prefix . $route;
        } while (DIRECTORY_SEPARATOR !== $route = dirname($route));
        $classes[] = $prefix;
        $vars = array(
            'CONTENT_CLASSES' => str_replace('/', '-', implode(' ', array_reverse($classes))),
            'CONTENT' => $content,
            'CONTENT_TITLE' => $context->getTitle(),
            'CONTENT_URL' => ($url = $context->getUrl()) ? $url : $this->_application->Url($context->getRoute()),
            'CONTENT_SUMMARY' => $context->getSummary(),
            'CHARSET' => $context->getCharset(),
            'HTML_HEAD' => implode(PHP_EOL, $this->_htmlHead),
            'HTML_HEAD_TITLE' => $context->getHtmlHeadTitle(),
            'CSS' => $this->getCssHtml(),
            'JS' => $this->getJsHtml(),
            'FLASH' => $this->_flash,
        );

        return $vars;
    }

    public function getJsHtml()
    {
        $html = array();

        foreach ($this->_jsFiles as $handle => $file) {
            $this->_addJsFileHtml($html, $handle, $file);
        }
        $html[] = '<script type="text/javascript">';
        if (!empty($this->_jsRaw)) {
            ksort($this->_jsRaw);
            foreach (array_keys($this->_jsRaw) as $i) {
                $html[] = $this->_jsRaw[$i];
            }
        }
        if (!empty($this->_js)) {
            ksort($this->_js);
            $html[] = 'jQuery(document).ready(function($) {';
            foreach (array_keys($this->_js) as $i) {
                $html[] = $this->_js[$i];
            }
            $html[] = '});';
        }
        $html[] = '</script>';

        return implode(PHP_EOL, $html);
    }

    /**
     * Adds HTML for the specified JS and its required JS files by recursively resolving depndencies.
     * @param array $html
     * @param string $handle
     * @param array $file
     */
    private function _addJsFileHtml(array &$html, $handle, array $file)
    {
        if (isset($html[$handle])) return;

        // File dependencies set?
        if (isset($file['dep'])) {
            foreach ((array)$file['dep'] as $dep) {
                if (isset($html[$dep])) { // Already added to HTML?
                    continue;
                } elseif (isset($this->_jsFiles[$dep])) { // Is it available?
                    $this->_addJsFileHtml($html, $dep, $this->_jsFiles[$dep]);
                } else {
                    return; // do not add this file because dependency was not met
                }
            }
        }

        // Source may be empty when added only for resolving dependencies
        if (!strlen($src = (string)$file['src'])) {
            $script = '';
        } else {
            $script = sprintf('<script type="text/javascript" src="%s"></script>', $src);
        }

        $html[$handle] = $script;
    }

    public function getCssHtml()
    {
        $html = array();
        foreach (array_keys($this->_cssFiles) as $i) {
            $html[$i] = sprintf('<link rel="stylesheet" type="text/css" media="%s" href="%s" />', $this->_cssFiles[$i][1], $this->_cssFiles[$i][0]);
        }
        foreach (array_keys($this->_css) as $i) {
            $html[$i] = implode(PHP_EOL, array('<style type="text/css"><!--', $this->_css[$i], '--></style>'));
        }
        ksort($html);

        return implode(PHP_EOL, $html);
    }

    private function _include()
    {
        extract(func_get_arg(1), EXTR_SKIP);
        return include func_get_arg(0);
    }

    public function addHtmlHead($head)
    {
        $this->_htmlHead[] = $head;

        return $this;
    }

    public function addJsFile($src, $handle, $dependency = null)
    {
        if (!isset($this->_jsFiles[$handle])) {
            $this->_jsFiles[$handle] = array('src' => $src, 'dep' => $dependency);
        }
        return $this;
    }
        
    public function removeJsFile($handle)
    {
        unset($this->_jsFiles[$handle]);
        return $this;
    }

    public function addJs($js, $onDomReady = true, $index = null)
    {
        if (!isset($index)) {
            $index = ++$this->_jsIndex;
        }
        if ($onDomReady) {
            $this->_js[$index] = $js;
        } else {
            $this->_jsRaw[$index] = $js;
        }

        return $this;
    }

    public function addCss($css, $index = null)
    {
        if (!isset($index)) {
            $index = ++$this->_cssIndex;
        }
        $this->_css[$index] = $css;

        return $this;
    }

    public function addCssFile($path, $handle, $media = 'screen', $index = null)
    {
        if (!isset($index)) {
            $index = ++$this->_cssIndex;
        }
        // Use id to prevent duplicates
        if (isset($this->_cssFileIndices[$handle])) {
            unset($this->_cssFiles[$this->_cssFileIndices[$handle]]);
        }
        $this->_cssFileIndices[$handle] = $index;
        $this->_cssFiles[$index] = array($path, $media);

        return $this;
    }
    
    public function removeCssFile($handle)
    {
        if (isset($this->_cssFileIndices[$handle])) {
            unset($this->_cssFiles[$this->_cssFileIndices[$handle]], $this->_cssFileIndices[$handle]);
        }
        return $this;
    }
}
