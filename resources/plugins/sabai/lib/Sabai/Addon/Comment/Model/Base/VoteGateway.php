<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_Comment_Model_Base_VoteGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'comment_vote';
    }

    public function getFields()
    {
        return array('vote_value' => SabaiFramework_Model::KEY_TYPE_INT, 'vote_tag' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'vote_id' => SabaiFramework_Model::KEY_TYPE_INT, 'vote_created' => SabaiFramework_Model::KEY_TYPE_INT, 'vote_updated' => SabaiFramework_Model::KEY_TYPE_INT, 'vote_post_id' => SabaiFramework_Model::KEY_TYPE_INT, 'vote_user_id' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'vote_id';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %scomment_vote WHERE vote_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %scomment_vote WHERE vote_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$scomment_vote comment_vote WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['vote_created'] = time();
        $values['vote_updated'] = 0;
        return sprintf('INSERT INTO %scomment_vote(vote_value, vote_tag, vote_created, vote_updated, vote_post_id, vote_user_id) VALUES(%d, %s, %d, %d, %d, %d)', $this->_db->getResourcePrefix(), $values['vote_value'], $this->_db->escapeString($values['vote_tag']), $values['vote_created'], $values['vote_updated'], $values['vote_post_id'], $values['vote_user_id']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['vote_updated'];
        $values['vote_updated'] = time();
        return sprintf('UPDATE %scomment_vote SET vote_value = %d, vote_tag = %s, vote_updated = %d, vote_post_id = %d, vote_user_id = %d WHERE vote_id = %d AND vote_updated = %d', $this->_db->getResourcePrefix(), $values['vote_value'], $this->_db->escapeString($values['vote_tag']), $values['vote_updated'], $values['vote_post_id'], $values['vote_user_id'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$scomment_vote WHERE vote_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['vote_updated'] = 'vote_updated=' . time();
        return sprintf('UPDATE %scomment_vote comment_vote SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE comment_vote FROM %1$scomment_vote comment_vote WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$scomment_vote comment_vote WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _afterInsert1($id, array $new)
    {
    }

    protected function _afterDelete1($id, array $old)
    {
    }

    protected function _afterUpdate1($id, array $new, array $old)
    {
    }

    protected function _afterInsert($id, array $new)
    {
        $this->_afterInsert1($id, $new);
    }

    protected function _afterUpdate($id, array $new, array $old)
    {
        $this->_afterUpdate1($id, $new, $old);
    }

    protected function _afterDelete($id, array $old)
    {
        $this->_afterDelete1($id, $old);
    }
}