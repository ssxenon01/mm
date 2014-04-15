<?php
class Sabai_Addon_Directory_Helper_PageSettingsForm extends Sabai_Helper
{
    public function help(Sabai $application, $addon, array $parents = array(), $collapsed = false)
    {
        $config = $application->getAddon($addon)->getConfig('pages');
        array_push($parents, 'pages');
        $ret = array(
            'pages' => array(
                '#title' => __('Page Settings', 'sabai-directory'),
                '#collapsed' => $collapsed,
                'directory_slug' => array(
                    '#title' => __('Directory index slug', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 30,
                    '#default_value' => $config['directory_slug'],
                    '#required' => true,
                    '#description' => __('Must consist of lower-case alphanumeric characters, dashes, and underscores only.', 'sabai-directory'),
                    '#element_validate' => array(array(array($this, 'validateSlug'), array(array('sabai'), 'directory_slug', $parents))),
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#max_length' => 25,
                    '#field_prefix' => rtrim($application->getScriptUrl('main'), '/') . '/',
                ),
                'directory_title' => array(
                    '#title' => __('Directory index title', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 50,
                    '#default_value' => $config['directory_title'],
                    '#required' => true,
                ),
                'listing_slug' => array(
                    '#title' => __('Single listing slug', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 30,
                    '#default_value' => isset($config['listing_slug']) ? $config['listing_slug'] :'listing',
                    '#required' => true,
                    '#element_validate' => array(array(array($this, 'validateSlug'), array(array($application->getAddon($addon)->getSlug('reviews'), $application->getAddon($addon)->getSlug('photos'), $application->getAddon($addon)->getSlug('categories')), 'listing_slug', $parents))),
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#field_prefix' => rtrim($application->getScriptUrl('main'), '/') . '/' . $config['directory_slug'] . '/',
                    '#field_suffix' => '/[' . __('Single-Listing-Title', 'sabai-directory') . ']', 
                    '#description' => __('Must consist of lower-case alphanumeric characters, dashes, and underscores only.', 'sabai-directory'),
                ),
                'categories_slug' => array(
                    '#title' => __('Categories slug', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 30,
                    '#default_value' => isset($config['categories_slug']) ? $config['categories_slug'] :'categories',
                    '#required' => true,
                    '#element_validate' => array(array(array($this, 'validateSlug'), array(array(), 'categories_slug', $parents))),
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#field_prefix' => rtrim($application->getScriptUrl('main'), '/') . '/' . $config['directory_slug'] . '/',
                    '#description' => __('Must consist of lower-case alphanumeric characters, dashes, and underscores only.', 'sabai-directory'),
                ),
                'claim_slug' => array(
                    '#title' => __('Claim slug', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 30,
                    '#default_value' => isset($config['claim_slug']) ? $config['claim_slug'] :'claim',
                    '#required' => true,
                    '#element_validate' => array(array(array($this, 'validateSlug'), array(array(), 'claim_slug', $parents))),
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#field_prefix' => rtrim($application->getScriptUrl('main'), '/') . '/' . $config['directory_slug'] . '/[' . __('Single-Listing-Title', 'sabai-directory') . ']',
                    '#description' => __('Must consist of lower-case alphanumeric characters, dashes, and underscores only.', 'sabai-directory'),
                ),
            )
        );
        if (!$application->getAddon($addon)->hasParent()) {
            $ret['pages'] += array(
                'dashboard_slug' => array(
                    '#title' => __('Dashboard slug', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 30,
                    '#default_value' => $config['dashboard_slug'],
                    '#required' => true,
                    '#element_validate' => array(array(array($this, 'validateSlug'), array(array('sabai'), 'dashboard_slug', $parents))),
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#field_prefix' => rtrim($application->getScriptUrl('main'), '/') . '/',
                    '#description' => __('Must consist of lower-case alphanumeric characters, dashes, and underscores only.', 'sabai-directory'),
                ),
                'dashboard_title' => array(
                    '#title' => __('Dashboard title', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#size' => 50,
                    '#default_value' => $config['dashboard_title'],
                    '#required' => true,
                ),
                'dashboard_nolink' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Do not add a shortcut link to the dashboard', 'sabai-directory'),
                    '#default_value' => !empty($config['dashboard_nolink']),
                ),
            );
        }

        return $ret;
    }
    
    public function validateSlug(Sabai_Addon_Form_Form $form, &$value, $element, $reservedSlugs, $self, $parents)
    {
        if (!empty($reservedSlugs) && in_array($value, $reservedSlugs)) {
            $form->setError(__('The slug is reserved by the system.', 'sabai-directory'), $element);
            return;
        }
        $slug_values = $form->getValue($parents);
        foreach (array('directory_slug', 'dashboard_slug', 'listing_slug') as $slug) {
            if ($slug !== $self && array_key_exists($slug, $slug_values) && $slug_values[$slug] === $value) {
                $form->setError(__('The slug must be a unique value.', 'sabai-directory'), $element);
                return;
            }
        }
    }
}