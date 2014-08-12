<?php
require_once 'SabaiFramework/Application/Http.php';

abstract class Sabai extends SabaiFramework_Application_Http
{
    public static $p;
    public static $sabai;
    private static $_instances = array();
    private $_isRunning = false, $_db,
        $_eventDispatcher, $_platform, $_user, $_currentAddonName = 'System',
        $_addons = array(), $_addonsLoaded = array(), $_addonsLoadedTimestamp,
        $_userDevice;

    // System version constants
    const VERSION = '2.0.5dev534', PACKAGE = 'sabai', PHP_VERSION_MIN = '5.2.0', PHP_VERSION_MAX = '', MYSQL_VERSION_MIN = '5.0.3';

    // Route type constants
    const ROUTE_NORMAL = 0, ROUTE_TAB = 1, ROUTE_MENU = 2, ROUTE_CALLBACK = 3, ROUTE_INLINE_TAB = 4,
        ROUTE_ACCESS_LINK = 0, ROUTE_ACCESS_CONTENT = 1,
        ROUTE_TITLE_NORMAL = 0, ROUTE_TITLE_TAB = 1, ROUTE_TITLE_TAB_DEFAULT = 2, ROUTE_TITLE_MENU = 3;

    const ADDON_NAME_REGEX = '/^[a-zA-Z][a-zA-Z0-9]{2,}$/';

    // Define various image sizes
    const THUMBNAIL_SIZE_SMALL = 24, THUMBNAIL_SIZE_MEDIUM = 50, THUMBNAIL_SIZE_LARGE = 100;

