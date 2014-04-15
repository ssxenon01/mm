<?php
class Sabai_Addon_DirectoryCSVImport_Controller_Admin_Import extends Sabai_Addon_Form_MultiStepController
{    
    protected function _getSteps(Sabai_Context $context)
    {
        return array('upload', 'settings', 'default_settings');
    }
    
    protected function _getFormForStepUpload(Sabai_Context $context, array &$formStorage)
    {
        return array(
            'file' => array(
                '#type' => 'file_file',
                '#title' => __('CSV file', 'sabai-directory'),
                '#upload_dir' => $this->getAddon('File')->getTmpDir(),
                '#allowed_extensions' => array('csv'),
                '#required' => true,
                // The finfo_file function used by the uploader to check mime types for CSV files is buggy. We can skip it safely here since this is for admins only.
                '#skip_mime_type_check' => true,
            ),
            'delimiter' => array(
                '#type' => 'textfield',
                '#title' => __('CSV file delimiter', 'sabai-directory'),
                '#size' => 5,
                '#description' => __('Enter the character to be used as field delimiter when parsing imported CSV file.', 'sabai-directory'),
                '#min_length' => 1,
                '#max_length' => 1,
                '#default_value' => ',',
                '#required' => true,
            ),
            'enclosure' => array(
                '#type' => 'textfield',
                '#title' => __('CSV file enclosure', 'sabai-directory'),
                '#size' => 5,
                '#description' => __('Enter the character to be used as field enclosure when parsing imported CSV file.', 'sabai-directory'),
                '#min_length' => 1,
                '#max_length' => 1,
                '#default_value' => '"',
                '#required' => true,
            ),
            'convert_encoding' => array(
                '#type' => 'checkbox',
                '#title' => __('Convert encoding of CSV file data to UTF-8', 'sabai-directory'),
                '#default_value' => false,
            ),
        );
    }
    
    protected function _getFormForStepSettings(Sabai_Context $context, array &$formStorage)
    {
        $csv_file = $formStorage['values']['upload']['file']['saved_file_path'];
        $csv_delimiter = $formStorage['values']['upload']['delimiter'];
        $csv_enclosure = $formStorage['values']['upload']['enclosure'];
        $csv_convert_encoding = !empty($formStorage['values']['upload']['convert_encoding']);
        if (false === $csv_columns = $this->_getCsvFileHeaders($context, $csv_file, $csv_delimiter, $csv_enclosure, $csv_convert_encoding)) {
            @unlink($csv_file);
            return false;
        }
        
        $fields = array(
            '' => __('Do not import', 'sabai-directory'),
            'title' => __('Title', 'sabai-directory'),
            'description' => __('Description', 'sabai-directory'),
            'category' => __('Category', 'sabai-directory'),
            'address' => __('Address', 'sabai-directory'),
            'address2' => __('Address 2', 'sabai-directory'),
            'city' => __('City', 'sabai-directory'),
            'state' => __('State/Province/Region', 'sabai-directory'),
            'country' => __('Country', 'sabai-directory'),
            'zip' => __('Zip/Postal Code', 'sabai-directory'),
            'lat' => __('Latitude', 'sabai-directory'),
            'lng' => __('Longitude', 'sabai-directory'),
            'date' => __('Published Date', 'sabai-directory'),
            'author_id' => __('Author User ID', 'sabai-directory'),
            'owner_id' => __('Owner User ID', 'sabai-directory'),
            'phone' => __('Phone Number', 'sabai-directory'),
            'mobile' => __('Mobile Number', 'sabai-directory'),
            'fax' => __('Fax Number', 'sabai-directory'),
            'email' => __('E-mail', 'sabai-directory'),
            'website' => __('Website', 'sabai-directory'),
            'twitter' => __('Twitter', 'sabai-directory'),
            'facebook' => __('Facebook URL', 'sabai-directory'),
            'googleplus' => __('Google+ URL', 'sabai-directory'),
        );
        // Add custom fields
        $custom_fields = array();
        foreach ($this->Entity_Field($context->bundle->name) as $field) {
            if (!$field->isCustomField()
                || !in_array($field->getFieldType(), array('boolean', 'text', 'string', 'choice', 'number', 'user', 'markdown_text', 'date_timestamp'))
            ) {
                continue;
            }
            
            $fields[$field->getFieldName()] = sprintf(__('Custom field - %s', 'sabai-directory'), $field->getFieldTitle());
            $custom_fields[$field->getFieldName()] = $field->getFieldType();
        }
        $formStorage['custom_fields'] = $custom_fields;
        
        $form = array(
            '#header' => array(
                '<div>' . __('Set up the associations between your CSV file data and directory listing fields. The "Category" field can be associated with more than one column if the field is configured to accept multiple values.', 'sabai-directory') . '</div>',
            ),
            '#fields' => $fields,
            'header' => array(
                '#type' => 'markup',
                '#markup' => '<table class="sabai-table"><thead><tr><th>' . __('CSV Column', 'sabai-directory') . '</th><th>' . __('Diretory Listing Field', 'sabai-directory') . '</th></tr></thead><tbody>'
            ),
            'fields' => array(
                '#tree' => true,
                '#class' => 'sabai-form-group',
                '#element_validate' => array(array(array($this, 'validateFields'), array($context))),
            ),
        );
        
        foreach ($csv_columns as $csv_column_key => $csv_column_label) {
            $form['fields'][$csv_column_key] = array(
                '#template' => false,
                '#prefix' => '<tr><td>'. $csv_column_label .'</td><td>',
                '#suffix' => '</td></tr>',
                //'#field_prefix' => $csv_column_label . ' &rarr;',
                '#type' => 'select',
                '#options' => $fields,
            );
        }
        $form['footer'] = array(
            '#type' => 'markup',
            '#markup' => '</tbody></table>',
        );
        
        return $form;
    }
    
