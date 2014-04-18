<?php
class Sabai_Addon_Voting_Model_VoteGateway extends Sabai_Addon_Voting_Model_Base_VoteGateway
{
    public function getResults($entityType, $entityId, $tag)
    {      
        $sql = sprintf(
            'SELECT vote_name, COUNT(*), SUM(vote_value), MAX(vote_created) FROM %svoting_vote WHERE vote_entity_type = %s AND vote_entity_id = %d AND vote_tag = %s GROUP BY vote_name',
             $this->_db->getResourcePrefix(),
             $this->_db->escapeString($entityType),
             $entityId,
             implode(',', array_map(array($this->_db, 'escapeString'), (array)$tag))
        );
        $rs = $this->_db->query($sql);
        $ret = array();
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = array('count' => (int)$row[1], 'sum' => $row[2], 'last_voted_at' => $row[3]);
        }
        
        return $ret;
    }
    
    public function getVotes($entityType, array $entityIds, $userId, array $tags = null)
    {
        $sql = sprintf(
            'SELECT vote_tag, vote_entity_id, vote_value FROM %svoting_vote WHERE vote_entity_type = %s AND vote_entity_id IN (%s) AND vote_user_id = %d %s',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($entityType),
            implode(',', array_map('intval', $entityIds)),
            $userId,
            isset($tags) ? sprintf('AND vote_tag IN (%s)', implode(',', array_map(array($this->_db, 'escapeString'), $tags))) : ''
        );
        $rs = $this->_db->query($sql);
        $ret = array();
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]][$row[1]] = $row[2];
        }
        return $ret;
    }
}