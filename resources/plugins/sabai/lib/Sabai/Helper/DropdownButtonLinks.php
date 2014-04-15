<?php
class Sabai_Helper_DropdownButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links, $size = 'small', $labelFormat = '%s')
    {
        if (!count($links)) return '';
        
        foreach ($links as $key => $link) {
            $link->setAttribute('title', '');
            if (!$link->isActive()) {
                continue;
            }
            
            $current = $this->_markCurrent($link, $size, $labelFormat);
            unset($links[$key]);
        }
        if (!isset($current)) {
            $current = $this->_markCurrent(array_shift($links), $size, $labelFormat);
        }
        return count($links)
            ? '<div class="sabai-btn-group">' . $current . '<ul class="sabai-dropdown-menu"><li>' . implode('</li><li>', $links) . '</li></ul></div>'
            : (string)$current;
    }
    
    private function _markCurrent($link, $size, $labelFormat)
    {
        $class = 'sabai-btn sabai-dropdown-toggle sabai-btn-' . $size;
        if ($_class = $link->getAttribute('class')) {
            $class .= ' ' . $_class;
        }
        $label = $link->isNoEscape() ? $link->getLabel() : Sabai::h($link->getLabel());
        return $link->setActive(false)
            ->setAttribute('onclick', '')
            ->setAttribute('class', $class)
            ->setAttribute('data-toggle', 'sabaidropdown')
            ->setLabel(sprintf($labelFormat, $label) . ' <span class="sabai-caret"></span>', false);
    }
}