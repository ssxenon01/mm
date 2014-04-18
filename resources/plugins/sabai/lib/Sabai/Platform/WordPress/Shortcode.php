<?php
class Sabai_Platform_WordPress_Shortcode
{
    private static $_count = 0, $_isInitialized = false, $_isFirstShortcode = true, $_isFirstPageShortcode = true, $_content = array();
    
    public static function render(Sabai_Platform_WordPress $platform, $path, array $attributes = array())
    {  
        try {
            // Init Sabai
            $sabai = $platform->getSabai(true, true);
            if (!self::$_isInitialized) {
                $sabai->getHelperBroker()->setHelper('LinkToRemote', array(new Sabai_Platform_WordPress_LinkToRemoteHelper(), 'help'));
                self::$_isInitialized = true;
            }
        
            // Create context
            $container = 'sabai-embed-wordpress-shortcode-' . ++self::$_count;
            $context = new Sabai_Context();
            $context->setContainer('#' . $container)
                ->setRequest(new Sabai_Request(true, true))
                ->setAttributes($attributes)
                ->addTemplateDir($platform->getAssetsDir() . '/templates');
        
            // Run Sabai
            $response = $sabai->run(new Sabai_MainRoutingController(), $context, $path);
            
            // Render output
            if ($context->isView()) {
                if (self::$_isFirstShortcode) {
                    // Need a layout file to render JS/CSS
                    $response->setLayoutHtmlTemplate('Sabai/Platform/WordPress/layout/shortcode.html.php');
                    self::$_isFirstShortcode = false;
                } else {
                    $response->setInlineLayoutHtmlTemplate('Sabai/Platform/WordPress/layout/shortcode_inline.html.php');
                }
                $placeholder = '<div id="' . $container . '"></div>';
                ob_start();
                $response->send($context);
                $content = '<div id="' . $container . '" class="sabai sabai-embed">' . ob_get_clean() . '</div>';
                if (!empty($attributes['return'])) {
                    return $content;
                }
                if (self::$_isFirstPageShortcode) {
                    add_filter('the_content', array(__CLASS__, 'filter'), 999999);
                    self::$_isFirstPageShortcode = false;
                }
                $placeholder = '<div id="' . $container . '"></div>';
                self::$_content[$placeholder] = $content;
                return $placeholder;
            } elseif ($context->isError()) {
                $ret = array();
                $error = $response->getError($context);
                foreach ($error['messages'] as $message) {
                    $ret[] = '<div class="sabai-error">' . $message . '</div>';
                }
                return implode(PHP_EOL, $ret);
            } elseif ($context->isRedirect()) {
                return sprintf(
                    '<script type="text/javascript">jQuery(document).ready(function(){window.location.replace("%s");});</script><p>%s</p><p>%s</p><div><a class="sabai-btn">%s</a></div>',
                    $context->getRedirectUrl(),
                    ($message = $context->getRedirectMessage()) ? $message : __('Redirecting...', 'sabai'),
                    __('If you are not redirected automatically, please click the button below:'),
                    __('Continue')
                );
            } else {
                return '';
            }
        } catch (Exception $e) {
            if (is_super_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
                // Print trace if admin
                return sprintf('<p>%s</p><p><pre>%s</pre></p>', Sabai::h($e->getMessage()), Sabai::h($e->getTraceAsString()));
            }
            return sprintf('<p>%s</p>', 'An error occurred while processing the request. Please contact the administrator of the website for further information.');
        }
    }
    
    public static function filter($content)
    {
        return str_replace(array_keys(self::$_content), array_values(self::$_content), $content);
    }
}
