<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_Model
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
abstract class SabaiFramework_Model_TreeGateway extends SabaiFramework_Model_Gateway
{
    /**
     * @param string $id
     * @param array $fields
     * @return SabaiFramework_DB_Rowset
     */
    public function selectDescendants($id, array $fields = array())
    {
        return $this->selectBySQL($this->_getSelectDescendantsQuery($id, $fields));
    }

    /**
     * @param string $id
     * @return int
     */
    public function countDescendants($id)
    {
        return $this->_db->query($this->_getCountDescendantsQuery($id))->fetchSingle();
    }

    /**
     * @param array $ids
     * @return SabaiFramework_DB_Rowset
     */
    public function countDescendantsByIds($ids)
    {
        return $this->_db->query($this->_getCountDescendantsByIdsQuery($ids));
    }

    /**
     * @param string $id
     * @param array $fields
     * @return SabaiFramework_DB_Rowset
     */
    public function selectParents($id, array $fields = array())
    {
        return $this->selectBySQL($this->_getSelectParentsQuery($id, $fields));
    }

    /**
     * @param string $id
     * @return int
     */
    public function countParents($id)
    {
        return $this->_db->query($this->_getCountParentsQuery($id))->fetchSingle();
    }

    /**
     * @param array $ids
     * @return SabaiFramework_DB_Rowset
     */
    public function countParentsByIds($ids)
    {
        return $this->_db->query($this->_getCountParentsByIdsQuery($ids));
    }
}