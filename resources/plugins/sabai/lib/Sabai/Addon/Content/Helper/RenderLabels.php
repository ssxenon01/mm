<?php
class Sabai_Addon_Content_Helper_RenderLabels extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $labels = array(), $featuredIcon = 'certificate')
    {
        $labels += empty($entity->data['content_labels']) ? array() : $entity->data['content_labels'];
        if ($featuredIcon !== false && $entity->isFeatured()) {
            $labels['featured'] = array(
                'label' => __('Featured', 'sabai'),
                'title' => __('This post is featured.', 'sabai'),
                'icon' => $featuredIcon,
                'class' => 'sabai-content-featured',
            );
        }
        $ret = array();
        foreach ($labels as $label) {
            if (isset($label['icon'])) {
                $icon = '<i class="sabai-icon-' . Sabai::h($label['icon']) . '"></i>';
                if (isset($label['icon_count'])) {
                    $icon = str_repeat($icon, $label['icon_count']);
                }
                $icon .= ' ';
            } else {
                $icon = '';
            }
            $ret[] = sprintf(
                '<span class="sabai-label sabai-content-label %s" title="%s">%s%s</span>',
                isset($label['class']) ? Sabai::h($label['class']) : '',
                Sabai::h($label['title']),
                $icon, 
                Sabai::h($label['label'])
            );
        }
        return implode(' ', $ret);
    }
}