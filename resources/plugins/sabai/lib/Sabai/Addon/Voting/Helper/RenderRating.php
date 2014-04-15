<?php
class Sabai_Addon_Voting_Helper_RenderRating extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $name = '')
    {
        $values = $entity->getFieldValue('voting_rating');
        if (!isset($values[$name])
            || empty($values[$name]['count'])
        ) {
            return '';
        }
        $rating = $values[$name];
        $rounded = round($rating['average'], 1) * 10;
        $remainder = $rounded % 5;
        $rounded -= $remainder;
        if ($remainder > 2) {
            $rounded += 5;
        }
        return sprintf(
            '<span class="sabai-rating sabai-rating-%d" title="%s"></span>',
            $rounded,
            sprintf(__('%.2f out of 5 stars', 'sabai'), $rating['average'])
        );
    }
}