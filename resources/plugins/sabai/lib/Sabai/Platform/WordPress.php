<?php
require_once 'Sabai/Platform.php';

class Sabai_Platform_WordPress extends Sabai_Platform
{
    const VERSION = '1.2.30';
    private $_mainContent, $_mainRoute, $_template, $_userToBeDeleted, $_sessionTransient = true, $_sessionTransientLifetime = 1800;
    private static $_instances = array();

    protected function __construct($pluginName)
    {
        parent::__construct('WordPress', $pluginName);
    }

    public static function getInstance($pluginName)
    {
        if (!isset(self::$_instances[$pluginName])) {
            self::$_instances[$pluginName] = new self($pluginName);
            if (defined('SABAI_WORDPRESS_SESSION_TRANSIENT')) {
                self::$_instances[$pluginName]->_sessionTransient = (bool)SABAI_WORDPRESS_SESSION_TRANSIENT;
            }
            if (self::$_instances[$pluginName]->_sessionTransient && defined('SABAI_WORDPRESS_SESSION_TRANSIENT_LIFETIME')) {
                self::$_instances[$pluginName]->_sessionTransientLifetime = (int)SABAI_WORDPRESS_SESSION_TRANSIENT_LIFETIME;
            }
        }
        return self::$_instances[$pluginName];
    }

    public function getUserIdentityFetcher()
    {
        return new Sabai_Platform_WordPress_UserIdentityFetcher(__('Guest', 'sabai'));
    }

