<?php
class Sabai_Addon_HTML_Helper_Filter extends Sabai_Helper
{
    /**
     * Filters HTML text using the HTMLPurifier library
     *
     * @return string Filtered text
     * @param Sabai $application
     * @param string $text
     * @param array $options HTMLPurifier options
     * @param bool $allowBlockLevelTags
     */
    public function help(Sabai $application, $text, array $options = array(), $allowBlockLevelTags = true)
    {
        $serializer_path = $application->getAddon('HTML')->getVarDir();
        $application->ValidateDirectory($serializer_path, true); 
        $options += array(
            'URI.Host' => $_SERVER['HTTP_HOST'],
            'HTML.DefinitionID' => 'HTML',
            'Attr.EnableID' => false,
            'AutoFormat.AutoParagraph' => (!$allowBlockLevelTags || false === strpos(trim($text), "\n")) ? false : true, // Do not auto-paragraph single line text
            'HTML.AllowedElements' => $this->_getAllowedHTMLTags($allowBlockLevelTags, !empty($options['HTML.SafeIframe']) && !empty($options['URI.SafeIframeRegexp'])),
            'Cache.SerializerPath' => $serializer_path,
            'Cache.DefinitionImpl' => 'Serializer',
            'Core.Encoding' => SABAI_CHARSET,
            'HTML.Nofollow' => true,
            'Attr.AllowedFrameTargets' => array('_blank', '_self', '_parent', '_top'),
        );
        
        // Remove the port part from the host name
        if ($pos = strpos($options['URI.Host'], ':')) {
            $options['URI.Host'] = substr($options['URI.Host'], 0, $pos);
        }
        
        // Allow other add-ons to modify filtering options
        $application->doEvent('HTMLFilter', array($text, &$options));

        require_once 'HTMLPurifier/Bootstrap.php';
        spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));
        $htmlpurifier = new HTMLPurifier($options);

        // Filter HTML and also allow other add-ons to modify the result
        return $application->Filter('HTMLFilter', $htmlpurifier->purify($text), array($text, $options));
    }

    protected function _getAllowedHTMLTags($allowBlockLevelTags = true, $allowIframe = false)
    {
        $tags = array('a', 'abbr', 'acronym', 'b', 'code', 'del',
            'em', 'i', 'ins', 'span', 'strong', 'sub', 'sup', 'u');
        if ($allowBlockLevelTags) {
            $tags = array_merge($tags, array('div', 'img', 'p', 'pre', 'br', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
                'table', 'caption', 'tr', 'th', 'td', 'blockquote', 'cite', 'hr', 'h1', 'h2', 'h3'
            ));
            if ($allowIframe) {
                $tags[] = 'iframe';
            }
        }

        return $tags;
    }
}