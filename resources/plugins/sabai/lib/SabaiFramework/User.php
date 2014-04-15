<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_User
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_User
{
    /**
     * @var SabaiFramework_User_Identity
     */
    protected $_identity;
    /**
     * @var bool
     */
    protected $_administrator = false;
    /**
     * @var array
     */
    protected $_permissions = array();
    /**
     * @var bool
     */
    protected $_finalized = false;
    /**
     * @var bool
     */
    protected $_finalize = false;

    /**
     * Constructor
     *
     * @param SabaiFramework_User_Identity $identity
     * @return SabaiFramework_User
     */
    public function __construct(SabaiFramework_User_Identity $identity)
    {
        $this->_identity = $identity;
    }

    /**
     * Magic method
     *
     * @param string $key
     */
    public function __get($key)
    {
        return $this->_identity->$key;
    }

    /**
     * Returns an identy object for the user
     *
     * @return SabaiFramework_User_Identity
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Checks if the user has an anonymous identity
     *
     * @return bool
     */
    public function isAnonymous()
    {
        return $this->_identity->isAnonymous();
    }

    /**
     * Sets the user identity as a super user
     *
     * @param bool $flag
     */
    public function setAdministrator($flag = true)
    {
        $this->_administrator = $flag;

        return $this;
    }

    /**
     * Checks whether this user is a super user or not
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->_administrator;
    }

    /**
     * Adds a permission
     *
     * @param string $perm
     */
    public function addPermission($perm)
    {
        $this->_permissions[$perm] = 1;

        return $this;
    }

    /**
     * Checks whether the user has a certain permission, e.g. $user->hasPermission('A').
     * Pass in an array of permission names to check if the user has one of the supplied
     * permissions, e.g. $user->hasPermission(array('A', 'B')).
     * It is also possible to check whether the user has a group of certain permissions
     * by passing in an array of permission array, e.g. $user->hasPermission(array(array('A', 'B', 'C'))).
     * For another example, in order to see whether the user has permission A or both permissions B and C
     * would be: $user->hasPermission(array('A', array('B', 'C')))
     *
     * @param mixed $perm string or array
     * @return bool
     */
    public function hasPermission($perm)
    {
        if ($this->isAdministrator()) return true;

        if (empty($this->_permissions)) return false;

        if (is_string($perm)) {
            return isset($this->_permissions[$perm]);
        }
        
        foreach ($perm as $_perm) {
            foreach ((array)$_perm as $__perm) {
                if (!isset($this->_permissions[$__perm])) continue 2;
            }
            return true;
        }
        return false;
    }

    /**
     * Returns the permissions array
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->_permissions;
    }

    /**
     * Sets the permissions array
     *
     * @param array $permissions
     */
    public function setPermissions(array $permissions)
    {
        $this->_permissions = $permissions;

        return $this;
    }

    /**
     * Checks if the user object is finalized
     *
     * @return bool
     */
    public function isFinalized()
    {
        return $this->_finalized;
    }

    /**
     * Sets the user object as finalized
     *
     * @param bool $flag
     */
    public function setFinalized($flag = true)
    {
        $this->_finalized = $flag;

        return $this;
    }

    /**
     * Finalize the user object
     *
     * @param bool $flag
     */
    public function finalize($flag = null)
    {
        if (is_bool($flag)) $this->_finalize = $flag;

        return $this->_finalize;
    }
}