<?php
class Sabai_Addon_Voting_Helper_DeleteVotes extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds, $tag)
    {
        $entities = array();
        if (!is_array($entityIds) && $entityIds instanceof Sabai_Addon_Content_Entity) {
            $entity = $entityIds;
            $application->getAddon('Entity')->updateEntity($entity, array(
                'voting_' . $tag => false,
            ));
            $entities[$entity->getId()] = $entity;
        } else { 
            foreach ($application->Entity_Entities('content', (array)$entityIds) as $entity) {
                $application->getAddon('Entity')->updateEntity($entity, array(
                    'voting_' . $tag => false,
                ));
                $entities[$entity->getId()] = $entity;
            }
        }
        if (empty($entities)) {
            return;
        }
        
        $application->getModel('Vote', 'Voting')->entityId_in(array_keys($entities))->tag_is($tag)->fetch()->delete(true);
        $event_name_suffix = implode('', array_map('ucfirst', explode('_', $tag)));     
        foreach ($entities as $entity) {
            // Notify vote deleted
            $application->doEvent('VotingEntityVoteDeleted', array($entity, $tag));
            // Notify by vote tag, entity type, and bundle
            $application->doEvent('VotingEntityVoteDeleted' . $event_name_suffix, array($entity));
            $application->doEvent('Voting' . $application->Camelize($entity->getType()) . 'EntityVoteDeleted' . $event_name_suffix, array($entity));
            $application->doEvent('Voting' . $application->Camelize($entity->getType()) . $application->Camelize($entity->getBundleType()) . 'EntityVoteDeleted' . $event_name_suffix, array($entity));
        }
    }
}