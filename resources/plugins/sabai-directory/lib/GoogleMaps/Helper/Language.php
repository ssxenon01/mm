<?php
class Sabai_Addon_GoogleMaps_Helper_Language extends Sabai_Helper
{
    private static $_lang, $_languages = array(
        'ar', 'bg', 'bn', 'ca', 'cs', 'da', 'de', 'el', 'en', 'en-AU', 'en-GB', 'es', 'eu',
        'fa', 'fi', 'fil', 'fr', 'gl', 'gu', 'he', 'hi', 'hr', 'hu', 'id', 'it', 'iw', 'ja', 'kn',
        'ko', 'lt', 'lv', 'ml', 'mr', 'nl', 'nn', 'no', 'or', 'pl', 'pt', 'pt-BR', 'pt-PT',
        'rm', 'ro', 'ru', 'sk', 'sl', 'sr', 'sv', 'tl', 'ta', 'te', 'th', 'tr', 'uk', 'vi',
        'zh-CN', 'zh-TW'
    );
    
    /**
     * @param Sabai $application
     * @param string $language
     */
    public function help(Sabai $application, $language = SABAI_LANG, $reset = false)
    {
        if (!isset(self::$_lang) || $reset) {
            if (strpos($language, '_')) {
                $langs = array_reverse(explode('_', $language));
                $langs[0] = $langs[1] . '-' . strtoupper($langs[0]);
            } elseif (strpos($language, '-')) {
                $langs = array_reverse(explode('-', $language));
                $langs[0] = $langs[1] . '-' . strtoupper($langs[0]);
            } else {
                $langs = array($language);
            }
            self::$_lang = 'en';
            foreach ($langs as $lang) {
                if (in_array($lang, self::$_languages)) {
                    self::$_lang = $lang;
                    break;
                }
            }
        }
        return self::$_lang;
    }
}