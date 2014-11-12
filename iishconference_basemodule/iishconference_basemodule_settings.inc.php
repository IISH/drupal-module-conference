<?php
/**
 * @file
 * Describes the form for changing the settings of this module
 */

/**
 * Implements hook_form()
 */
function iishconference_basemodule_settings_form($form, &$form_state) {

	// +-------------------------------------------------------------------------------------------------------------+
	// API SETTINGS

    $form['api_settings'] = array(
        '#type' => 'fieldset',
        '#title' => iish_t('Conference Management System API'),
    );

    $form['api_settings']['conference_client_id'] = array(
		'#type' => 'textfield',
		'#title' => iish_t('Client ID'),
		'#default_value' => variable_get('conference_client_id'),
		'#description' => iish_t('Enter your client ID for communicating with the API. See table oauth_client_details for details.'),
	);

    $form['api_settings']['conference_client_secret'] = array(
        '#type' => 'textfield',
        '#title' => iish_t('Client secret'),
        '#default_value' => variable_get('conference_client_secret'),
        '#description' => iish_t('Enter your client secret for communicating with the API. See table oauth_client_details for details.'),
    );

    $form['api_settings']['conference_base_url'] = array(
        '#type' => 'textfield',
        '#title' => iish_t('Base URL of the CMS'),
        '#default_value' => variable_get('conference_base_url', 'https://conference.socialhistoryservices.org/'),
        '#description' => iish_t('Enter the base URL to communicate with. E.g. https://conference.socialhistoryservices.org/'),
    );

	// +-------------------------------------------------------------------------------------------------------------+
	// EVENT / DATE SETTINGS

	$form['event_date_settings'] = array(
		'#type' => 'fieldset',
		'#title' => iish_t('Conference event and date'),
	);

	$form['event_date_settings']['conference_event_code'] = array(
		'#type' => 'textfield',
		'#title' => iish_t('Event code'),
		'#default_value' => variable_get('conference_event_code'),
		'#description' => iish_t('Enter the code of the current event. E.g. \'esshc\''),
	);

	$form['event_date_settings']['conference_date_code'] = array(
		'#type' => 'textfield',
		'#title' => iish_t('Event date code'),
		'#default_value' => variable_get('conference_date_code'),
		'#description' => iish_t('Enter the code of the current event date. E.g. \'2014\''),
	);

	// +-------------------------------------------------------------------------------------------------------------+

	return system_settings_form($form);
}
