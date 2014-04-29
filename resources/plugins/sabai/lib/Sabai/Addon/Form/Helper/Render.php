<?php
class Sabai_Addon_Form_Helper_Render extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Form_Form $form, $extraJs = '')
    {
        list($html, $js) = $application->getAddon('Form')->buildForm($form->settings, !$form->rebuild, $form->values, $form->getErrors())->render();
        return sprintf('
%s
<script type="text/javascript">
%s
%s
</script>', $html, $js, $extraJs);
    }
}