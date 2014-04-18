<?php
class Sabai_Helper_LinkToRemote extends Sabai_Helper
{
    private static $_alwaysPost = false;
    
    public function __construct()
    {
        if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG) {
            self::$_alwaysPost = true;
        }
    }
    
    public function help(Sabai $application, $linkText, $update, $linkUrl, array $options = array(), array $attributes = array())
    {
        if (SabaiFramework_Request_Http::isXhr()) {
            // Request came from Ajax, so we don't need the normal URL to be generated
            $link_url = '#';
            $ajax_url = isset($options['url']) ? $application->Url($options['url']) : $application->Url($linkUrl);
        } else {
            $link_url = $application->Url($linkUrl);
            $ajax_url = isset($options['url']) ? $application->Url($options['url']) : clone $link_url;
        }
        $update = Sabai::h($update);

        // Add options
        $ajax_options = array();
        if (isset($options['loadingImage']) && !$options['loadingImage']) $ajax_options[] = 'loadingImage:false';
        if (!empty($options['slide'])) $ajax_options[] = "effect:'slide'";
        if (isset($options['scroll'])) {
            if (is_bool($options['scroll']) && $options['scroll']) {
                $ajax_options[] = "scrollTo:'" . $update . "'";
            } else {
                $ajax_options[] = "scrollTo:'" . Sabai::h($options['scroll']) . "'";
            }
        }
        if (!empty($options['highlight'])) $ajax_options[] = 'highlight:true';
        if (!empty($options['replace'])) $ajax_options[] = 'replace:true';
        if (!empty($options['width'])) $ajax_options[] = 'modalWidth:' . intval($options['width']);
        if (!empty($options['cache'])) $ajax_options[] = 'cache:true';
        if (!empty($options['sendData'])) {
            $ajax_options[] = 'onSendData:function(data, trigger){' . $options['sendData'] . '}';
        }
        if (!empty($options['success'])) {
            $ajax_options[] = 'onSuccess:function(result, target, trigger){' . $options['success'] . '}';
        }
        if (!empty($options['error'])) {
            $ajax_options[] = 'onError:function(result, target, trigger){' . $options['error'] . '}';
        }
        if (!empty($options['errorDisableTrigger'])) {
            $ajax_options[] = "onErrorDisableTrigger:true";
        }
        if (!empty($options['content'])) {
            $ajax_options[] = 'onContent:function(response, target, trigger){' . $options['content'] . '}';
        }
        if (!empty($options['post']) || self::$_alwaysPost) {
            $ajax_options[] = "type:'post'";
            if (empty($options['sendData'])) {
                // Use http_build_query instead of json_encode so that boolean values are converted to integers
                $ajax_options[] = sprintf('data:"%s"', strtr(http_build_query($ajax_url['params']), array('%7E' => '~', '+' => '%20')));
            } else {
                // However, we use json_encode since the sendData callback expects the data parameter to be an object instead of a query string
                $ajax_options[] = sprintf('data:%s', json_encode($ajax_url['params']));
            }
            $ajax_url['params'] = array();
        }
        $ajax_url['separator'] = '&';
        $ajax_options[] = "trigger:jQuery(this), target:'" . $update . "', url:'" . $ajax_url . "'";

        // Create attributes
        $attributes['onclick'] = 'SABAI.ajax({' . implode(',', $ajax_options) . '}); return false;';
        
        return new Sabai_Link($link_url, $linkText, $options, $attributes);
        
        
    }
}
