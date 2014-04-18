<?php
class Sabai_Helper_ButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links, $size = 'small', $showTooltip = true, $showLabel = false)
    {
        foreach ($links as $link) {
            $class = 'sabai-btn sabai-btn-' . $size;
            if ($_class = $link->getAttribute('class')) {
                $class .= ' ' . $_class;
            }
            $link->setAttribute('class', $class);
            if ($showTooltip) {
                $link->setAttribute('rel', 'sabaitooltip');
            }
            if (!$showLabel) {
                $link->setLabel('');
            }
        }
		return implode(PHP_EOL, $links);
    }
}