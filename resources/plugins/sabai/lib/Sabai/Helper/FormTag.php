<?php
class Sabai_Helper_FormTag extends Sabai_Helper
{
    public function help(Sabai $application, $method = 'post', $actionPath = null, array $params, array $attributes = array())
    {
        $extra_html = array();
        if (strcasecmp($method, 'get') == 0) {
            $method = 'get';
            // embed route parameter if method is get and route is not an empty string
            if (isset($actionPath)) {
                $extra_html[] = sprintf('<input type="hidden" name="%s" value="%s" />', $application->getRouteParam(), rtrim($actionPath, '/'));
            }
        } else {
            $method = 'post';
        }
        if (isset($actionPath)) {
            $attributes['action'] = $application->Url($actionPath);
        }
        $attr = array();
        foreach ($attributes as $k => $v) {
            $attr[] = sprintf(' %s="%s"', $k, Sabai::h($v, ENT_COMPAT));
        }
        $params = array();
        parse_str(parse_url($application->Url(array('script' => $application->getCurrentScriptName())), PHP_URL_QUERY), $params);
        foreach ($params as $param_name => $param_value) {
            $extra_html[] = sprintf('<input type="hidden" name="%s" value="%s" />', Sabai::h($param_name), Sabai::h($param_value));
        }

        printf('<form method="%s"%s>%s', $method, implode('', $attr), implode(PHP_EOL, $extra_html));
    }
}