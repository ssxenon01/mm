<?php
class Sabai_Helper__n extends Sabai_Helper
{
    public function help(Sabai $application, $msgId, $msgId2, $num, $packageName = 'sabai')
    {
        return $num === 1 ? $msgId : $msgId2;
    }
}