<?php
class Sabai_Addon_Content_Helper_RenderTitle extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $link = true, Sabai_Addon_Content_Entity $parent = null, $parentTitleFormat = null, $featuredIcon = 'certificate', $altTitle = null)
    {
        $ret = array();
        // Define icon labels
        $labels = empty($entity->data['content_icons']) ? array() : $entity->data['content_icons'];
        if ($featuredIcon && $entity->isFeatured()) {
            $labels['featured'] = array(
                'title' => __('This post is featured.', 'sabai'),
                'icon' => $featuredIcon,
                'class' => 'sabai-content-featured',
            );
        }
        // Render icon labels
        foreach ($labels as $label) {
            if (isset($label['color'])) {
                $style = ' style="color:' . Sabai::h($label['color']) . ';"';
            } else {
                $style = '';
            }
            if (isset($label['class'])) {
                $icon = '<i class="sabai-content-icon '. Sabai::h($label['class']) .' sabai-icon-' . Sabai::h($label['icon']) . '" title="'. Sabai::h($label['title']) .'"></i>';
            } else {
                $icon = '<i class="sabai-content-icon sabai-icon-' . Sabai::h($label['icon']) . '" title="'. Sabai::h($label['title']) .'"></i>';
            }
            if (isset($label['icon_count'])) {
                $icon = str_repeat($icon, $label['icon_count']);
            }
            $ret[] = $icon;
        }
        // Render main title part
        if (is_object($parent)) {
            // Use parent title and custom title format if set
            $title = isset($altTitle) ? $altTitle : $parent->getTitle();
            if ($link) {
                $title = $application->Entity_Permalink($entity, array('title' => $title));
            } else {
                $title = Sabai::h($title);
            }
            if (isset($parentTitleFormat)) {
                $title = sprintf($parentTitleFormat, $title);
            }
        } else {
            $title = isset($altTitle) ? $altTitle : $entity->getTitle();
            if ($link) {
                $title = $application->Entity_Permalink($entity, array('title' => $title));
            } else {
                $title = Sabai::h($title);
            }
        }      
        $ret[] = $title;
        // Output
        return implode(' ', $ret);
    }
}