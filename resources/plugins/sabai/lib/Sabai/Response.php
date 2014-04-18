<?php
abstract class Sabai_Response extends SabaiFramework_Application_HttpResponse
{
    /**
     * Call an application helper
     */
    public function __call($name, $args)
    {
        return $this->_application->getHelperBroker()->callHelper($name, $args);
    }

    public function send(SabaiFramework_Application_Context $context)
    {
        $this->_application->doEvent('SabaiResponseSend', array($context, $this));

        parent::send($context);

        $this->_application->doEvent('SabaiResponseSendComplete', array($context));
    }

    protected function _sendRedirect(SabaiFramework_Application_HttpContext $context)
    {
        switch ($context->getRedirectType()) {
            case Sabai_Context::REDIRECT_PERMANENT:
                $this->sendStatusHeader(301);
                $this->sendHeader('Location', (string)$this->_getRedirectUrl($context));
                return;

            case Sabai_Context::REDIRECT_TEMPORARY:
            default:
                $this->sendStatusHeader(302);
                $this->sendHeader('Location', (string)$this->_getRedirectUrl($context));
        }
    }

    protected function _getGlobalTemplateVars(Sabai_Context $context)
    {
        return array(
            'CURRENT_ROUTE' => $context->getRoute(),
            'CURRENT_CONTAINER' => $context->getContainer(),
            'CONTEXT' => $context,
            'CURRENT_USER' => $this->_application->getUser(),
            'CURRENT_ADDON' => $this->_application->getCurrentAddonName(),
            'SITE_NAME' => $this->_application->getPlatform()->getSiteName(),
            'SITE_URL' => $this->_application->getPlatform()->getHomeUrl(),
            'SITE_EMAIL' => $this->_application->getPlatform()->getSiteEmail(),
            'SITE_ADMIN_URL' => $this->_application->getPlatform()->getSiteAdminUrl(),
            'IS_MOBILE' => $this->_application->isMobile(),
            'IS_TABLET' => $this->_application->isTablet(),
        );
    }

    protected function _getSuccessUrl(Sabai_Context $context, $separator = '&')
    {
        if (!$url = $context->getSuccessUrl()) {
            $url = Sabai_Request::url(); // use the current URL
        } else {
            $url = $this->_application->Url($url); // converts to an SabaiFramework_URL object
            $url['separator'] = $separator;
        }

        return $url;
    }

    protected function _getRedirectUrl(Sabai_Context $context, $separator = '&')
    {
        if (!$url = $context->getRedirectUrl()) {
            $url = Sabai_Request::url(); // use the current URL
        } else {
            $url = $this->_application->Url($url); // convert to an SabaiFramework_URL object
            $url['separator'] = $separator;
        }

        return $url;
    }
}
