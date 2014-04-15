<?php
abstract class Sabai_Addon_PaidListings_Controller_AddPlan extends Sabai_Addon_Form_MultiStepController
{    
    protected function _getSteps(Sabai_Context $context)
    {
        return array('select_plan_type', 'add_plan');
    }
    
    protected function _getFormForStepSelectPlanType(Sabai_Context $context, array &$formStorage)
    {
        $options = array();
        foreach ($this->_getPlanTypes($context) as $type => $type_info) {
            $options[$type] = $type_info['title'];
        }
        return array(
            'plan_type' => array(
                '#title' => __('Plan Type', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => $options,
                '#required' => true,
                '#default_value' => $context->getRequest()->asStr('type', array_shift(array_keys($options))),
            ),
        );
    }
    
    protected function _getFormForStepAddPlan(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons['submit'] = array(
            '#value' => __('Add Plan', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        $form = array(
            'settings' => array(
                '#title' => __('Basic Settings', 'sabai-directory'),
                '#tree' => false,
                'name' => array(
                    '#type' => 'textfield',
                    '#title' => __('Plan Name', 'sabai-directory'),
                    '#required' => true,
                ),
                'description' => array(
                    '#type' => 'textarea',
                    '#title' => __('Description', 'sabai-directory'),
                    '#rows' => 3,
                ),
                'active' => array(
                    '#type' => 'radios',
                    '#title' => __('Enabled', 'sabai-directory'),
                    '#options' => array(1 => __('Yes', 'sabai-directory'), 0 => __('No', 'sabai-directory')),
                    '#default_value' => 1,
                    '#required' => true,
                    '#class' => 'sabai-form-inline',
                ),
                'weight' => array(
                    '#type' => 'textfield',
                    '#title' => __('Display Order', 'sabai-directory'),
                    '#integer' => true,
                    '#size' => 5,
                    '#default_value' => 0,
                    '#min_value' => 0,
                    '#max_value' => 999,
                ),
                'price' => array(
                    '#type' => 'textfield',
                    '#title' => __('Price', 'sabai-directory'),
                    '#numeric' => true,
                    '#size' => 5,
                    '#required' => true,
                    '#field_prefix' => $this->PaidListings_Currencies($this->_getCurrency($context), false),
                ),
            ),
            'features' => array('#tree' => true),
        );
        $plan_types = $this->_getPlanTypes($context);
        $plan_type = $formStorage['values']['select_plan_type']['plan_type'];
        $i = 0;
        foreach ($plan_types[$plan_type]['features'] as $feature_name) {
            $ifeature = $this->PaidListings_FeatureImpl($feature_name);
            $feature_info = $ifeature->paidListingsFeatureGetInfo();
            $form['features'][$feature_name] = $ifeature->paidListingsFeatureGetSettingsForm($feature_info['default_settings'], array('features')) + array('#title' => $feature_info['label']);
            if (!empty($plan_types[$plan_type]['default_feature']) && in_array($feature_name, (array)$plan_types[$plan_type]['default_feature'])) {
                $form['features'][$feature_name]['#weight'] = 0;
                $form['features'][$feature_name]['enable'] = array(
                    '#type' => 'hidden',
                    '#value' => 1,
                );
            } else {
                $form['features'][$feature_name]['#weight'] = ++$i;
                $form['features'][$feature_name]['enable'] = array(
                    '#type' => 'checkbox',
                    '#default_value' => false,
                    '#title' => isset($form['features'][$feature_name]['enable']['#title']) ? $form['features'][$feature_name]['enable']['#title'] : __('Enable this feature', 'sabai-directory'),
                );
            }            
        }
        
        return $form;
    }
    
    protected function _submitFormForStepAddPlan(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        $plan = $this->getModel(null, 'PaidListings')->create('Plan')->markNew();
        $plan->name = $form->values['name'];
        $plan->description = $form->values['description'];
        $plan->active = (bool)$form->values['active'];
        $plan->weight = (int)$form->values['weight'];
        $plan->price = $form->values['price'];
        $plan->type = $form->storage['values']['select_plan_type']['plan_type'];
        $plan->features = $form->values['features'];
        $plan->commit();
    }
  
    protected function _complete(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        $context->setSuccess($this->_getPlansUrl($context, array('type' => $form->storage['values']['select_plan_type']['plan_type'])));
    }
    
    protected function _getPlansUrl(Sabai_Context $context, array $params)
    {
        return $this->Url(dirname($context->getRoute()), $params);
    }
    
    abstract protected function _getPlanTypes(Sabai_Context $context);
    abstract protected function _getCurrency(Sabai_Context $context);
}