    public function getCurrentUser()
    {
        $wp_user = wp_get_current_user();
        if ($wp_user->ID == 0) return false;

        $thumbnail_large = $thumbnail_medium = $thumbnail_small = '';
        $avatar_default = get_option('avatar_default');
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_LARGE, $avatar_default), $matches)) {
            $thumbnail_large = $matches[1];
        }
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_MEDIUM, $avatar_default), $matches)) {
            $thumbnail_medium = $matches[1];
        }
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_SMALL, $avatar_default), $matches)) {
            $thumbnail_small = $matches[1];
        }
        return new SabaiFramework_User(new Sabai_UserIdentity(
            $wp_user->ID,
            $wp_user->user_login,
            array(
                'name' => $wp_user->display_name,
                'email' => $wp_user->user_email,
                'url' => $wp_user->user_url,
                'created' => strtotime($wp_user->user_registered),
                'thumbnail_large' => $thumbnail_large,
                'thumbnail_medium' => $thumbnail_medium,
                'thumbnail_small' => $thumbnail_small,
            )
        ));
    }

    public function getUserRoles()
    {
        global $wp_roles;

        if (!isset($wp_roles)) $wp_roles = new WP_Roles();

        return $wp_roles->get_names();
    }
    
    public function isAdministrator(Sabai_UserIdentity $identity)
    {
        return is_super_admin($identity->id) || user_can($identity->id, 'manage_sabai_content') || user_can($identity->id, 'manage_sabai');
    }

    public function isSuperUserRole($roleName)
    {
        $role = get_role($roleName);
        if (!is_object($role)) return;
        // WP returns true in the is_super_admin() function when
        // the user being checked has the delete_users capability
        return $role->has_cap('delete_users');
    }

    public function getUserRolesByUser(Sabai_UserIdentity $identity)
    {
        $wp_user = new WP_User($identity->id);

        return $wp_user->roles;
    }
    
    public function getUsersByUserRole($roleName)
    {
        $ret = array();
        $avatar_default = get_option('avatar_default');
        $avatar_rating = get_option('avatar_rating');
        foreach ((array)$roleName as $role_name) {
            foreach (get_users(array('role' => $role_name)) as $user) {
                if (!isset($ret[$user->ID])) {
                    $ret[$user->ID] = new Sabai_Platform_WordPress_UserIdentity($user, $avatar_default, $avatar_rating);
                }
            }
        }

        return $ret;
    }
    
    public function getAddonPaths()
    {
        return ($dirs = glob(WP_PLUGIN_DIR . '/sabai-*/lib', GLOB_ONLYDIR | GLOB_NOSORT)) ? $dirs : array();   
    }

    public function getWriteableDir()
    {
        return WP_CONTENT_DIR . '/sabai';
    }
        
    public function getSitePath()
    {
        return rtrim(ABSPATH, '/');
    }

    public function getSiteName()
    {
        return get_option('blogname');
    }

    public function getSiteEmail()
    {
        return get_option('admin_email');
    }

    public function getSiteUrl()
    {
        return site_url();
    }

    public function getSiteAdminUrl()
    {
        return rtrim(admin_url(), '/');
    }

    public function getAssetsUrl($package = null)
    {
        return plugins_url() . '/' . (isset($package) ? $package : $this->getSabaiName()) . '/assets';
    }

    public function getAssetsDir($package = null)
    {
        return WP_PLUGIN_DIR . '/' . (isset($package) ? $package : $this->getSabaiName()) . '/assets';
    }
    
    public function getLoginUrl($redirect)
    {
        return wp_login_url($redirect);
    }

    public function getLogoutUrl()
    {
        return wp_logout_url();
    }

    public function getUserRegisterUrl($redirect)
    {
        return site_url('/wp-login.php?action=register&redirect_to=' . rawurlencode($redirect));
    }
    
    public function getHomeUrl()
    {
        return home_url();
    }

    public function getDBConnection()
    {
        return new Sabai_Platform_WordPress_DBConnection();
    }

    public function getDBTablePrefix()
    {
        return $GLOBALS['wpdb']->prefix . 'sabai_';
    }

    public function mail($to, $subject, $body, array $attachments = null, $bodyHtml = null)
    {
        $headers = array(sprintf(
            'From: %s <%s>',
            $this->getSiteName(),
            $this->getSiteEmail()
        ));

        // Attachments?
        if (isset($attachments)) {
            foreach (array_keys($attachments) as $i) {
                // wp_mail() accepts file path only
                $attachments[$i] = $attachment[$i]['path'];
            }
        }

        $result = wp_mail($to, $subject, $body, implode("\n", $headers), $attachments);

        return $this;
    }

    public function setSessionVar($name, $value, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if ($this->_sessionTransient) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return $this;
                }
                $name .= ':' . $userId;
            }
            $this->setCache($value, 'session_' . $name, $this->_sessionTransientLifetime);
        } else {
            $_SESSION[$this->_sabaiName][$name] = $value;
        }
        return $this;
    }

    public function getSessionVar($name, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if ($this->_sessionTransient) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return;
                }
                $name .= ':' . $userId;
            }
            $ret = $this->getCache('session_' . $name);
            return $ret === false ? null : $ret;
        }
        return isset($_SESSION[$this->_sabaiName][$name])
            ? $_SESSION[$this->_sabaiName][$name]
            : null;
    }

    public function deleteSessionVar($name, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if ($this->_sessionTransient) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return;
                }
                $name .= ':' . $userId;
            }
            $this->deleteCache('session_' . $name);
        } else {
            if (isset($_SESSION[$this->_sabaiName][$name])) {
                unset($_SESSION[$this->_sabaiName][$name]);
            }
        }

        return $this;
    }

    public function setUserOption($userId, $name, $value)
    {
        update_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_' . $this->_sabaiName . '_' . $name, $value);

        return $this;
    }

    public function getUserOption($userId, $name, $default = null)
    {
        $ret = get_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_' . $this->_sabaiName . '_' . $name, true);
        return $ret === '' ? $default : $ret;
    }

    public function deleteUserOption($userId, $name)
    {
        delete_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_' . $this->_sabaiName . '_' . $name);

        return $this;
    }

    public function getCache($id)
    {
        return get_transient('sabai_' . md5($this->_sabaiName . '_' . $id));
    }

    public function setCache($data, $id, $lifetime = null)
    {
        // Always set expiration to prevent this cache data from being autoloaded on every request by WP.
        // Lifetime can be set to 0 to never expire but the value will be autoloaded on every request.
        if (!isset($lifetime)) {
            $lifetime = 604800;
        }
        set_transient('sabai_' . md5($this->_sabaiName . '_' . $id), $data, $lifetime);

        return $this;
    }

    public function deleteCache($id)
    {
        delete_transient('sabai_' . md5($this->_sabaiName . '_' . $id));

        return $this;
    }
    
    public function clearCache()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_transient_sabai_%')");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_transient_timeout_sabai_%')");
        // delete plugin autoupdate remote info cache
        delete_site_transient('sabai_plugin_info');
        
        return $this;
    }

    public function logInfo($info)
    {
        error_log(sprintf('[%s][INFO] %s', $this->_sabaiName, $info) . PHP_EOL);
        return $this;
    }

    public function logWarning($warning)
    {
        error_log(sprintf('[%s][WARNING] %s', $this->_sabaiName, $warning) . PHP_EOL);
        return $this;
    }

    public function logError($error)
    {
        error_log(sprintf('[%s][ERROR] %s', $this->_sabaiName, $error) . PHP_EOL);
        return $this;
    }
    
    public function getLocale()
    {
        return get_locale();
    }
    
    public function isLanguageRTL()
    {
        // for some reason the is_rtl() function is not available on certain installs
        return function_exists('is_rtl') ? is_rtl() : false;
    }
    
    public function getCookie($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }
    
    public function setCookie($name, $value, $expire = 0)
    {
        @setcookie($name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, false, true);
        return $this;
    }
    
    public function setOption($name, $value)
    {
        $this->_updateOption('sabai_' . strtolower($this->_sabaiName . '_' . $name), $value);
        return $this;
    }
    
    public function getOption($name, $default = null)
    {
        return get_option('sabai_' . strtolower($this->_sabaiName . '_' . $name), $default);
    }
    
    public function deleteOption($name)
    {
        delete_option('sabai_' . strtolower($this->_sabaiName . '_' . $name));
    }
    
    public function getDateFormat()
    {
        return get_option('date_format');
    }
    
    public function getStartOfWeek()
    {
        return (int)get_option('start_of_week');
    }
    
    public function getGMTOffset()
    {
        return (int)get_option('gmt_offset');
    }
    
    public function getCustomAssetsDir()
    {
        return WP_CONTENT_DIR  . '/sabai/assets';
    }
    
    public function getCustomAssetsDirUrl()
    {    
        return WP_CONTENT_URL . '/sabai/assets';
    }
    
    public function getUserProfileHtml($userId)
    {
        return nl2br(get_the_author_meta('description', $userId));
    }
    
    public function resizeImage($imgPath, $destPath, $width, $height, $crop = false)
    {
        $img = wp_get_image_editor($imgPath);
        if (!is_wp_error($img)) {
            $img->resize($width, $height, $crop);
            $img->save($destPath);
        }
    }

    public function run()
    {
        $this->_addFiltersAndActions();

        if (is_admin()) {
            $this->_admin();

            return;
        }

        if (false === $this->_main()) {
            // Not a Sabai page

            // Add sabai stylesheet
            add_action('wp_print_styles', array($this, 'onWpPrintStylesAction'));
        }
    }

    private function _main()
    {
        // Do not run Sabai if not using the pretty permalinks
        if (!$permalink_structure = get_option('permalink_structure')) return false;

        $sabai_page_requested = false;
        $site_path = parse_url(home_url(), PHP_URL_PATH);
        // Some sites have *.php in their custom permalink URL
        if ($pos = strpos($permalink_structure, '.php')) {
            $site_path .= substr($permalink_structure, 0, $pos + 4);
        }
        $request_path = $this->_getRequestPath();
        if ($site_path) {
            if (0 !== strpos($request_path, $site_path))  {// is a valid path requested?
                // Sabai page was not requested, so clear flash messages that might
                // have been saved in the session during previous requests.
                if ($user_id = get_current_user_id()) {
                    $this->deleteSessionVar('system_flash', $user_id);
                }
                return false;
            }
            $page_request_path = substr($request_path, strlen($site_path)); // get the requested page path
        } else {
            $page_request_path = $request_path;
        }
        if ($page_request_path === '/') {
            // Check if the Sabai page is configured as the front page
            if (($front_page_id = get_option('page_on_front'))
                && ($sabai_page_slug = $this->_isSabaiPageId($front_page_id))
            ) {
                $sabai_page_requested = '';
                $page_request_path = '/' . $sabai_page_slug;
            }
        } else {
            // Normal page has been requested, check if it is a Sabai page
            $post_type = null;
            if (false !== $sabai_page_slug = $this->_isSabaiPagePath($page_request_path, $post_type)) {
                $sabai_page_requested = '/' . $sabai_page_slug;
                if (strpos(trim($page_request_path, '/'), '/')) {
                    // Do not redirect if not top page
                    remove_action('template_redirect', 'redirect_canonical');
                }
            }
        }
        if (false === $sabai_page_requested) {
            // Sabai page was not requested, so clear flash messages that might
            // have been saved in the session during previous requests.
            if ($user_id = get_current_user_id()) {
                $this->deleteSessionVar('system_flash', $user_id);
            }
            return false;
        }

        // Set the requested Sabai route
        $this->_mainRoute = $page_request_path;

        if (!isset($post_type)
           || !get_page_by_path(basename($page_request_path), null, $post_type) // check if custom post type page requested
        ) {
            // Prepare REQUEST_URI for WordPress core to fetch page
            $_SERVER['ORIG_REQUEST_URI'] = $_SERVER['REQUEST_URI']; // save original
            // http_build_query does urlencode, so need a little adjustment for RFC1738 compat
            $_SERVER['REQUEST_URI'] = sprintf('%s%s/?%s', $site_path, $sabai_page_requested, strtr(http_build_query($_GET), array('%7E' => '~', '+' => '%20')));
            $_SERVER['PATH_INFO'] = '';
        }

        // Add filter to make sure request parameters are not included in WP query vars
        add_filter('request', array($this, 'onRequestFilter'));
        // Add action method to run Sabai
        add_action('wp', array($this, 'onWpAction'), 1);
        
        // Removes rel="next" links
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        // Stops 301 rediretion loop
        remove_filter('template_redirect', 'redirect_canonical');

        // Do not redirect using 404 Redirection plugin
        remove_action('wp', 'redirect_all_404s', 1);
    }

    private function _isSabaiPageId($id)
    {
        $page_slugs = $this->getSabaiOption('page_slugs', array());
        if (is_array($page_slugs[2])
            && ($slug = array_search($id, $page_slugs[2]))
        ) {
            return $slug;
        }
        return false;
    }

    private function _isSabaiPagePath($path, &$postType = null)
    {
        $slug = trim($path, '/');
        if (strpos($slug, 'sabai') === 0) {
            return 'sabai';
        }
        if (!$slug) {
            return false;
        }
        if ($post_types = $this->getSabaiOption('post_types')) {
            $_slug = $slug;
            do {
                if (isset($post_types['slug'][$_slug])) {
                    $postType = $post_types['slug'][$_slug];
                    return $_slug;
                }
            } while (($_slug = dirname($_slug)) && strlen($_slug) > 1);
        }
        if ($page_slugs = $this->getSabaiOption('page_slugs')) {
            do {
                if (isset($page_slugs[0][$slug])
                    && !empty($page_slugs[2][$slug]) // make sure page ID is set so that page exists
                    && 'publish' === get_post_status($page_slugs[2][$slug]) // make sure the page is published
                ) {
                    return $page_slugs[0][$slug];
                }
            } while (($slug = dirname($slug)) && strlen($slug) > 1);
        }
        
        return false;
    }

    private function _getRequestPath()
    {
        if (strpos($_SERVER['REQUEST_URI'], 'wp-load.php')) {
            return @parse_url(wp_get_referer(), PHP_URL_PATH);
        }

        if (strpos($_SERVER['SCRIPT_FILENAME'], 'index.php')) {
            $ret = $_SERVER['REQUEST_URI'];

            // Remove the GET variables from the request URI if any
            if (false !== $pos = strpos($ret, '?')) {
                $ret = substr($ret, 0, $pos);
            }

            return $ret;
        }

        return false;
    }

    public function onRequestFilter($queryVars)
    {
        // Prevent WP from using Sabai request parameters to determine the requested page
        return array_diff_key($queryVars, $_REQUEST);
    }

    public function onWpAction()
    {
        $GLOBALS['wp_query']->is_404 = false;
        $this->_mainContent = $this->_runMain($this->getSabai(), $this->_mainRoute);
        remove_all_filters('the_content');
        add_filter('the_content', array($this, 'onTheContentFilter'), 99999);
    }

    private function _runMain(Sabai $sabai, $route = null)
    {
        // Create request
        $request = new Sabai_Request(true, true); // force stripslashes since WP adds them vis wp_magic_quotes() if magic_quotes_gpc is off
        // Create context
        $context = new Sabai_Context();
        $context->setRequest($request)->addTemplateDir($this->getAssetsDir() . '/templates');
        // Run Sabai
        try {
            $response = $sabai->run(new Sabai_MainRoutingController(), $context, $route);
            if (!$context->isView()
                || $request->isAjax()
                || $context->getContainer() !== '#sabai-content'
                || $context->getContentType() !== 'html'
            ) {
                if (!$request->isAjax()
                    && $context->isError()
                    && $context->getErrorType() == 404
                    && ($template_404 = get_404_template())
                ) {
                    $response->sendStatusHeader(404);
                    include $template_404;
                } else {
                    if ($context->getContainer() === '#sabai-content') {
                        $response->setInlineLayoutHtmlTemplate(dirname(__FILE__) . '/WordPress/layout/main_inline.html.php');
                    }
                    $response->send($context); // no HTML content or layout
                }
                exit;
            }
            ob_start();
            $layout_dir = dirname(__FILE__) . '/WordPress/layout';
            $response->setInlineLayoutHtmlTemplate($layout_dir . '/main_inline.html.php')
                ->setLayoutHtmlTemplate($layout_dir . '/main.html.php')
                ->send($context);
            return ob_get_clean();
        } catch (Exception $e) {
            if (is_super_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
                // Print trace
                return sprintf('<p>%s</p><p><pre>%s</pre></p>', Sabai::h($e->getMessage()), Sabai::h($e->getTraceAsString()));
            }

            return sprintf('<p>%s</p>', 'An error occurred while processing the request. Please contact the administrator of the website for further information.');
        }
    }

    private function _admin()
    {        
        // Do not include WP admin header automatically if sabai admin page
        if (isset($_REQUEST['page']) && 0 === strpos($_REQUEST['page'], $this->_sabaiName)) {
            $_GET['noheader'] = 1;
            // Get valid WP admin page for the requested Sabai route
            if (isset($_REQUEST['q'])
                && ($admin_menus = $this->getSabaiOption('admin_menus'))
            ) {
                $path = $_REQUEST['q'];
                do {
                    if (isset($admin_menus[$path])) {
                        $_GET['page'] = $_REQUEST['page'] = 'sabai' . $path;
                        break;
                    } 
                } while (DIRECTORY_SEPARATOR !== $path = dirname($path));
            }
        }

        add_action('admin_print_styles', array($this, 'onAdminPrintStylesAction'));
        add_action('admin_menu', array($this, 'onAdminMenuAction'));
        add_action('admin_notices', array($this, 'onAdminNoticesAction'));
        
        if (function_exists( 'members_get_capabilities')) {
            add_filter('members_get_capabilities', array($this, 'onMembersGetCapabilitiesFilter'));
        }
    }

    public function onMembersGetCapabilitiesFilter($caps)
    {
        // Add sabai capablities to the list of capabilities in the Members plugin 
        $caps[] = 'manage_sabai_content';
        $caps[] = 'manage_sabai';
        return $caps;
    }
    
    public function getSabaiPlugins($force = false)
    {
        if ($force || false === $sabai_plugin_names = $this->getCache('wordpress_sabai_plugins')) {
            $sabai_plugin_names = array();
            if ($sabai_plugin_dirs = glob(WP_PLUGIN_DIR . '/sabai-*', GLOB_ONLYDIR | GLOB_NOSORT)) {
                foreach ($sabai_plugin_dirs as $sabai_plugin_dir) {
                    $sabai_plugin_name = basename($sabai_plugin_dir);
                    $sabai_plugin_names[] = $sabai_plugin_name;
                }
            }
            $this->setCache($sabai_plugin_names, 'wordpress_sabai_plugins');
        }
        return $sabai_plugin_names;
    }

    public function onAdminMenuAction()
    {
        // Allow super users and users with the manage_sabai capability to access sabai settings page
        add_options_page('Sabai', 'Sabai', current_user_can('install_plugins') ? 'install_plugins' : 'manage_sabai', 'sabai/settings', array($this, 'runAdmin'));
        
        // Allow super users and users with the manage_sabai_content capability to access sabai content administration pages
        if (current_user_can('install_plugins')) {
            $capability = 'install_plugins';
        } elseif (current_user_can('manage_sabai_content')) {
            $capability = 'manage_sabai_content';
        } else {
            return;
        }
        
        $admin_menus = $this->getSabaiOption('admin_menus');
        if ($admin_menus && !empty($admin_menus['/']['children'])) {
            $position = 26.583425;
            foreach ($admin_menus['/']['children'] as $route) {
                if (!isset($admin_menus[$route])) continue;
                
                if (in_array($route, array('/settings'))) {
                    continue;
                }
            
                $menu = $admin_menus[$route];
                $label = isset($menu['label']) ? $menu['label'] : $menu['title'];
                
                $position += 0.000001;
                add_menu_page($label, $label, $capability, 'sabai' . $route, array($this, 'runAdmin'), isset($menu['icon']) ? 'div' : '', (string)$position);
                add_submenu_page('sabai' . $route, $menu['title'], $menu['title'], $capability, 'sabai' . $route, array($this, 'runAdmin'));
            
                if (empty($menu['children'])) {
                    continue;
                }
                
                foreach ($menu['children'] as $_route) {
                    if (!isset($admin_menus[$_route])) continue;
                
                    $_menu = $admin_menus[$_route];
                    add_submenu_page('sabai' . $route, $_menu['title'], $_menu['title'], $capability, 'sabai' . $_route, array($this, 'runAdmin'));
                }
            }
        }
    }
    
    public function onAdminNoticesAction()
    {
        if (get_option('permalink_structure') === '') {
            echo '<div class="updated fade"><p>' . __('You must <a href="options-permalink.php">change your permalink structure</a> for Sabai plugins to work properly.', 'sabai') . '</p></div>';
        }
        
        if (current_user_can('update_plugins')) {
            if (false === $updates = $this->getCache('wordpress_addon_updates')) {
                $installed_addons = $this->getSabai()->getInstalledAddons();
                $local_addons = $this->getSabai()->getLocalAddons();
                $updates = array();
                foreach ($installed_addons as $addon_name => $installed_addon) {
                    if (isset($local_addons[$addon_name])
                        && version_compare($installed_addon['version'], $local_addons[$addon_name]['version'], '<')
                    ) {
                        $updates[] = $addon_name;
                    }
                }
                // This can be cached for as long as we want since the cache is cleared upon both plugin install/update/uninstall and add-on upgrade/uninstall operations.
                $this->setCache($updates, 'wordpress_addon_updates', 86400);
            }
            if (!empty($updates)) {
                echo '<div class="updated fade"><p>' . sprintf(__('There are %1$d upgradable Sabai add-on(s). Please go to the <a href="%2$s">add-on listing section</a> and upgrade all add-ons.', 'sabai'), count($updates), admin_url('admin.php?page=sabai/settings#sabai-system-admin-addons-installed')) . '</p></div>';
            }
        }
    }
    
    public function onDeleteSiteTransientUpdatePluginsAction()
    {
        // Delete addon update info
        $this->deleteCache('wordpress_addon_updates');
  
        // Delete update info of plugins that have been updated
        if ($info = get_site_transient('sabai_plugin_info')) {
            $save = false;
            foreach ($this->getSabaiPlugins() as $sabai_plugin) {
                if (!isset($info[$sabai_plugin])) {
                    continue;
                }
                if (version_compare(self::getCurrentPluginVersion($sabai_plugin), $info[$sabai_plugin]->version, '>=')) {
                    unset($info[$sabai_plugin]);
                    $save = true;
                }
            }
            if ($save) {
                set_site_transient('sabai_plugin_info', $info, 7200); // cache for 2 hours
            }
        }
    }       

    public function runAdmin()
    {
        $sabai = $this->getSabai();
        if (($slash_pos = strpos($_REQUEST['page'], '/'))
            && ($route = substr($_REQUEST['page'], $slash_pos))
        ) {
            if (in_array($route, array('/settings'))) {
                $admin_url = admin_url() . 'options-general.php?page=' . $this->_sabaiName . $route . '&';
            } else {
                $admin_url = admin_url() . 'admin.php?page=' . $this->_sabaiName . $route . '&';
            }
            $sabai->setScriptUrl($admin_url, 'admin' . $route)->setCurrentScriptName('admin' . $route);
        } else {
            $sabai->setCurrentScriptName('admin');
        }
        
        // Create request
        $request = new Sabai_Platform_WordPress_AdminRequest(true, true);
        // Set the default route if none requested
        if (empty($_REQUEST[$sabai->getRouteParam()]) && isset($route)) {
            $request->set($sabai->getRouteParam(), $route);
        }
        
        $context = new Sabai_Context();
        $context->setRequest($request)->setIsAdmin(true)->addTemplateDir($this->getAssetsDir() . '/templates');

        try {
            // Run Sabai         
            $response = $sabai->run(new Sabai_AdminRoutingController(), $context);
            if (!$context->isView()
                || $request->isAjax()
                || $context->getContainer() !== '#sabai-content'
                || $context->getContentType() !== 'html'
            ) {
                if ($context->isView()
                    && $context->getContainer() === '#sabai-content'
                ) {
                    $response->setInlineLayoutHtmlTemplate(dirname(__FILE__) . '/WordPress/layout/admin_inline.html.php');
                }
                $response->send($context);
            } else {
                $layout_dir = dirname(__FILE__) . '/WordPress/layout';
                $response->setInlineLayoutHtmlTemplate($layout_dir . '/admin_inline.html.php')
                    ->setLayoutHtmlTemplate($layout_dir . '/admin.html.php')
                    ->send($context);
            }
        } catch (Exception $e) {
            // Display error message
            require_once ABSPATH . 'wp-admin/admin-header.php';
            printf('<p>%s</p><p><pre>%s</pre></p>', $e->getMessage(), $e->getTraceAsString());
            require_once ABSPATH . 'wp-admin/admin-footer.php';
        }
        exit;
    }

    public function _($str)
    {
        return __($str, 'sabai');
    }

    public function getSabai($loadAddons = true, $reload = false)
    {
        require_once 'Sabai.php';

        if (!Sabai::started()) {
            Sabai::start(get_option('blog_charset', 'UTF-8'), get_locale(), !$this->_sessionTransient, defined('SABAI_WORDPRESS_PAGE_PARAM') ? SABAI_WORDPRESS_PAGE_PARAM : 'p');
        }
        if (!$sabai = Sabai::exists($this->_sabaiName)) {
            $sabai = $this->_createSabai();
        }
        if ($loadAddons) {
            if ($reload) {
                $sabai->reloadAddons();
            } else {
                $sabai->loadAddons();
            }
        }

        return $sabai;
    }

    private function _createSabai()
    {
        $permalink_structure = get_option('permalink_structure');
        if ($pos = strpos($permalink_structure, '.php')) {
            $main_url = home_url() . substr($permalink_structure, 0, $pos + 4);
        } else {
            $main_url = home_url();
        }       
        $sabai = Sabai::create($this)
            ->setScriptUrl($main_url, 'main')
            ->setScriptUrl(admin_url() . 'admin.php?page=' . $this->_sabaiName . '&', 'admin');
        // Init helpers
        $sabai->getHelperBroker()
            ->setHelper('WordPressTemplate', array($this, 'wordPressTemplateHelper'))
            ->setHelper('Date', array($this, 'dateHelper'))
            ->setHelper('Time', array($this, 'timeHelper'))
            ->setHelper('DateTime', array($this, 'dateTimeHelper'))
            ->setHelper('LoadJs', array($this, 'loadJsHelper'))
            ->setHelper('LoadCss', array($this, 'loadCssHelper'))
            ->setHelper('jQuery_Load', array($this, 'jQueryLoadHelper'))
            ->setHelper('jQuery_LoadUI', array($this, 'jQueryLoadUIHelper'))
            ->setHelper('jQuery_LoadJson2', array($this, 'jQueryLoadJson2Helper'))
            ->setHelper('Token', array($this, 'tokenHelper'))
            ->setHelper('TokenValidate', array($this, 'tokenValidateHelper'))
            ->setHelper('GravatarUrl', array($this, 'gravatarUrlHelper'))
            ->setHelper('_', array($this, '_Helper'))
            ->setHelper('_n', array($this, '_nHelper'))
            ->setHelper('__', array($this, '__Helper'))
            ->setHelper('SystemToSiteTime', array($this, 'systemToSiteTimeHelper'))
            ->setHelper('SiteToSystemTime', array($this, 'siteToSystemTimeHelper'))
            ->setHelper('Slugify', array($this, 'slugifyHelper'));
        // Add custom helper directory if exists
        if (is_dir($custom_helper_dir = WP_CONTENT_DIR . '/sabai/helpers')) {
            $sabai->getHelperBroker()->addHelperDir(WP_CONTENT_DIR . '/sabai/helpers', 'Sabai_Platform_WordPress_Helper_');
        }
        if (class_exists('BuddyPress', false)) {
            $sabai->getHelperBroker()->setHelper('UserIdentityUrl', array($this, 'bpUserIdentityUrlHelper'));
        }

        return $sabai;
    }

    private function _addFiltersAndActions()
    {
        // Create a new action event envoked once every hour
        if (!wp_next_scheduled('sabai_cron')) {
            wp_schedule_event(time(), 'hourly', 'sabai_cron');
        }

        add_action('init', array($this, 'onInitAction'));
        add_action('admin_init', array($this, 'onAdminInitAction'));
        add_action('widgets_init', array($this, 'onWidgetsInitAction'));
        add_filter('robots_txt', array($this, 'onRobotsTxt'));
        add_action('wp_login', array($this, 'onWpLoginAction'));
        add_action('wp_logout', array($this, 'onWpLogoutAction'));
        add_action('admin_head-widgets.php', array($this, 'onAdminHeadWidgetsPhpAction'));
        add_action('sabai_cron', array($this, 'onSabaiCron'));
        add_action('wp_before_admin_bar_render', array($this, 'onWpBeforeAdminBarRenderAction'));
        add_action('delete_user', array($this, 'onDeleteUserAction'));
        add_action('deleted_user', array($this, 'onDeletedUserAction'));
        // Disable WP comments
        add_filter('comments_template', array($this, 'onCommentsTemplateFilter'));
        // Add Sabai sitemap index to WP SEO sitemap index 
        add_filter('wpseo_sitemap_index', array($this, 'onWpSeoSitemapIndexFilter'));
        
        add_shortcode('sabai', array($this, 'onSabaiShortcode'));

        // Always append the redirect_to parameter to login/register/lostpassword url generated by the Theme My Login plugin
        add_filter('tml_action_url', array($this, 'onTmlActionUrlFilter'), 10, 2);
    }
    
    public function onTmlActionUrlFilter($url, $action)
    {
        if (isset($_REQUEST['redirect_to']) && in_array($action, array('login', 'register', 'lostpassword'))) {
            return add_query_arg('redirect_to', $_REQUEST['redirect_to'], $url);
        }
        return $url;
    }

    public function onSabaiShortcode($atts, $content, $tag)
    {
        if (!isset($atts['path']) || !strlen($atts['path']) || false === $this->_isSabaiPagePath($atts['path'])) {
            return;
        }
        $query = array();
        if (isset($atts['query'])) {
            if (is_array($atts['query'])) {
                $query = $atts['query'];
            } else {
                parse_str($atts['query'], $query);
            }
        }
        return $this->shortcode($atts['path'], $query);
    }

    public function onSabaiCron()
    {
        $log = $this->getSabai()->Cron();
        $this->updateSabaiOption('cron_log', implode(PHP_EOL, (array)$log));
    }
    
    public function onInitAction()
    {
        $locale = get_locale();
        // Load language file for sabai
        load_textdomain('sabai', WP_LANG_DIR . '/sabai-' . $locale . '.mo');
        load_plugin_textdomain('sabai', false, $this->_sabaiName . '/languages/');
        // Load language files for other sabai plugins
        foreach ($this->getSabaiPlugins() as $plugin) { 
            load_textdomain($plugin, WP_LANG_DIR . '/' . $plugin . '-' . $locale.'.mo');
            load_plugin_textdomain($plugin, false, $plugin . '/languages/');
            @include_once WP_PLUGIN_DIR . '/' . $plugin . '/' . $plugin . '.php';
        }
        $this->getSabai()->doEvent('SabaiPlatformWordPressInit');
    }
    
    public function onAdminInitAction()
    {
        // Run autoupdater
        if (current_user_can('update_plugins')) {
            $sabai_plugins = $this->getSabaiPlugins();
            
            // Enable update notification if any license key is set
            $license_keys = $this->getSabaiOption('license_keys', array());
            if (!empty($license_keys)) {
                foreach ($license_keys as $sabai_plugin_name => $license_key) {
                    if (!in_array($sabai_plugin_name, $sabai_plugins)
                        || !strlen((string)@$license_key['value'])
                    ) {
                        continue;
                    }
                    $remote_args = array(
                        'license_type' => $license_key['type'],
                        'license_key' => $license_key['value'],
                    );
                    require_once 'Sabai/Platform/WordPress/AutoUpdater.php';
                    $updater = new Sabai_Platform_WordPress_AutoUpdater($sabai_plugin_name, $remote_args);
                    $updater->addFilters();
                }
                // Use whichever license key to fetch info of the Sabai package
                if (isset($remote_args)) {
                    $updater = new Sabai_Platform_WordPress_AutoUpdater('sabai', $remote_args);
                    $updater->addFilters();
                }
            }
            
            // Add a hook to clear cache of upgradable add-ons when plugins are installed/updated/uninstalled
            add_action('delete_site_transient_update_plugins', array($this, 'onDeleteSiteTransientUpdatePluginsAction'));
        }
        // Invoke add-ons
        $this->getSabai()->doEvent('SabaiPlatformWordPressAdminInit');
    }

    public function onWidgetsInitAction()
    {
        if (false === $widgets = $this->getCache('wordpress_widgets')) {
            $widgets = $this->getSabai()->getAddon('WordPress')->getWidgets();
            $this->setCache($widgets, 'wordpress_widgets');
        }
        if (empty($widgets)) return;
        
        require_once 'Sabai/Platform/WordPress/Widget.php';
        // Fetch all sabai widgets and then convert each to a wp widget
        foreach ($widgets as $addon_name => $_widgets) {
            foreach ($_widgets as $widget_name => $widget) {
                $class = sprintf('Sabai_Platform_WordPress_Widget_%s_%s', $this->_sabaiName, $widget_name);
                if (class_exists($class, false)) {
                    continue;
                }
                eval(sprintf('
class %s extends Sabai_Platform_WordPress_Widget {
    public function __construct() {
        parent::__construct("%s", "%s", "%s", "%s", "%s");
    }
}
                ', $class, $this->_sabaiName, $addon_name, $widget_name, $widget['title'], $widget['summary']));
                register_widget($class);
            }
        }
    }
    
    public function onRobotsTxt($output)
    {
        $public = get_option('blog_public');
        if ('0' != $public) {
            $site_url = site_url();
            $path = (string)parse_url($site_url, PHP_URL_PATH);
            // Disallow content files
            $output .= "\nDisallow: $path/wp-content/sabai/";
            // Allow thumbnail files
            $output .= "\nAllow: $path/wp-content/sabai/File/thumbnails/"; // allow thumbnail files
            // Disallow library files
            $output .= "\nDisallow: $path/wp-content/plugins/sabai/";
            foreach ($this->getSabaiPlugins() as $plugin) {
                $output .= "\nDisallow: $path/wp-content/plugins/$plugin/";
            }
            // Add linkt to sitemap index
            $output .= "\nSitemap: $site_url/sabai-sitemap-index.xml";
        }
        return $output;
    }
    
    public function onWpLoginAction()
    {
        if (!$this->_sessionTransient) {
            if (!session_id()) {        
                @ini_set('session.use_only_cookies', 1);
                @ini_set('session.use_trans_sid', 0);
                @ini_set('session.hash_function', 1);
                @ini_set('session.cookie_httponly', 1);
                session_start();
            }
            session_regenerate_id(true); // to prevent session fixation attack
        }
        $this->deleteSessionVar('system_permissions');
    }
        
    public function onWpLogoutAction()
    {
        if (session_id()) {
            $_SESSION = array();
            session_destroy();
        }
    }

    public function onWpPrintStylesAction()
    {
        if ($data = $this->getCache('sabai_addons_loaded')) {
            $addons_last_update = $data['timestamp'];
        } else {
            $addons_last_update = time();
        }
        $sabai_plugins = $this->getSabaiPlugins();
        wp_enqueue_style('sabai', $this->getAssetsUrl() . '/css/main.css', array(), $addons_last_update);
        foreach ($sabai_plugins as $sabai_plugin_name) {
            wp_enqueue_style($sabai_plugin_name, $this->getAssetsUrl($sabai_plugin_name) . '/css/main.css', array('sabai'), $addons_last_update);
        }
        if ($this->isLanguageRTL()) {
            wp_enqueue_style('sabai-rtl', $this->getAssetsUrl() . '/css/main-rtl.css', array('sabai'), $addons_last_update);
            foreach ($sabai_plugins as $sabai_plugin_name) {
                wp_enqueue_style($sabai_plugin_name . '-rtl', $this->getAssetsUrl($sabai_plugin_name) . '/css/main-rtl.css', array($sabai_plugin_name), $addons_last_update);
            }
        }
        // Add custom stylesheet by theme
        if (file_exists($this->getCustomAssetsDir() . '/style.css')) {
            wp_enqueue_style('sabai-wordpress', $this->getCustomAssetsDirUrl() . '/style.css', array('sabai'), $addons_last_update);
        }
        // Add JS
        wp_enqueue_script('sabai', $this->getAssetsUrl() . '/js/sabai.js', array('jquery'));
    }
    
    public function onAdminPrintStylesAction()
    {
        echo '<style type="text/css">';
        $admin_menus = $this->getSabaiOption('admin_menus');
        if ($admin_menus && !empty($admin_menus['/']['children'])) {
            foreach ($admin_menus['/']['children'] as $route) {
                if (!isset($admin_menus[$route])) continue;
                
                $menu = $admin_menus[$route];
                if (!isset($menu['icon'])) continue;
                
                printf('
#toplevel_page_sabai%1$s .wp-menu-image {
    background:transparent url(%2$s) no-repeat center center !important;
}
#toplevel_page_sabai%1$s:hover .wp-menu-image, #toplevel_page_sabai%1$s.wp-has-current-submenu .wp-menu-image {
    background-image:url(%3$s) !important;
}',
                    str_replace('/', '-', $route),
                    $this->getSiteUrl() . '/' . (isset($menu['icon_dark']) ? $menu['icon_dark'] : $menu['icon']),
                    $this->getSiteUrl() . '/' . $menu['icon']
                );
            }
        }
        echo '</style>';
    }

    public function getSabaiOption($key, $default = null)
    {
        return get_option($this->_getSabaiOptionName($key), $default);
    }

    public function updateSabaiOption($key, $value, $new = false)
    {
        return $this->_updateOption($this->_getSabaiOptionName($key), $value, $new);
    }

    private function _getSabaiOptionName($key)
    {
        return 'sabai_' . $this->_sabaiName . '_' . $key;
    }
    
    protected function _updateOption($key, $value, $new = false)
    {
        if ($new) {
            delete_option($key);
            return add_option($key, $value);
        }
        return false === get_option($key) ? add_option($key, $value) : update_option($key, $value);
    }

    /* Begin WordPress filter methods */

    public function onTheContentFilter($content)
    {
        return $GLOBALS['wp_query']->get_queried_object_id() == $GLOBALS['post']->ID ? $this->_mainContent : $content;
    }

    public function onDeleteUserAction($userId)
    {
        // Cache user data here so that we can reference it after the user actually being deleted
        $identity = $this->getSabai()->UserIdentity($userId);
        if (!$identity->isAnonymous()) $this->_userToBeDeleted[$userId] = $identity;
    }

    public function onDeletedUserAction($userId)
    {
        if (!isset($this->_userToBeDeleted[$userId])) return;

        // Notify that a user account has been dleted
        $this->getSabai()->doEvent('SabaiUserDeleted', array($this->_userToBeDeleted[$userId]));

        unset($this->_userToBeDeleted[$userId]);
    }

    public function onCommentsTemplateFilter($file)
    {
        // disable comments on Sabai WordPress pages by including a blank template file
        return isset($this->_mainContent) ? dirname(__FILE__) . '/WordPress/comments_template.php' : $file;
    }

    public function onAdminHeadWidgetsPhpAction()
    {
        echo '<style type="text/css">.sabai-form-field {margin:0 0 1em;}</style>';
    }
    
    public function onWpSeoSitemapIndexFilter($sitemap)
    {
        return $sitemap . '
<sitemap>
    <loc>' . $this->getSiteUrl() . '/sabai-sitemap-index.xml</loc>
    <lastmod>' . date('c', time()) . '</lastmod>
</sitemap>
';
    }

    public function onWpBeforeAdminBarRenderAction()
    {
        $user = $this->getSabai()->getUser();
        
        if ($menus = $this->getSabai()->getAddon('System')->getUserMenus()) {      
            // Add primary tool bar menus
            $this->_addAdminBarNodes($user, $menus);
        }
       
        if ($user->isAnonymous()) return; // no accout menus for anonymous users
        
        // Show both profile and account menus to the current user
        $menus = $this->getSabai()->getAddon('System')->getUserProfileMenus();
        if (!empty($menus)) {
            if (!class_exists('BuddyPress', false)) {
                // Add secondary parent item for all Sabai add-ons
                $GLOBALS['wp_admin_bar']->add_menu( array(
                    'parent' => 'my-account',
                    'id' => 'sabai-account',
                    'title' => __('My Account', 'sabai'),
                    'group' => true,
                    'meta' => array(
                        'class' => 'ab-sub-secondary'
                    ),
                ));
                $this->_addAdminBarNodes($user, $menus, '', 'sabai-account');
            } else {
                // Add to BuddyPress secondary account menu item
                $this->_addAdminBarNodes($user, $menus, '', 'my-account-buddypress');
            }
        }
    }
    
    private function _addAdminBarNodes($user, array $nodes, $parent = '', $realParent = '')
    {        
        foreach ((array)@$nodes[$parent] as $node_id => $node) {
            $GLOBALS['wp_admin_bar']->add_menu(array(
                'id' => 'sabai-' . $node_id,
                'parent' => $realParent,
                'title' => (string)@$node['title'],
                'href' => (string)@$node['url'],
                'meta' => (array)@$node['meta'])
            );
            if (!empty($nodes[$node_id])) {
                $this->_addAdminBarNodes($user, $nodes, $node_id, 'sabai-' . $node_id);
            }
        }
    }

    /* End WordPress filter methods */

    public function getTemplate()
    {
        if (!isset($this->_template)) {
            require_once 'Sabai/Platform/WordPress/Template.php';
            $this->_template = new Sabai_Platform_WordPress_Template($this);
        }

        return $this->_template;
    }

    public function wordPressTemplateHelper(Sabai $application)
    {
        return $this->getTemplate();
    }

    public function dateHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('date_format'), $timestamp + get_option('gmt_offset') * 3600);
    }

    public function timeHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('time_format'), $timestamp + get_option('gmt_offset') * 3600);
    }

    public function dateTimeHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp + get_option('gmt_offset') * 3600);
    }
    
    public function jQueryLoadHelper(Sabai $application, $response)
    {
        wp_enqueue_script('jquery');
        $response->addJsFile('', 'jquery');
    }
    
    public function jQueryLoadUIHelper(Sabai $application, $response, $components)
    {
        foreach ($components as $component) {
            wp_enqueue_script('jquery-' . $component);
        }
        $response->addJsFile('', 'jquery-ui');
    }
    
    public function jQueryLoadJson2Helper(Sabai $application, $response)
    {
        wp_enqueue_script('json2');
        $response->addJsFile('', 'json2');
    }
        
    public function loadJsHelper(Sabai $application, $response, $url, $handle, $dependency = null)
    {
        wp_enqueue_script($handle, $url, (array)$dependency);
        $response->addJsFile('', $handle);
    }
    
    public function loadCssHelper(Sabai $application, $response, $url, $handle, $version = null, $media = 'screen')
    {
        wp_enqueue_style($handle, $url, array(), $version, $media);
    }
        
    public function tokenHelper(Sabai $application, $tokenId, $tokenLifetime = 1800, $reobtainable = false)
    {
        return wp_create_nonce($this->_sabaiName . '_' . $tokenId);
    }
        
    public function tokenValidateHelper(Sabai $application, $tokenValue, $tokenId, $reuseable)
    {
        $result = wp_verify_nonce($tokenValue, $this->_sabaiName . '_' . $tokenId);
        // 1 indicates that the nonce has been generated in the past 12 hours or less.
        // 2 indicates that the nonce was generated between 12 and 24 hours ago.
        // Use 1 for enhanced security
        return $result === 1;
    }
            
    public function gravatarUrlHelper(Sabai $application, $email, $size = 96, $default = 'mm', $rating = null, $secure = false)
    {
        if (preg_match('/src=("|\')(.*?)("|\')/i', get_avatar($email, $size, $default), $matches)) {
            return str_replace('&amp;', '&', $matches[2]);
        }
    }
    
    public function _Helper(Sabai $application, $msgId, $packageName = 'sabai')
    {
        return __($msgId, $packageName);
    }
    
    public function _nHelper(Sabai $application, $msgId, $msgId2, $num, $packageName = 'sabai')
    {
        return _n($msgId, $msgId2, $num, $packageName);
    }
    
    public function __Helper(Sabai $application, $msgId, $context = '', $packageName = 'sabai')
    {
        return _x($msgId, $context, $packageName);
    }
    
    public function __nHelper(Sabai $application, $msgId, $msgId2, $num, $context = '', $packageName = 'sabai')
    {
        return _nx($msgId, $msgId2, $num, $context, $packageName);
    }
    
    public function siteToSystemTimeHelper(Sabai $application, $timestamp)
    {
        // mktime should return UTC in WP
        return intval($timestamp - get_option('gmt_offset') * 3600);
    }
    
    public function systemToSiteTimeHelper(Sabai $application, $timestamp)
    {
        return intval($timestamp + get_option('gmt_offset') * 3600);
    }
    
    public function slugifyHelper(Sabai $application, $string)
    {
        return rawurldecode(sanitize_title($string));
    }
    
    public function bpUserIdentityUrlHelper(Sabai $application, SabaiFramework_User_Identity $user)
    {
        return bp_core_get_user_domain($user->id);
    }
    
    public function shortcode($path, array $attributes = array())
    {
        return Sabai_Platform_WordPress_Shortcode::render($this, $path, $attributes);
    }

    public function activate()
    {
        require_once 'Sabai/Platform/WordPress/include/activate.php';
        sabai_platform_wordpress_activate($this);
    }

    public function activatePlugin($pluginName, $primaryAddonName)
    {
        require_once 'Sabai/Platform/WordPress/include/activate_plugin.php';
        return sabai_platform_wordpress_activate_plugin($this, $pluginName, $primaryAddonName);
    }
    
    public function createPage($slug, $title, ArrayObject $log = null)
    {
        require_once 'Sabai/Platform/WordPress/include/create_page.php';
        return sabai_platform_wordpress_create_page($this, $slug, $title, $log);
    }
    
    public static function getCurrentPluginVersion($pluginName)
    {
        // Fetch plugin data for version comparison
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_file = WP_PLUGIN_DIR . '/' . $pluginName . '/' . $pluginName . '.php';
        if (!file_exists($plugin_file)) {
            return '0.0.0';
        }
        $plugin_data = get_plugin_data($plugin_file, false, false);
        
        return $plugin_data['Version'];
    }
}
