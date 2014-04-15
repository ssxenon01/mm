<?php
class Sabai_Helper
{
    public function help(Sabai $application)
    {
        throw new BadMethodCallException(sprintf('%s::%s may not be called directly', __CLASS__, __METHOD__));
    }
    
    public function reset(Sabai $application){}
}