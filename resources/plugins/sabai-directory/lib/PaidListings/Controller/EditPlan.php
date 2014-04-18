<?php
abstract class Sabai_Addon_PaidListings_Controller_EditPlan extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Fetch plan to edit
        $context->plan = $this->getModel('Plan', 'PaidListings')->fetchById($this->_getPlanId($context));
        if (!$context->plan) {
            return false;
        }
     
        $this->_submitButtons['submit'] = array(
            '#value' => __('Save Changes', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        $url_params = array('sort' => $context->getRequest()->asStr('sort'), 'order' => $context->getRequest()->asStr('order'), 'type' => $context->getRequest()->asStr('type'));
        $this->_cancelUrl = $this->_getPlansUrl($context, $url_params);
        $form = array(
            'settings' => array(
                '#title' => __('Basic Settings', 'sabai-directory'),
                '#tree' => false,
                'name' => array(
                    '#type' => 'textfield',
                    '#title' => __('Plan Name', 'sabai-directory'),
                    '#required' => true,
                    '#default_value' => $context->plan->name,
                ),
                'description' => array(
                    '#type' => 'textarea',
                    '#title' => __('Description', 'sabai-directory'),
                    '#default_value' => $context->plan->description,
                    '#rows' => 3,
                ),
                'active' => array(
                    '#type' => 'radios',
                    '#title' => __('Enabled', 'sabai-directory'),
                    '#options' => array(1 => __('Yes', 'sabai-directory'), 0 => __('No', 'sabai-directory')),
                    '#default_value' => $context->plan->active,
                    '#required' => true,
                    '#class' => 'sabai-form-inline',
                ),
                'weight' => array(
                    '#type' => 'textfield',
                    '#title' => __('Display Order', 'sabai-directory'),
                    '#integer' => true,
                    '#size' => 5,
                    '#default_value' => $context->plan->weight,
                    '#min_value' => 0,
                    '#max_value' => 999,
                ),
                'price' => array(
                    '#type' => 'textfield',
                    '#title' => __('Price', 'sabai-directory'),
                    '#numeric' => true,
                    '#size' => 10,
                    '#required' => true,
                    '#field_prefix' => $this->PaidListings_Currencies($this->_getCurrency($context), false),
                    '#default_value' => $context->plan->price,
                ),
            ),
            'features' => array('#tree' => true),
        );
        $plan_types = $this->_getPlanTypes($context);
        $i = 0;
        foreach ($plan_types[$context->plan->type]['features'] as $feature_name) {
            $ifeature = $this->PaidListings_FeatureImpl($feature_name);
            $feature_info = $ifeature->paidListingsFeatureGetInfo();
            $form['features'][$feature_name] = $ifeature->paidListingsFeatureGetSettingsForm((array)@$context->plan->features[$feature_name] + $feature_info['default_settings'], array('features')) + array('#title' => $feature_info['label']);
            if (!empty($plan_types[$context->plan->type]['default_feature']) && in_array($feature_name, (array)$plan_types[$context->plan->type]['default_feature'])) {
                $form['features'][$feature_name]['#weight'] = 0;
                $form['features'][$feature_name]['enable'] = array(
                    '#type' => 'hidden',
                    '#value' => 1,
                );
            } else {
                $form['features'][$feature_name]['#weight'] = ++$i;
                $form['features'][$feature_name]['enable'] = array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($context->plan->features[$feature_name]['enable']),
                    '#title' => isset($form['features'][$feature_name]['enable']['#title']) ? $form['features'][$feature_name]['enable']['#title'] : __('Enable this feature', 'sabai-directory'),
                );
            }
        }
        
        // Add URL params as hidden
        foreach ($url_params as $key => $value) {
            $form[$key] = array(
                '#type' => 'hidden',
                '#value' => $value,
            );
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->plan->name = $form->values['name'];
        $context->plan->description = $form->values['description'];
        $context->plan->active = (bool)$form->values['active'];
        $context->plan->weight = (int)$form->values['weight'];
        $context->plan->price = $form->values['price'];
        $context->plan->features = $form->values['features'];
        $context->plan->commit();
        $context->setSuccess($this->_cancelUrl);
    }
    
    abstract protected function _getPlanTypes(Sabai_Context $context);
    
    abstract protected function _getPlansUrl(Sabai_Context $context, array $params);
    
    /**
     * @return intger
     */
    abstract protected function _getPlanId(Sabai_Context $context);
    abstract protected function _getCurrency(Sabai_Context $context);
}
