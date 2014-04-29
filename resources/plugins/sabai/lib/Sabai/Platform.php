<?php
abstract class Sabai_Platform
{
    protected $_name, $_sabaiName, $_routeParam;

    protected function __construct($name, $sabaiName, $routeParam = 'q')
    {
        $this->_name = $name;
        $this->_sabaiName = $sabaiName;
        $this->_routeParam = $routeParam;
    }

    final public function getName()
    {
        return $this->_name;
    }

    final public function getSabaiName()
    {
        return $this->_sabaiName;
    }

    final public function getRouteParam()
    {
        return $this->_routeParam;
    }
    
    public function getHomeUrl()
    {
        return $this->getSiteUrl();
    }
    
    /**
     * Gets an instance of Sabai
     * @param $loadAddons bool
     * @return Sabai
     */
    abstract public function getSabai($loadAddons = true);
    /**
     * @return SabaiFramework_User_IdentityFetcher
     */
    abstract public function getUserIdentityFetcher();
    /**
     * @return SabaiFramework_User
     */
    abstract public function getCurrentUser();
    /**
     * @return array
     */
    abstract public function getUserRoles();
    /**
     * @param string $role
     * @return bool
     */
    abstract public function isSuperUserRole($role);
    /**
     * @param Sabai_UserIdentity $identity
     * @return array
     */
    abstract public function getUserRolesByUser(Sabai_UserIdentity $identity);
    /**
     * @param string|array $roleName
     * @return array Array of Sabai_UserIdentity indexed by user IDs
     */
    abstract public function getUsersByUserRole($roleName);
    abstract public function getWriteableDir();
    abstract public function getAddonPaths();
    abstract public function getSitePath();
    abstract public function getSiteName();
    abstract public function getSiteEmail();
    abstract public function getSiteUrl();
    abstract public function getSiteAdminUrl();
    abstract public function getAssetsUrl($package = null);
    abstract public function getAssetsDir($package = null);
    abstract public function getLoginUrl($redirect);
    abstract public function getLogoutUrl();
    abstract public function getUserRegisterUrl($redirect);
    abstract public function getDBConnection();
    abstract public function getDBTablePrefix();
    abstract public function mail($to, $subject, $body, array $attachments = null, $bodyHtml = null);
    abstract public function setSessionVar($name, $value, $userId = null);
    abstract public function getSessionVar($name, $userId = null);
    abstract public function deleteSessionVar($name, $userId = null);
    abstract public function setUserOption($userId, $name, $value);
    abstract public function getUserOption($userId, $name, $default = null);
    abstract public function deleteUserOption($userId, $name);
    abstract public function setCache($data, $id, $lifetime = null);
    abstract public function getCache($id);
    abstract public function deleteCache($id);
    abstract public function clearCache();
    abstract public function logInfo($info);
    abstract public function logWarning($warning);
    abstract public function logError($error);
    abstract public function getLocale();
    abstract public function isLanguageRTL();
    abstract public function setCookie($name, $value, $expire = 0);
    abstract public function getCookie($name, $default = null);
    abstract public function setOption($name, $value);
    abstract public function getOption($name, $default = null);
    abstract public function deleteOption($name);
    abstract public function getDateFormat();
    abstract public function getCustomAssetsDir();
    abstract public function getCustomAssetsDirUrl();
    abstract public function getUserProfileHtml($userId);
    abstract public function resizeImage($imgPath, $destPath, $width, $height, $crop = false);
    /**
     * @return int
     */
    abstract public function getStartOfWeek();
    /**
     * @return int
     */
    abstract public function getGMTOffset();
}
