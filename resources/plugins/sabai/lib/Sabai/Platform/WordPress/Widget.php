<?php
class Sabai_Platform_WordPress_Widget extends WP_Widget
{
    protected $_wpPluginName, $_addonName, $widgetName;

    public function __construct($wpPluginName, $addonName, $widgetName, $widgetTitle, $widgetSummary)
    {
        $this->_wpPluginName = $wpPluginName;
        $options = array('description' => $widgetSummary);
        parent::__construct(false, sprintf('%s | %s', $widgetTitle, $wpPluginName), $options);
        $this->_addonName = $addonName;
        $this->_widgetName = $widgetName;
    }

    protected function _getSabai()
    {
        return Sabai_Platform_WordPress::getInstance($this->_wpPluginName)->getSabai();
    }
    
    /**
     * Call an application helper
     */
    public function __call($name, $args)
    {
        return $this->_getSabai()->getHelperBroker()->callHelper($name, $args);
    }

    function widget($args, $instance)
    {
        if (!$widget = $this->_getWidget(true)) {
            return;
        }

        if ($content = $widget->widgetsWidgetGetContent($instance)) {
            if (!file_exists($tpl = get_stylesheet_directory() . '/sabai/wordpress_widget.html.php')) {
                $tpl = Sabai_Platform_WordPress::getInstance($this->_wpPluginName)->getAssetsDir() . '/templates/wordpress_widget.html.php';
            }
            $tpl_vars = array(
                'content' => $content,
                'addon_name' => $this->_addonName,
                'widget_name' => $this->_widgetName,
            );

            // Output content
            if (!strlen($instance['_title_'])) {
                echo $args['before_widget'];
                $this->_include($tpl, $tpl_vars);
                echo $args['after_widget'];
            } else {  
                echo $args['before_widget'];
                echo $args['before_title'];
                Sabai::_h($instance['_title_']);
                echo $args['after_title'];
                $this->_include($tpl, $tpl_vars);
                echo $args['after_widget'];
            }
        }
    }
    
    private function _include()
    {
        extract(func_get_arg(1), EXTR_SKIP);
        include func_get_arg(0);
    }

    function update($new_instance, $old_instance)
    {
        if ($widget = $this->_getWidget()) {
            $widget->widgetsWidgetOnSettingsSaved($new_instance, $old_instance);
        }
        
        return $new_instance;
    }

    function form($instance)
    {        
        if (!$widget = $this->_getWidget()) {
            return;
        }

        // Get additional settings
        $elements = array();
        if ($widget_settings = $widget->widgetsWidgetGetSettings()) {
            foreach ($widget_settings as $key => $data) {
                if ($data['#type'] === 'checkbox') {
                    $default_value = !empty($instance[$key][0]);
                } else {
                    $default_value = array_key_exists($key, $instance) ? $instance[$key] : @$widget_settings[$key]['#default_value'];
                }
                $elements[$this->get_field_name($key)] = array_merge(
                    $data,
                    array(
                        '#type' => @$data['#type'],
                        '#title' => isset($data['#title']) ? $data['#title'] : null,
                        '#description' => isset($data['#description']) ? $data['#description'] : null,
                        '#default_value' => $default_value,
                    )
                );
            }
        }
        $elements[$this->get_field_name('_title_')] = array(
            '#title' => __('Title'),
            '#type' => 'textfield',
            '#default_value' => isset($instance['_title_'])
                ? $instance['_title_']
                : $widget->widgetsWidgetGetLabel(),
            '#weight' => -1,
        );

        list($html, ) = $this->_getSabai()->getAddon('Form')->buildForm($elements)->render(true);
        echo $html;
    }
    
    protected function _getWidget($loadModel = false)
    {
        $sabai = $this->_getSabai();
        if (!$sabai->isAddonLoaded($this->_addonName)) return;

        if ($loadModel && !$sabai->isRunning()) {
            // For some strange reason, this is required for the Model classes to be loaded on non-Sabai pages
            class_exists('Sabai_Model');
        }
        
        return $sabai->getAddon($this->_addonName)->widgetsGetWidget($this->_widgetName);
    }
}