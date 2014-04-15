<?php
class Sabai_Helper_UserRegisterUrl extends Sabai_Helper
{
    public function help(Sabai $application, $redirect)
    {
        return $application->getPlatform()->getUserRegisterUrl((string)$redirect);
    }
}