    public function validateFields($form, &$value, $element, $context)
    {
        if (!in_array('title', $value)) {
            $form->setError(__('Please select a column for the "Title" field.', 'sabai-directory'));
        }
        $count = array_count_values($value);
        foreach ($count as $field_name => $_count) {
            if ($field_name !== '' && $_count > 1) {
                if ($field_name === 'category' && $this->_isMultipleCategoriesAllowed($context)) {
                    continue; // category field allows multiple values
                }
                $form->setError(sprintf(
                    __('You may not associate multiple columns with the "%s" field.', 'sabai-directory'),
                    $form->settings['#fields'][$field_name]
                ));
            }
        }
    }
    
    protected function _isMultipleCategoriesAllowed(Sabai_Context $context)
    {
        $category_field = $this->Entity_Field($context->bundle->name, 'directory_category');
        if (!$category_field) {
            return false; // this should never happen
        }
        return $category_field->getFieldMaxNumItems() > 1;
    }
    
    protected function _getFormForStepDefaultSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons[] = array('#value' => __('Import', 'sabai-directory'), '#btn_type' => 'primary');
        $form = array();
        
        $selected_fields = $formStorage['values']['settings']['fields'];
        if (!$category_columns = array_keys($selected_fields, 'category')) {
            $form['category'] = array(
                '#tree' => false,
                '#title' => __('Category', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'category_type' => array(
                    '#type' => 'radios',
                    '#default_value' => 'none',
                    '#options' => array(
                        'none' => __('No category', 'sabai-directory'),
                        'existing' => __('Select from existing categories', 'sabai-directory'),
                        'new' => __('Create a new category', 'sabai-directory'),
                    ),
                ),
                'category_existing' => array(
                    '#type' => 'select',
                    '#options' => array(0 => __('Select Category', 'sabai-directory')) + $this->Taxonomy_Tree($this->getAddon($context->bundle->addon)->getCategoryBundleName()),
                    '#states' => array(
                        'visible' => array(
                            'input[name="category_type[0]"]' => array('type' => 'value', 'value' => 'existing'),
                        ),
                    ),
                    '#empty_value' => 0,
                    '#required' => array($this, 'isExistingCategoryRequired'),
                    '#size' => 5,
                ),
                'category_new' => array(
                    '#type' => 'textfield',
                    '#size' => 40,
                    '#states' => array(
                        'visible' => array(
                            'input[name="category_type[0]"]' => array('type' => 'value', 'value' => 'new'),
                        ),
                    ),
                    '#required' => array($this, 'isNewCategoryRequired'),
                    '#attributes' => array('placeholder' => __('Enter a new category name', 'sabai-directory')),
                ),
            );
        } else {
            $form['category'] = array(
                '#tree' => false,
                '#title' => __('Category', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'category_create' => array(
                    '#type' => 'checkbox',
                    '#default_value' => true,
                    '#title' => __('Create non-existent categories', 'sabai-directory'),
                ),
            );
            $form['category_separator'] = array(
                '#field_prefix' => __('Delimiter', 'sabai-directory'),
                '#type' => 'textfield',
                '#description' => __('Enter the character to be used as delimiter if the category column contains multiple values.', 'sabai-directory'),
                '#default_value' => '',
                '#size' => 5,
                '#no_trim' => true,
            );
        }
        if (!in_array('date', $selected_fields)) {
            $form['date'] = array(
                '#type' => 'date_datepicker',
                '#title' => __('Published Date', 'sabai-directory'),
                '#current_date_selected' => true,
            );
        }
        if (!in_array('author_id', $selected_fields)) {
            $form['author_id'] = array(
                '#type' => 'autocomplete_user',
                '#title' => __('Author', 'sabai-directory'),
                '#default_value' => $this->getUser()->id,
                '#multiple' => false,
                '#width' => '200px',
            );
        }
        if (!in_array('owner_id', $selected_fields)) {
            $form['owner_id'] = array(
                '#type' => 'autocomplete_user',
                '#title' => __('Owner', 'sabai-directory'),
                '#multiple' => false,
                '#width' => '200px',
            );
        }
        if (in_array('address', $selected_fields)) {
            $address_format = '';
            if (in_array('address2', $selected_fields)) {
                $address_format .= ' {address2}';
            }
            if (in_array('city', $selected_fields)) {
                $address_format .= ' {city}';
            }
            if (in_array('state', $selected_fields)) {
                $address_format .= ' {state}';
            }
            if (in_array('zip', $selected_fields)) {
                $address_format .= ' {zip}';
            }
            if (in_array('country', $selected_fields)) {
                $address_format .= ' {country}';
            }
        
            if ($address_format !== '') {
                $form['address_format'] = array(
                    '#type' => 'textfield',
                    '#title' => __('Address Format', 'sabai-directory'),
                    '#default_value' => '{address}' . $address_format,              
                );
            }
            if (!in_array('lat', $selected_fields) || !in_array('lng', $selected_fields)) {
                $form['latlng'] = array(
                    '#tree' => false,
                    '#title' => __('Latitude / Longitude', 'sabai-directory'),
                    '#collapsible' => false,
                    '#class' => 'sabai-form-group',
                    'latlng_method' => array(
                        '#type' => 'radios',
                        '#default_value' => 'geocoding',
                        '#options' => array(
                            'geocoding' => __('Use Google geocoding service', 'sabai-directory'),
                            'manual' => __('Enter values manually', 'sabai-directory'),
                        ),
                        '#options_description' => array(
                            'geocoding' => __('Use the Google geocoding service to resolve the latitude/longitude coordinate from the address of each listing. This can be slow if the CSV data is large as well as there is a 2500 geocode request per day limit.', 'sabai-directory'),
                            'manual' => __('Manually enter the latitude/longitude coordinate that will be applied to all listings.', 'sabai-directory'),
                        ),
                    ),
                    'lat' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 9,
                        '#field_prefix' => __('Latitude:', 'sabai-directory'),
                        '#regex' => '/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,5}/',
                        '#numeric' => true,
                        '#states' => array(
                            'visible' => array(
                                'input[name="latlng_method[0]"]' => array('type' => 'value', 'value' => 'manual'),
                            ),
                        ),
                        '#required' => array($this, 'isLatLngRequired'),
                    ),
                    'lng' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 10,
                        '#field_prefix' => __('Longitude:', 'sabai-directory'),
                        '#regex' => '/^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,5}/',
                        '#numeric' => true,
                        '#states' => array(
                            'visible' => array(
                                'input[name="latlng_method[0]"]' => array('type' => 'value', 'value' => 'manual'),
                            ),
                        ),
                        '#required' => array($this, 'isLatLngRequired'),
                    ),
                );
            }
        }
        
