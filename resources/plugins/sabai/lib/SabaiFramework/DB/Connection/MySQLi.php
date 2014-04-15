<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_DB
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_DB_Connection_MySQLi extends SabaiFramework_DB_Connection_MySQL
{
    protected function _doConnect()
    {
        $link = mysqli_init();
        $flags = $this->_resourceSecure ? MYSQLI_CLIENT_FOUND_ROWS | MYSQLI_CLIENT_SSL : MYSQLI_CLIENT_FOUND_ROWS;
        if (!mysqli_real_connect($link, $this->_resourceHost, $this->_resourceUser, $this->_resourceUserPassword, $this->_resourceName, $this->_resourcePort, null, $flags)) {
            throw new SabaiFramework_DB_ConnectionException(sprintf('Unable to connect to database server. Error: %s(%s)', mysqli_connect_error(), mysqli_connect_errno()));
        }
        mysqli_autocommit($link, true);

        // Set client encoding if requested
        if (!empty($this->_clientEncoding)) {
            if ($mysql_charset = @self::$_charsets[strtolower($this->_clientEncoding)]) {
                if (!mysqli_set_charset($link, $mysql_charset)) $this->_clientEncoding = null;
            }
        }

        $this->_resourceId = $link;
        return true;
    }

    public function getDSN()
    {
        return sprintf('mysqli://%s:%s@%s:%d/%s',
            rawurlencode($this->_resourceUser),
            rawurlencode($this->_resourceUserPassword),
            rawurlencode($this->_resourceHost),
            $this->_resourcePort,
            rawurlencode($this->_resourceName)
        );
    }
}