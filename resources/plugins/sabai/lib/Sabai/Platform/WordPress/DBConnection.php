<?php
class Sabai_Platform_WordPress_DBConnection extends SabaiFramework_DB_Connection
{
    public function __construct()
    {
        parent::__construct('MySQL');
        $this->_resourceName = $GLOBALS['wpdb']->dbname;
        $this->_clientEncoding = $GLOBALS['wpdb']->charset;
    }

    protected function _doConnect()
    {
        return $GLOBALS['wpdb']->dbh;
    }

    public function getDSN()
    {
        return sprintf('mysql://%s:%s@%s/%s?client_flags=%d',
            rawurlencode($GLOBALS['wpdb']->dbuser),
            rawurlencode($GLOBALS['wpdb']->dbpassword),
            rawurlencode($GLOBALS['wpdb']->dbhost),
            rawurlencode($GLOBALS['wpdb']->dbname),
            defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0
        );
    }
}