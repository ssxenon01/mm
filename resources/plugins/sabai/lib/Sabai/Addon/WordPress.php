<?php
class Sabai_Addon_WordPress extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_IWidgets
{
    const VERSION = '1.2.29', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */
    
    public function systemGetAdminRoutes()
    {
        return array(
            '/wordpress/permalink' => array(
                'controller' => 'Permalink',
            ),
            '/wordpress/verify-license' => array(
                'type' => Sabai::ROUTE_CALLBACK,
                'controller' => 'VerifyLicense',
            ),
            '/settings/wordpress' => array(
                'controller' => 'Settings',
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {

    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */

    public function onSabaiWordPressAddonLoaded()
    {
        // Initialize mod_rewrite format
        $format = rtrim($this->_application->getScriptUrl('main'), '/') . '%1$s';
        $this->_application->setModRewriteFormat($format, 'main');
    }

    /**
     * Fetch widget data to be used on WP widgets_init action
     */
    public function getWidgets()
    {
        $widgets = array();
        foreach ($this->_application->getAddon('Widgets')->getModel('Widget')->fetch() as $widget) {
            if (!$this->_application->isAddonLoaded($widget->addon)) continue;

            $iwidget = $this->_application->getAddon($widget->addon)->widgetsGetWidget($widget->name);
            $widgets[$widget->addon][$widget->name] = array(
                'title' => $iwidget->widgetsWidgetGetTitle(),
                'summary' => $iwidget->widgetsWidgetGetSummary(),
            );
        }
        return $widgets;
    }

    public function onSystemAdminInfoFilter(&$info)
    {
        $info += array(
            'wordpress_locale' => array('name' => 'WordPress Locale', 'value' => get_locale()),
            'wordpress_lang' => array('name' => 'WPLANG', 'value' => WPLANG),
            'wordpress_lang_dir' => array('name' => 'WP_LANG_DIR', 'value' => WP_LANG_DIR),
        );
        if ($_plugin_info = get_site_transient('sabai_plugin_info')) {
            $plugin_info = array();
            foreach (array_merge(array('sabai'), $this->_application->getPlatform()->getSabaiPlugins()) as $plugin_name) {
                if (!$plugin = @$_plugin_info[$plugin_name]) continue;
                $plugin_info[] = sprintf('<b>%s</b> (Version: %s, Date: %s, Download URL: %s)', $plugin_name, $plugin->version, $plugin->last_updated, $plugin->download_link);
            }
            $info['wordpress_plugin_info'] = array('name' => 'WordPress Plugin Info', 'value' => implode('<br />', $plugin_info));
        }
    }

    public function onWidgetsIWidgetsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_widgets');
    }

    public function onWidgetsIWidgetsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_widgets');
    }

    public function onWidgetsIWidgetsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_widgets');
    }
        
    public function onSystemIMainMenusInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $menus = $addon->systemGetMainMenus();
        
        $platform = $this->_application->getPlatform();
        $slugs = $platform->getSabaiOption('page_slugs', array());
        $slugs[1][$addon->getName()] = array();
        ksort($menus); // sort by path
        foreach ($menus as $menu_path => $menu) {
            $slug = trim($menu_path, '/');
            $slugs[0][$slug] = $slug;
            $slugs[1][$addon->getName()][$slug] = $slug;
            if ($post = get_page_by_path($slug)) {    
                $slugs[2][$slug] = $post->ID;
            } else {
                // creat page
                if ($page_id = $platform->createPage($slug, $menu['title'])) {
                    $slugs[2][$slug] = $page_id;
                }
            }
        }
        $platform->updateSabaiOption('page_slugs', $slugs);
    }
    
