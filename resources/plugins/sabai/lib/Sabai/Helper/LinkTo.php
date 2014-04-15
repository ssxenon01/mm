<?php
class Sabai_Helper_LinkTo extends Sabai_Helper
{
    public function help(Sabai $application, $linkText, $url, array $options = array(), array $attributes = array())
    {
        return new Sabai_Link($application->Url($url), $linkText, $options, $attributes);
    }
}