        // Skip this step if no fields to configure
        if (empty($form)) {
            $next_step = $this->_skipStep($formStorage);
            return $this->_getForm($next_step, $context, $formStorage);
        }
            
        $form['#header'] = array(
            '<div>' . __('Here you can set the default values and settings for the fields below. The values and settings on this page will be applied to all directory listings created.', 'sabai-directory') . '</div>',
        );
        
        return $form;
    }
    
    public function isExistingCategoryRequired($form)
    {
        return $form->values['category_type'] === 'existing';
    }
        
    public function isNewCategoryRequired($form)
    {
        return $form->values['category_type'] === 'new';
    }
    
    public function isLatLngRequired($form)
    {
        return $form->values['latlng_method'] === 'manual';
    }
    
    protected function _submitFormForStepDefaultSettings(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        @set_time_limit(0);
        
        $csv_file = $form->storage['values']['upload']['file']['saved_file_path'];
        $csv_delimiter = $form->storage['values']['upload']['delimiter'];
        $csv_enclosure = $form->storage['values']['upload']['enclosure'];
        if (!$fp = $this->_getCsvFile($context, $csv_file, false)) {
            @unlink($csv_file);
            return false;
        }
        
        if (false === $csv_columns = $this->_getCsvFileHeaders($context, $fp, $csv_delimiter, $csv_enclosure, false)) {
            @unlink($csv_file);
            return false;
        }
        
        $settings = $form->storage['values']['settings']['fields'];
        $geocode = !empty($form->values['latlng_method']) && $form->values['latlng_method'] === 'geocoding';
        
        $category = null;
        $category_bundle_name = $this->getAddon($context->bundle->addon)->getCategoryBundleName();
        $category_ids = array();
        if (!in_array('category', $settings)) {
            switch ($form->values['category_type']) {
                case 'new':
                    $category_entity = $this->getAddon('Entity')->createEntity($category_bundle_name, array('taxonomy_term_title' => $form->values['category_new']));
                    $category = array($category_entity->getId());
                    break;
                case 'existing':
                    $category = array($form->values['category_existing']);
                    break;
            }
        }
        
        $defaults = array(
            'category' => $category,
            'date' => !empty($form->values['date']) ? $form->values['date'] : time(),
            'author_id' => !empty($form->values['author_id']) ? $form->values['author_id'] : null,
            'owner_id' => !empty($form->values['owner_id']) ? $form->values['owner_id'] : null,
            'lat' => !$geocode && !empty($form->values['lat']) ? $form->values['lat'] : null,
            'lng' => !$geocode && !empty($form->values['lng']) ? $form->values['lng'] : null,
        );
        
        $rows_imported = 0;
        $row_number = 1;
        $rows_geocoded = 0;
        $rows_failed = array();
        while (false !== $csv_row = fgetcsv($fp, 0, $form->storage['values']['upload']['delimiter'], $form->storage['values']['upload']['enclosure'])) {
            ++$row_number;
            if (empty($csv_row) || !is_array($csv_row)) {
                continue;
            }
            $row = array('category' => array());
            // Load row data from CSV
            foreach ($csv_columns as $csv_row_key => $csv_column_name) {
                if (isset($settings[$csv_row_key])) {
                    if ($settings[$csv_row_key] === 'category') {
                        if (!empty($form->values['category_separator'])) {
                            foreach (explode($form->values['category_separator'], $csv_row[$csv_row_key]) as $_category) {
                                $row['category'][] = trim($_category);
                            }
                        } else {
                            $row['category'][] = $csv_row[$csv_row_key];
                        }
                    } else {
                        $row[$settings[$csv_row_key]] = $csv_row[$csv_row_key];
                    }
                }
            }
            // Convert category names to IDs
            if (!empty($row['category'])) {
                // Check if any term slugs were specified instead of IDs
                $category_slugs = $category_labels = array();
                foreach ($row['category'] as $k => $category_name) {
                    if (!is_numeric($category_name)) {
                        $category_slug = $this->Slugify($category_name);
                        if (isset($category_ids[$category_slug])) {
                            if (false !== $category_ids[$category_slug]) {
                                $row['category'][$k] = $category_ids[$category_slug];
                            }
                        } else {
                            $category_slugs[$category_slug] = $category_slug;
                            $category_labels[$category_slug] = $category_name;
                            unset($row['category'][$k]);
                        }
                    }
                }
                if (!empty($category_slugs)) {
                    foreach ($this->getModel('Term', 'Taxonomy')->entityBundleName_is($category_bundle_name)->name_in($category_slugs)->fetch() as $category) {
                        $row['category'][] = $category->id;
                        $category_ids[$category->name] = $category->id;
                        unset($category_slugs[$category->name]); // found
                    }
                    // Categories not found. Create them and Cache ID to prevent from querying again
                    foreach ($category_slugs as $category_slug) {
                        if (!empty($form->values['category_create'])) {
                            // Create category
                            $category_entity = $this->getAddon('Entity')->createEntity($category_bundle_name, array('taxonomy_term_title' => $category_labels[$category_slug]));
                            $category_ids[$category_slug] = $row['category'][] = $category_entity->getId();
                        } else {
                            $category_ids[$category_slug] = false; // invalid category
                        }
                    }
                }
            }
            
            // Format address
            if (!empty($form->values['address_format'])) {
                $row['address'] = str_replace(
                    array('{address}', '{address2}', '{city}', '{state}', '{zip}', '{country}'),
                    array($row['address'], (string)@$row['address2'], (string)@$row['city'], (string)@$row['state'], (string)@$row['zip'], (string)@$row['country']),
                    $form->values['address_format']
                );
            }
            
            // Append default setting values
            $row += $defaults;
            
            // Init directory listing values
            $values = array(
                'content_post_title' => $row['title'],
                'content_body' => isset($row['description']) ? array('text' => $row['description'], 'filtered_text' => $row['description']) : null,
                'directory_location' => array(
                    'address' => $row['address'],
                ),
                'content_post_published' => !empty($row['date']) && is_numeric($row['date']) ? (int)$row['date'] : strtotime($row['date']),
                'content_post_user_id' => !empty($row['author_id']) ? $row['author_id'] : $defaults['author_id'],
                'directory_claim' => array(
                    'claimed_by' => !empty($row['owner_id']) ? $row['owner_id'] : $defaults['owner_id'],
                    'claimed_at' => time(),
                ),
                'directory_category' => !empty($row['category']) ? array_values($row['category']) : $defaults['category'],
                'directory_contact' => array(
                    'phone' => isset($row['phone']) ? $row['phone'] : null,
                    'mobile' => isset($row['mobile']) ? $row['mobile'] : null,
                    'fax' => isset($row['fax']) ? $row['fax'] : null,
                    'email' => isset($row['email']) ? $row['email'] : null,
                    'website' => isset($row['website'])
                        ? strpos($row['website'], 'http') === 0 ? $row['website'] : 'http://' . $row['website'] : null,
                ),
                'directory_social' => array(
                    'twitter' => isset($row['twitter']) ? str_replace('http://twitter.com/', '', $row['twitter']) : null,
                    'facebook' => isset($row['facebook']) ? $row['facebook'] : null,
                    'googleplus' => isset($row['googleplus']) ? $row['googleplus'] : null,
                ),
            );
            
            // Process custom fields
            if (!empty($form->storage['custom_fields'])) {
                foreach ($form->storage['custom_fields'] as $custom_field_name => $custom_field_type) {
                    if (!isset($row[$custom_field_name]) || !strlen($row[$custom_field_name])) {
                        continue;
                    }
                    switch ($custom_field_type) {
                        case 'choice':
                            $values[$custom_field_name] = explode(',', $row[$custom_field_name]);
                            break;
                        case 'markdown_text':
                            $values[$custom_field_name] = array('text' => $row[$custom_field_name], 'filtered_text' => $row[$custom_field_name]);
                            break;
                        default:
                            $values[$custom_field_name] = $row[$custom_field_name];
                    }
                }
            }
            
            try {
                if (empty($row['lat']) || empty($row['lng'])) {
                    if ($geocode) {
                        $geocode_result = $this->GoogleMaps_GoogleGeocode($row['address']);
                        $values['directory_location']['lat'] = $geocode_result['lat'];
                        $values['directory_location']['lng'] = $geocode_result['lng'];
                        ++$rows_geocoded;
                        if ($rows_geocoded % 10 === 0) {
                            sleep(1); // this is to prevent rate limit of 10 requests per second
                        }
                    }
                } else {
                    $values['directory_location']['lat'] = $row['lat'];
                    $values['directory_location']['lng'] = $row['lng'];
                }
                $this->getAddon('Entity')->createEntity($context->bundle, $values);
                ++$rows_imported;
            } catch (Exception $e) {
                $rows_failed[$row_number] = $e->getMessage();
            }
        }
        $form->storage['rows_imported'] = $rows_imported;
        $form->storage['rows_failed'] = $rows_failed;
    }

    protected function _complete(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        $context->addTemplate('form_results');
        $success_count = $form->storage['rows_imported'];
        $context->success = sprintf(_n('%d row imported successfullly.', '%d rows imported successfullly.', $success_count, 'sabai-directory'), $success_count);
        $failed_count = count($form->storage['rows_failed']);
        if ($failed_count) {
            $error = array();
            foreach ($form->storage['rows_failed'] as $row_num => $error_message) {
                $error[] = sprintf(__('CSV data on row number %d could not be imported: %s', 'sabai-directory'), $row_num, $error_message);
            }
            $context->error = $error;
        }
        @unlink($form->storage['values']['upload']['file']['saved_file_path']);
    }
    
    protected function _getCsvFile(Sabai_Context $context, $csvFilePath, $toUtf8 = false)
    {
        if (false === $fp = fopen($csvFilePath, 'r')) {
            $context->setError(sprintf(__('An error occurred while opening the CSV file: %s', 'sabai-directory'), $csvFilePath));
            return false;
        }
        if ($toUtf8) {
            $contents = fread($fp, filesize($csvFilePath));
            fclose($fp);
            if ($contents === false) {
                $context->setError(sprintf(__('An error occurred while reading the CSV file: %s', 'sabai-directory'), $csvFilePath));
                return false;
            }
            if (false === $fp = fopen($csvFilePath, 'w+')) {
                $context->setError(__('An error occurred while converting the encoding of CSV file to UTF-8.', 'sabai-directory'));
                return false;
            }
            fwrite($fp, mb_convert_encoding($contents, 'UTF-8', mb_detect_encoding($contents)));
            rewind($fp);
        }
        return $fp;
    }
    
    protected function _getCsvFileHeaders(Sabai_Context $context, $csvFile, $delimiter, $enclosure, $toUtf8 = false)
    {
        if (!is_resource($csvFile)
            && (false === $csvFile = $this->_getCsvFile($context, $csvFile, $toUtf8))
        ) {
            return false;
        } 
        if (false === $csv_headers = fgetcsv($csvFile, 0, $delimiter, $enclosure)) {
            $context->setError(__('An error occurred while parsing the header row of the CSV file.', 'sabai-directory'));
            return false;
        }
        return $csv_headers;
    }
}