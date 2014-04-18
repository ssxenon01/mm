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
class SabaiFramework_DB_MySQLi extends SabaiFramework_DB_MySQL
{
    protected function _doQuery($query)
    {
        if (!$rs = mysqli_query($query, $this->_connection->connect())) {
            return false;
        }

        if (!class_exists('SabaiFramework_DB_Rowset_MySQLi', false)) require 'SabaiFramework/DB/Rowset/MySQLi.php';

        return new SabaiFramework_DB_Rowset_MySQLi($rs);
    }

    protected function _doExec($sql)
    {
        return mysqli_query($sql, $this->_connection->connect());
    }

    public function affectedRows()
    {
        return mysqli_affected_rows($this->_connection->connect());
    }

    public function lastInsertId($tableName, $keyName)
    {
        return mysqli_insert_id($this->_connection->connect());
    }

    public function lastError()
    {
        return sprintf('%s(%s)', mysqli_error($this->_connection->connect()), mysqli_errno($this->_connection->connect()));
    }

    /**
     * Escapes a string value for MySQL DB
     *
     * @param string $value
     * @return string
     */
    public function escapeString($value)
    {
        return "'" . mysqli_real_escape_string($this->_connection->connect(), $value) . "'";
    }

    protected function _doGetVersion()
    {
        $version = mysqli_get_server_version($this->_connection->connect());

        return  sprintf('%d.%d.%d', $version / 10000, ($version % 10000) / 100, $version % 100);
    }
}