    public static function start($charset, $lang, $startSession = true, $pageParam = 'p')
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
        SabaiFramework::start($charset, $lang, $startSession);
        Sabai::$p = $pageParam;
    }
    
    public static function autoload($class)
    {
        if (0 === strpos($class, 'Sabai_') || 0 === strpos($class, 'SabaiFramework_')) {
            include str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        }
    }

    public static function started()
    {
        return SabaiFramework::started();
    }

    public static function exists($name)
    {
        return isset(self::$_instances[$name]) ? self::$_instances[$name] : false;
    }

    public static function create(Sabai_Platform $platform, $class = 'Sabai_Web')
    {

        $sabai = new $class($platform);
        $sabai->_init();
        self::$_instances[$platform->getSabaiName()] = $sabai;
        Sabai::$sabai = $sabai;
        return $sabai;
    }

    protected function __construct(Sabai_Platform $platform)
    {
        parent::__construct($platform->getSabaiName(), $platform->getRouteParam());
        $this->_platform = $platform;
    }

    private function _init()
    {
        $this->_db = SabaiFramework_DB::factory($this->_platform->getDBConnection(), $this->_platform->getDBTablePrefix());
        $helper_broker = new Sabai_HelperBroker($this);
        $helper_broker->addHelperDir(dirname(__FILE__) . '/Sabai/Helper', 'Sabai_Helper_');
        $this->setHelperBroker($helper_broker);
        $this->_eventDispatcher = new Sabai_EventDispatcher($this);
        
        if (!$user_device = $this->_platform->getCookie('sabai_user_device')) {
            if (!class_exists('Mobile_Detect')) {
                require 'Mobile/Detect.php';
            }
            $md = new Mobile_Detect();
            if ($md->isMobile()) {
                $user_device = 'mobile';
            } elseif ($md->isTablet()) {
                $user_device = 'tablet';
            } else {
                $user_device = 'pc';
            }
            $this->_platform->setCookie('sabai_user_device', $user_device, time() + 2592000); // cache for 30 days
        }
        $this->_userDevice = $user_device;
    }

    public function loadAddons()
    {
        if (!isset($this->_addonsLoadedTimestamp)) {
            $this->_loadAddons();
        }

        return $this;
    }

    public function reloadAddons($clearObjectCache = true)
    {
        if ($clearObjectCache) {
            $this->_addons = array();
        }
        $this->_loadAddons(true);

        return $this;
    }

    public function run(SabaiFramework_Application_Controller $controller, SabaiFramework_Application_Context $context, $route = null)
    {
        $this->_isRunning = true;

        // Invoke SabaiRun event
        $this->doEvent('SabaiRun', array($context, $controller));

        $response = parent::run($controller, $context, $route);

        // Invoke SabaiRunComplete event
        $this->doEvent('SabaiRunComplete', array($context));

        return $response;
    }

    public function isRunning()
    {
        return $this->_isRunning;
    }

    public function isPrimary()
    {
        return $this->_platform->getSabaiName() === 'sabai';
    }
    
    public function isMobile()
    {
        return $this->_userDevice === 'mobile';
    }
    
    public function isTablet()
    {
        return $this->_userDevice === 'tablet';
    }    

    /**
     *
     * @return Sabai_Platform
     */
    public function getPlatform()
    {
        return $this->_platform;
    }

    /**
     *
     * @return SabaiFramework_DB 
     */
    public function getDB()
    {
        return $this->_db;
    }

    public function getDBSchema()
    {
        return SabaiFramework_DB_Schema::factory($this->getDB());
    }

    public function getLibDir()
    {
        return $this->Path(dirname(__FILE__));
    }

    public function setUser(SabaiFramework_User $user)
    {
        $user_changed = $this->_user && $this->_user->id !== $user->id;
        // Notify that the current user object has been initialized
        $this->doEvent('SabaiUserInitialized', array($user, $user_changed));
        
        $this->_user = $user;
        
        return $this;
    }

    /**
     * @return SabaiFramework_User
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            // Initialize the current user object if not any set
            if ($user = $this->_platform->getCurrentUser()) {
                $user->setAdministrator($this->_platform->isAdministrator($user->getIdentity()));
            } else {
                $user = new SabaiFramework_User($this->_platform->getUserIdentityFetcher()->getAnonymous());
            }
            // Start user session
            SabaiFramework::startSession();
            $this->setUser($user);
        }
        
        return $this->_user;
    }

    public function setCurrentAddon($addon)
    {
        $this->_currentAddonName = (string)$addon;

        return $this;
    }

    public function getCurrentAddonName()
    {
        return $this->_currentAddonName;
    }

    public function getAddonsLoadedTimestamp()
    {
        return $this->_addonsLoadedTimestamp;
    }

    public function isAddonLoaded($addonName)
    {
        return isset($this->_addonsLoaded[$addonName]);
    }

    /**
     * A shortcut method for fetching a addon object
     * @param string $addonName
     * @return Sabai_Addon
     */
    public function getAddon($addonName = null)
    {
        if (!isset($addonName)) $addonName = $this->_currentAddonName;

        if (!isset($this->_addons[$addonName])) {
            // Create addon
            $addon = $this->_createAddon($addonName);
            // Let the addon have chance to initialize itself
            $addon->init($this->_addonsLoaded[$addonName]['config']);
            // Notify that a addon has been initialized
            $this->doEvent('SabaiAddonInitialized', array($addon)); // global
            $this->doEvent($addonName . 'Initialized', array($addon)); // addon specific
            // Add to memory cache
            $this->_addons[$addonName] = $addon;
        }

        return $this->_addons[$addonName];
    }
    
    /**
     * Gets a addon which is not yet installed
     *
     * @param string $addonName
     * @param array $config
     * @return Sabai_Addon
     */
    public function fetchAddon($addonName, array $config = array())
    {
        $addon = $this->_createAddon($addonName);
        // Let the addon have chance to initialize itself
        $addon->init($config + $addon->getDefaultConfig());
        
        return $addon;
    }
    
    private function _createAddon($addonName)
    {
        // Instantiate addon
        $addon_file_path = $this->getAddonPath($addonName) . '.php';
        if (!@include_once $addon_file_path) {
            // Reload add-on data and try again
            $this->reloadAddonData($addonName);
            if (!@include_once $addon_file_path) {
                throw new Sabai_AddonNotFoundException('Add-on file for add-on ' . $addonName . ' was not found at ' . $addon_file_path);
            }
        }
        $reflection = new ReflectionClass('Sabai_Addon_' . $addonName);
        return $reflection->newInstanceArgs(array($addonName, $this));       
    }

    /**
     * Returns the full path to a addon directory
     * @param string $addonName
     * @return string
     */
    public function getAddonPath($addonName)
    {
        $path = $this->_getAddonData($addonName, 'path');
        return isset($path) ? $this->SitePath() . $path : $this->getLibDir() . '/Sabai/Addon/' . $addonName;
    }
    
    public function getAddonPackage($addonName)
    {
        return $this->_getAddonData($addonName, 'package');
    }
    
    protected function _getAddonData($addonName, $key)
    {
        if (!isset($this->_addonsLoaded[$addonName])) {
            // Fetch from local file
            $local_addons = $this->getLocalAddons();
            if (isset($local_addons[$addonName])) {
                return $local_addons[$addonName][$key];
            }
            // No local file data, so force reload
            $this->reloadAddonData($addonName);
        }
        return $this->_addonsLoaded[$addonName][$key];        
    }
    
    protected function reloadAddonData($addonName)
    {
        $this->_loadAddons(true);
        if (isset($this->_addonsLoaded[$addonName])) {
            return;
        }
        // Can't find the add-on. Generate the add-on file if it is a cloned add-on
        if ($addonName !== 'System'
            && ($addon_info = $this->getAddon('System')->getModel('Addon')->name_is($addonName)->fetchOne()) 
            && $addon_info->parent_addon
            && isset($this->_addonsLoaded[$addon_info->parent_addon])
            && $this->CloneAddon($addon_info->parent_addon, $addonName)
        ) {
            // Load the cloned add-on
            $this->_loadAddons(true);
            if (isset($this->_addonsLoaded[$addonName])) {
                return;
            }
        }
        throw new Sabai_AddonNotInstalledException('The following add-on is not installed or loaded: ' . $addonName);
    }

    public function doEvent($eventName, array $eventArgs = array())
    {
        $this->_eventDispatcher->dispatch($eventName, $eventArgs);

        return $this;
    }

    public function doFilter($filterName, $filterValue, array $filterArgs = array())
    {
        if (is_object($filterValue)) {
            array_unshift($filterArgs, $filterValue);
        } else {
            // Pass in the value as reference so it can be altered
            $filterArgs = array_merge(array(&$filterValue), $filterArgs);
        }
        // Dispatch filter event
        $this->_eventDispatcher->dispatch($filterName . 'Filter', $filterArgs);

        return $filterValue; // return the altered value
    }

    /**
     * A shortcut method for fetching the model object of the current running addon
     * @param string $modelName
     * @param string $addonType
     * @return Sabai_Model
     */
    public function getModel($modelName = null, $addonType = null)
    {
        return $this->getAddon($addonType)->getModel($modelName);
    }

    public function getInstalledAddons($force = false)
    {
        if ($force || (false === $installed_addons = $this->_platform->getCache('sabai_addons_installed'))) {
            try {
                $addons = $this->fetchAddon('System')->getModel('Addon')->fetch(0, 0, 'priority', 'DESC');
            } catch (SabaiFramework_DB_QueryException $e) {
                // Probably Sabai has not been installed yet
                return array();
            }

            $installed_addons = array();
            foreach ($addons as $addon) {
                $installed_addons[$addon->name] = array(
                    'version' => $addon->version,
                    'config' => $addon->getParams(false),
                    'events' => $addon->events,
                    'parent' => $addon->parent_addon,
                );
            }
            $this->_platform->setCache($installed_addons, 'sabai_addons_installed');
        }

        return $installed_addons;
    }

    public function getInstalledAddon($addonName, $force = false)
    {
        $addons = $this->getInstalledAddons($force);

        return isset($addons[$addonName]) ? $addons[$addonName] : false;
    }

    public function getInstalledAddonInterfaces($force = false)
    {
        $local = $this->getLocalAddons($force);
        $addons = $this->getInstalledAddons($force);
        $data = array();
        foreach (array_keys($addons) as $addon_name) {
            if (!empty($local[$addon_name]['interfaces'])) {
                foreach ($local[$addon_name]['interfaces'] as $interface) {
                    $data[$interface][$addon_name] = $addon_name;
                }
            }
        }

        return $data;
    }

    public function getInstalledAddonsByInterface($interface, $force = false)
    {
        $interfaces = $this->getInstalledAddonInterfaces($force);

        return isset($interfaces[$interface]) ? $interfaces[$interface] : array();
    }

    private function _loadAddons($force = false)
    {
        if ($force || (false === $data = $this->_platform->getCache('sabai_addons_loaded'))) {
            $data = array('addons' => array(), 'timestamp' => time());
            $this->_eventDispatcher->clear();
            $local = $this->getLocalAddons($force);
            $installed_addons = $this->getInstalledAddons($force);
            foreach (array_keys($installed_addons) as $addon_name) {
                if ($addon_data = @$local[$addon_name]) {
                    $data['addons'][$addon_name] = array(
                        'config' => $installed_addons[$addon_name]['config'],
                        'events' => $installed_addons[$addon_name]['events'],
                        'parent' => $installed_addons[$addon_name]['parent'],
                        'path' => $addon_data['path'],
                        'package' => $addon_data['package'],
                    );
                }
            }
            $this->_platform->setCache($data, 'sabai_addons_loaded', 0);
        }

        if (empty($data['addons'])) {
            throw new Sabai_NotInstalledException();
        }

        $this->_addonsLoaded = array();
        $this->_addonsLoadedTimestamp = $data['timestamp'];
        
        // Load addons
        foreach ($data['addons'] as $addon_name => $addon_data) {
            $this->_addonsLoaded[$addon_name] = array(
                'path' => $addon_data['path'],
                'config' => $addon_data['config'],
                'package' => $addon_data['package'],
                'parent' => @$addon_data['parent'], // suppress error for backward compat with < 1.1.4
            );
            if (!empty($addon_data['events'])) {
                foreach ($addon_data['events'] as $event) {
                    if (is_array($event)) {
                        $event_name = $event[0];
                        $event_priority = $event[1];
                    } else {
                        $event_name = $event;
                        $event_priority = 10;
                    }
                    $this->_eventDispatcher->addListener($event_name, $addon_name, $event_priority);
                }
            }
        }

        // Invoke AddonLoaded event for each addon
        foreach (array_keys($this->_addonsLoaded) as $addon_name) {
            $this->doEvent('Sabai' . $addon_name . 'AddonLoaded');
        }

        // Invoke SabaiAddonsLoaded event
        $this->doEvent('SabaiAddonsLoaded');
    }

    public function getLocalAddons($force = false)
    {
        if ($force || (false === $addons = $this->_platform->getCache('sabai_addons_local'))) {
            $addons = array();
            $lib_dir = $this->Path(dirname(__FILE__));
            // Get paths to available addons
            $directories = $this->_platform->getAddonPaths();
            // Add directory where cloned add-on files should be placed
            $directories[] = $this->_platform->getWriteableDir() . '/System/clones';
            // Add the core path
            array_unshift($directories, $lib_dir . '/Sabai/Addon');
            $platform_root = $this->SitePath();
            foreach ($directories as $directory) {
                $directory = $this->Path($directory);
                if ($platform_root && strpos($directory, $platform_root) !== 0) {
                    // Directory must be within the application platform directory
                    continue;
                }
                if (!$files = glob($directory . '/*.php', GLOB_NOSORT)) {
                    continue;
                }
                foreach ($files as $file) {
                    $addon_name = basename($file, '.php');
                    // Skip addons without a valid name or if already set
                    if (isset($addons[$addon_name])
                        || !preg_match(self::ADDON_NAME_REGEX, $addon_name)
                    ) continue;

                    require_once $file;
                    $addon_class = 'Sabai_Addon_' . $addon_name;
                    if (!class_exists($addon_class, false)) continue;

                    $addons[$addon_name] = array(
                        'version' => constant($addon_class . '::VERSION'),
                        'package' => constant($addon_class . '::PACKAGE'),
                        'interfaces' => class_implements($addon_class, false),
                        'path' => strpos($directory, $lib_dir) === 0
                            ? null
                            : substr($directory, strlen($platform_root)) . '/' . $addon_name, // remove the root part
                    );
                }

                ksort($addons);
                $this->_platform->setCache($addons, 'sabai_addons_local');
            }
        }

        return $addons;
    }
    
    /**
     * Alias for htmlspecialchars()
     *
     * @param string $str
     * @param int $quoteStyle
     * @param bool $doubleEncode
     * @return string
     */
    public static function h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
    {
        return htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
    }

    /**
     * Echos out the result of htmlspecialchars()
     *
     * @param string $str
     * @param int $quoteStyle
     * @param bool $doubleEncode
     * @return string
     */
    public static function _h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
    {
        echo htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
    }
}
