<?php
class Sabai_Helper_DropdownButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links, $size = 'small', $labelFormat = '%s')
    {
        if (!count($links)) return '';
        
        $dropdown_links = array();
        $dropdown_link_class = 'sabai-dropdown-link';
        foreach ($links as $key => $link) {
            $link->setAttribute('title', '');
            if (!$link->isActive()) {
                $dropdown_links[$key] = $link;
                $dropdown_links[$key]->setAttribute('class', ($class = $link->getAttribute('class')) ? $dropdown_link_class . ' ' . $class : $dropdown_link_class);
                continue;
            }
            $current = $this->_markCurrent($link, $size, $labelFormat);
        }
        if (!isset($current)) {
            $current = $this->_markCurrent(array_shift($links), $size, $labelFormat);
            array_shift($dropdown_links);
        }
        return count($dropdown_links)
            ? '<div class="sabai-btn-group">' . $current . '<ul class="sabai-dropdown-menu"><li>' . implode('</li><li>', $dropdown_links) . '</li></ul></div>'
            : (string)$current;
    }
    
    private function _markCurrent($link, $size, $labelFormat)
    {
        $dropdown_toggle_class = 'sabai-btn sabai-dropdown-toggle sabai-btn-' . $size;
        if ($class = $link->getAttribute('class')) {
            $dropdown_toggle_class .= ' ' . $class;
        }
        $label = $link->isNoEscape() ? $link->getLabel() : Sabai::h($link->getLabel());
        return $link->setActive(false)
            ->setAttribute('onclick', '')
            ->setAttribute('class', $dropdown_toggle_class)
            ->setAttribute('data-toggle', 'sabaidropdown')
            ->setLabel(sprintf($labelFormat, $label) . ' <span class="sabai-caret"></span>', false);
    }
}