    public function onSystemIMainMenusUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $platform = $this->_application->getPlatform();
        $slugs = $platform->getSabaiOption('page_slugs', array());
        if (!empty($slugs[0]) && !empty($slugs[1][$addon->getName()])) {
            // Remove slugs and ids of the uninstalled plugin from the global slug list
            $slugs[0] = array_diff_key($slugs[0], $slugs[1][$addon->getName()]); // remove from slugs by slug list
            $slugs[2] = array_diff_key($slugs[2], $slugs[1][$addon->getName()]); // remoev from page ids by slug list
            unset($slugs[1][$addon->getName()]); // unset slugs by plugin
        }
        $platform->updateSabaiOption('page_slugs', $slugs);
    }
    
    public function onSystemIMainMenusUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->onSystemIMainMenusUninstalled($addon, $log);
        $this->onSystemIMainMenusInstalled($addon, $log);
    }    
    
    public function onSystemIAdminMenusInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        // Save admin menu cache
        $this->_application->getPlatform()->updateSabaiOption('admin_menus', $this->_application->getAddon('System')->getAdminMenus());
    }
    
    public function onSystemIAdminMenusUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->onSystemIAdminMenusInstalled($addon, $log);
    }
    
    public function onSystemIAdminMenusUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->onSystemIAdminMenusInstalled($addon, $log);
    }
    
    public function onSabaiWebResponseRenderContentAdminAddPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
        
    public function onSabaiWebResponseRenderContentAdminAddChildPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
    
    public function onSabaiWebResponseRenderContentAdminEditPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
        
    public function onSabaiWebResponseRenderContentAdminEditChildPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
    
    public function onSabaiWebResponseRenderTaxonomyAdminAddTerm($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_taxonomy_term');
    }
    
    public function onSabaiWebResponseRenderTaxonomyAdminEditTerm($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_taxonomy_term');
    }
    
    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array('wordpress_captcha');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'wordpress_captcha':
                return new Sabai_Addon_WordPress_CaptchaFieldWidget($this);
        }
    }

    /* End implementation of Sabai_Addon_Field_IWidgets */
    
    public function onFormBuildSystemAdminSettings(&$form)
    {
        $form[$this->_name] = array(
            '#tree' => true,
            '#weight' => 3,
        );
        $token = $this->_application->Token('wordpress_verify_license', 1800, true);
        $plugin_names = $this->_application->getPlatform()->getSabaiPlugins(true);
        if (!empty($plugin_names)) {
            $form[$this->_name]['envato_license_keys'] = array(
                '#title' => __('CodeCanyon.net Purchase Code Settings', 'sabai'),
                '#class' => 'sabai-form-group',
                'info' => array(
                    '#type' => 'markup',
                    '#markup' => '<p>' . __('Enter the item purchase code you received from CodeCanyon.net to enable automatic updates for each plugin. Make sure that you enter a valid purchase code here otherwise it will just slow your site down.', 'sabai') . '</p>',
                ),
            );
            $license_keys = $this->_application->getPlatform()->getSabaiOption('license_keys', array());
            foreach ($plugin_names as $plugin_name) {
                if (!$plugin_data = $this->_getPluginData($plugin_name)) {
                    continue;
                }
                $form[$this->_name]['envato_license_keys'][$plugin_name] = array(
                    '#type' => 'textfield',
                    '#min_length' => 36,
                    '#max_length' => 36,
                    '#regex' => '/^[a-z0-9-]+$/',
                    '#default_value' => isset($license_keys[$plugin_name]) && $license_keys[$plugin_name]['type'] === 'envato' ? $license_keys[$plugin_name]['value'] : null,
                    '#title' => $plugin_data['Name'],
                    '#size' => 40,
                    '#field_suffix' => $this->_application->LinkToRemote(
                        __('Verify', 'sabai'),
                        '#sabai-content',
                        $this->_application->Url('/wordpress/verify-license', array('plugin' => $plugin_name, 'license_type' => 'envato', Sabai_Request::PARAM_TOKEN => $token)),
                        array(
                            'sendData' => 'data["license_key"]=trigger.closest(".sabai-form-field").find("input[type=text]").val();',
                            'success' => 'trigger.after(" <span class=\"sabai-success\"><i class=\"sabai-icon-ok\"></i></span>"); return false;',
                            'error' => 'trigger.after(" <span class=\"sabai-error\"><i class=\"sabai-icon-remove\"></i></span>"); return false;'
                        ),
                        array('class' => 'sabai-btn sabai-btn-small')
                    ),
                );
            }
        }
        
        $form['#submit'][0][] = array($this, 'submitSystemAdminSettingsForm');
    }
    
    private function _getPluginData($pluginName)
    {
        // Fetch plugin data for version comparison
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_file = WP_PLUGIN_DIR . '/' . $pluginName . '/' . $pluginName . '.php';
        if (!file_exists($plugin_file)) {
            return false;
        }
        return get_plugin_data($plugin_file, false, false);
    }

    public function submitSystemAdminSettingsForm($form)
    {
        // Save license keys to WP options table
        $license_keys = $this->_application->getPlatform()->getSabaiOption('license_keys', array());
        foreach ($form->values[$this->_name]['envato_license_keys'] as $plugin_name => $license_key) {
            $license_keys[$plugin_name] = array('type' => 'envato', 'value' => $license_key);
        }
        $this->_application->getPlatform()->updateSabaiOption('license_keys', $license_keys);
    }
    
    public function onSabaiAddonUninstalled($addonEntity, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_addon_updates');
    }

    public function onSabaiAddonUpgraded($addonEntity, $previousVersion, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_addon_updates');
    }

    public function onContentPostBodyFilter(&$body, $entity)
    {
        if (!$entity->getAuthorId()) {
            return;
        }
        $author = $entity->getAuthor();
        if ($entity->getBundleType() === 'directory_listing') {
            if ($owner = $this->_application->Directory_ListingOwner($entity)) {
                // Check if the owner is the administrator
                $author = $owner;
            }
        }
        if (!$author->isAnonymous()) {
            if (!empty($this->_config['do_user_shortcode']) || $this->_application->IsAdministrator($author)) {
                $body = do_shortcode($body);
            }
        }
    }

    public function onTaxonomyTermBodyFilter(&$body, $entity)
    {
        if ($entity->getAuthorId() && $this->_application->IsAdministrator($this->_application->UserIdentity($entity->getAuthorId()))) {
            $body = do_shortcode($body);
        }
    }
    
    public function getDefaultConfig()
    {
        return array(
            'do_user_shortcode' => false,
        );
    }
    
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/wordpress', 'modal' => true, 'modal_width' => 600);
    }
    
    public function onEntityViewEntity(Sabai_Addon_Entity_Entity $entity)
    {
        add_filter('body_class', array($this, 'addBodyClass'));
    }
    
    public function addBodyClass($classes)
    {
        if (isset($GLOBALS['sabai_content_entity'])) {
            $classes[] = 'sabai-entity-id-' . $GLOBALS['sabai_content_entity']->getId();
            $classes[] = 'sabai-entity-bundle-name-' . $GLOBALS['sabai_content_entity']->getBundleName();
            $classes[] = 'sabai-entity-bundle-type-' . $GLOBALS['sabai_content_entity']->getBundleType();
        }
        return $classes;
    }
}
