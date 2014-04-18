<?php
class Sabai_Addon_Voting_Helper_CastVote extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $tag, $value, array $options = array())
    {
        $settings = $application->Voting_TagSettings($tag);
        
        // Validate value
        if (!is_numeric($value)
            || $value > $settings['max']
            || $value < $settings['min']
            || ($value * 100) % ($settings['step'] * 100) !== 0 // avoid using float numbers for % operation
            || (empty($value) && !$settings['allow_empty'])
        ) {
            throw new Sabai_UnexpectedValueException('Invalid vote value');
        }
        
        $default = array(
            'comment' => '',
            'system' => false,
            'name' => '',
            'user_id' => null,
            'reference_id' => null, // required for edit/delte vote
            'is_edit' => false,
        );
        $options += $default;

        // If not a system vote...
        if (!$options['system']) {
        
            // Require additional permission to down vote
            if ($value < 0
                && $settings['require_vote_permissions']
                && $settings['require_vote_down_permission']
                && !$application->HasPermission($entity->getBundleName() . '_voting_down_' . $tag)
            ) {
                throw new Sabai_RuntimeException(__('You do not have the permission to perform this action.', 'sabai'));
            }  
            
            $user_id = isset($options['user_id']) ? $options['user_id'] : $application->getUser()->id;
            if (empty($settings['allow_multiple']) || !empty($options['reference_id'])) {
                $votes = $application->getModel('Vote', 'Voting')
                    ->entityType_is($entity->getType()) 
                    ->entityId_is($entity->getId())
                    ->userId_is($user_id)
                    ->tag_is($tag)
                    ->name_is($options['name']);
                if (!empty($options['reference_id'])) {
                    $votes->referenceId_is($options['reference_id']);
                }
                $vote = $votes->fetchOne('created', 'DESC');
        
                if ($vote) {
                    $prev_value = $vote->value;
                    // Has voted before
                    if ($vote->value == $value && !$options['is_edit']) {
                        // Same value, undo vote
                        $vote->markRemoved();
                        $value = false;
                    } else {
                        // Update vote
                        $vote->value = $value;
                    }
                } else {
                    $prev_value = false;
                    // New vote
                    $vote = $this->_createVote($application, $entity, $tag, $value, $user_id, $options['comment'], $options['name'], $options['reference_id']);
                }
            } else {
                $prev_value = false;
                // New vote
                $vote = $this->_createVote($application, $entity, $tag, $value, $user_id, $options['comment'], $options['name']);
            }
        } else {
            // Voting cast by the system
            $prev_value = false;
            // New vote
            $vote = $this->_createVote($application, $entity, $tag, $value, 0, $options['comment'], $options['name'], $options['reference_id']);
        }
        
        $vote->commit();
        
        // Calculate results and update entity
        $results = $application->getAddon('Voting')->recalculateEntityVotes($entity, $tag, $options['name']);

        $results['value'] = $value;
        $results['prev_value'] = $prev_value;
        
        // Notify voted
        $application->doEvent('VotingEntityVoted', array($entity, $tag, $results));
        // Notify by vote tag and bundle
        $event_name_suffix = implode('', array_map('ucfirst', explode('_', $tag)));
        $application->doEvent('VotingEntityVoted' . $event_name_suffix, array($entity, $results, $vote));
        $application->doEvent('Voting' . $application->Camelize($entity->getBundleType()) . 'EntityVoted' . $event_name_suffix, array($entity, $results, $vote));
        
        return $results;
    }
    
    private function _createVote(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $tag, $value, $userId, $comment, $name, $referenceId = null)
    {
        $vote = $application->getModel(null, 'Voting')->create('Vote')->markNew();
        $vote->entity_type = $entity->getType();
        $vote->entity_id = $entity->getId();
        $vote->bundle_id = $application->Entity_Bundle($entity)->id;
        $vote->tag = $tag;
        $vote->user_id = $userId;
        $vote->value = $value;
        $vote->comment = $comment;
        $vote->name = $name;
        $vote->reference_id = $referenceId;
        
        return $vote;
    }
}
