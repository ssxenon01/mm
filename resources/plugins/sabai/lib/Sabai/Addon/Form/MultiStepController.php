<?php
abstract class Sabai_Addon_Form_MultiStepController extends Sabai_Addon_Form_Controller
{
    protected $_nextBtnLabel, $_backBtnLabel;
    private $_currentStep, $_steps;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_steps = $this->_getSteps($context);
        
        // Reset all properties
        $this->_submitable = true;
        $this->_submitButtons = array();
        $this->_ajaxSubmit = null;
        $this->_ajaxCancelType = 'hide';
        $this->_ajaxCancelUrl = null;
        $this->_ajaxOnCancel = 'function(target){}';
        $this->_ajaxOnSuccess = null;
        $this->_ajaxOnSuccessFlash = false;
        $this->_ajaxOnError = null;
        $this->_ajaxOnContent = null;
        $this->_cancelUrl = null;
        $this->_cancelWeight = 99;
        $this->_js = '';
        $this->_successFlash = null;

        if (isset($formStorage['step'])
            && ($formStorage['step'] !== $this->_getFirstStep())
        ) {
            $this->_currentStep = $formStorage['step'];
            // Get form for the current step
            if (!$form = $this->_getForm($this->_currentStep, $context, $formStorage)) {
                return false;
            }
        } else {
            $this->_currentStep = $this->_getFirstStep();
            // Get form for the current step
            if (!$form = $this->_getForm($this->_currentStep, $context, $formStorage)) {
                return false;
            }
            $form['#disable_back_btn'] = true;            
        }
        if (false !== $this->_submitButtons) { // false means to never add submit buttons
            if (empty($this->_submitButtons)) {
                if (false !== $this->_getNextStep()) {
                    $this->_submitButtons[] = array(
                        '#value' => isset($this->_nextBtnLabel) ? $this->_nextBtnLabel : __('Next', 'sabai'),
                        '#btn_type' => 'primary',
                        '#weight' => 10,
                    );
                } else {
                    $this->_submitButtons[] = array(
                        '#btn_type' => 'primary',
                        '#value' => __('Save', 'sabai'),
                    );
                }
            }
            if (empty($form['#disable_back_btn'])) {
                $this->_submitButtons['back'] = array(
                    '#value' => isset($this->_backBtnLabel) ? $this->_backBtnLabel : __('Back', 'sabai'),
                    '#weight' => -10,
                    '#submit' => array(array(array($this, 'previousForm'), array($context))),
                    '#force_submit' => true, // skip validating the currently displayed form
                    '#btn_type' => false,
                );
            }
        }
        $form['#enable_storage'] = true;
        $form['#token_id'] = $this->_getFormName(get_class($this));

        return $form;
    }

    public function previousForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (false === $previous_step = $this->_getPreviousStep()) {
            // this should never happen
            throw new Sabai_RuntimeException('Previus step does not exist');
        }
        $form->storage['step'] = $previous_step;
        $form->values = $form->storage['values'][$form->storage['step']];
        $form->rebuild = true;
        $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);
    }

    final public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        // Save submitted form values
        $form->storage['values'][$this->_currentStep] = $form->values;

        // Call submit callback if any exists
        if (false === $this->_submitForm($this->_currentStep, $context, $form)) {
            if (!$form->hasError()) {
                $form->setError(__('An error occurred while submitting the form.', 'sabai'));
            }

            return; // Display the same form again
        }
        
        // Return if error or redirect
        if ($context->isError()
            || $context->isRedirect()
        ) {
            return;
        }

        // One or more steps may have been skipped, so make sure there are more steps afterwards.
        if (false === $this->_getNextStep()) {
            $this->_complete($context, $form);
            return;
        }

        // Advance to the next step
        $form->storage['step'] = $this->_getNextStep();
        if (!$form->redirect) {
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);
        }
    }

    final protected function _skipStep(&$formStorage)
    {
        $this->_currentStep = $formStorage['step'] = $this->_getNextStep();
        return $this->_currentStep;
    }
    
    protected function _getNextStep()
    {
        $next_step_key_index = array_search($this->_currentStep, $this->_steps) + 1;
        
        return isset($this->_steps[$next_step_key_index]) ? $this->_steps[$next_step_key_index] : false;
    }
    
    protected function _getPreviousStep()
    {
        $previous_step_key_index = array_search($this->_currentStep, $this->_steps) - 1;
        
        return isset($this->_steps[$previous_step_key_index]) ? $this->_steps[$previous_step_key_index] : false;
    }
    
    protected function _getFirstStep()
    {
        return array_shift(array_values($this->_steps));
    }
    
    protected function _getForm($step, Sabai_Context $context, array &$formStorage)
    {
        return call_user_func_array(array($this, '_getFormForStep' . $this->Camelize($step)), array($context, &$formStorage));
    }
    
    protected function _submitForm($step, Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        $method = '_submitFormForStep' . $this->Camelize($step);
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), array($context, $form));
        }
    }

    /**
     * @return array
     */
    abstract protected function _getSteps(Sabai_Context $context);

    abstract protected function _complete(Sabai_Context $context, Sabai_Addon_Form_Form $form);
}