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
abstract class SabaiFramework_Model_TreeEntityRepository extends SabaiFramework_Model_EntityRepository
{
    /**
     * Fetches all children entities
     *
     * @param string $id
     * @return SabaiFramework_Model_EntityCollection
     */
    public function fetchDescendantsByParent($id)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->selectDescendants($id, array()));
    }

    /**
     * Counts all children entities
     *
     * @param string $id
     * @return int
     */
    public function countDescendantsByParent($id)
    {
        return $this->_model->getGateway($this->getName())->countDescendants($id);
    }

    /**
     * Fethces all parent entities
     *
     * @param string $id
     * @return SabaiFramework_Model_EntityCollection
     */
    public function fetchParents($id)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->selectParents($id, array()));
    }

    /**
     * Counts all parent entities
     *
     * @param string $id
     * @return int
     */
    public function countParents($id)
    {
        return $this->_model->getGateway($this->getName())->countParents($id);
    }

    /**
     * Counts all parent entities
     *
     * @param array $ids
     * @return array
     */
    public function countParentsByIds($ids)
    {
        $ret = array();
        $rs = $this->_model->getGateway($this->getName())->countParentsByIds($ids);
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = $row[1];
        }
        return $ret;
    }

    /**
     * Counts all descendant entities
     *
     * @param array $ids
     * @return array
     */
    public function countDescendantsByIds($ids)
    {
        $ret = array();
        $rs = $this->_model->getGateway($this->getName())->countDescendantsByIds($ids);
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = $row[1];
        }
        return $ret;
    }
}