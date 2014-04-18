<?php
class Sabai_Addon_Google_GeocodeException extends Sabai_RuntimeException
{
    protected $_query;
    
    public function __construct($query, $message = '')
    {
        parent::__construct($message);
        $this->_query = $query;
    }
    
    public function getGeocodeQuery()
    {
        return $this->_query;
    }
}