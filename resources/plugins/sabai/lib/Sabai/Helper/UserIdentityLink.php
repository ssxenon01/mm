<?php
class Sabai_Helper_UserIdentityLink extends Sabai_Helper
{
    private $_links;

    /**
     * Creates an HTML link of a user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if ($identity->isAnonymous()) {
            return $identity->url
                ? sprintf('<a href="%s" target="_blank" rel="nofollow external">%s</a>', Sabai::h($identity->url), Sabai::h($identity->name))
                : Sabai::h($identity->name);
        }

        $id = $identity->id;
        if (!isset($this->_links[$id])) {
            $url = $application->UserIdentityUrl($identity);
            $this->_links[$id] = $url
                ? $application->LinkTo($identity->name, $url, array(), array('rel' => 'nofollow', 'data-popover-url' => $application->MainUrl('/sabai/user/profile/' . $identity->username)))
                : Sabai::h($identity->name);
        }

        return $this->_links[$id];
    }
}