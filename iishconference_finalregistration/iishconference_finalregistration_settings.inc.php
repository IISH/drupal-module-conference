<?php
/**
 * @file
 * Describes the form for changing the settings of this module
 */

/**
 * Implements hook_form()
 */
function iishconference_finalregistration_settings_form($form, &$form_state) {
	$form['payway_address'] = array(
		'#type' => 'textfield',
		'#title' => t('PayWay address'),
		'#default_value' => variable_get('payway_address', 'https://payway.socialhistoryservices.org/api/'),
		'#description' => t('Enter the base address of the PayWay application. Make sure to end it with \'/\'.'),
	);

	$form['passphrase_payway_in'] = array(
		'#type' => 'textfield',
		'#title' => t('Passphrase IN'),
		'#default_value' => variable_get('passphrase_payway_in', ''),
		'#description' => t('Enter the pass phrase used for creating the signature added to the messages FOR PayWay.'),
	);

	$form['passphrase_payway_out'] = array(
		'#type' => 'textfield',
		'#title' => t('Passphrase OUT'),
		'#default_value' => variable_get('passphrase_payway_out', ''),
		'#description' => t('Enter the pass phrase used for validating the signature added to the messages FROM PayWay.'),
	);

	$form['payway_project'] = array(
		'#type' => 'textfield',
		'#title' => t('PayWay project name'),
		'#default_value' => variable_get('payway_project', ''),
		'#description' => t('Enter the project name of your PayWay account.'),
	);

	$form['payment_accepted_email_template_id'] = array(
		'#type' => 'textfield',
		'#title' => t('Email template id for accepted payments'),
		'#default_value' => variable_get('payment_accepted_email_template_id', ''),
		'#description' => t('Enter a valid identifier of an email template in the database.'),
	);

	$form['payment_bank_transfer_email_template_id'] = array(
		'#type' => 'textfield',
		'#title' => t('Email template id for bank transfer information'),
		'#default_value' => variable_get('payment_bank_transfer_email_template_id', ''),
		'#description' => t('Enter a valid identifier of an email template in the database.'),
	);

	$form['payment_show_days_session_planned'] = array(
		'#type' => 'checkbox',
		'#title' => t('Display days sessions planned?'),
		'#default_value' => variable_get('payment_show_days_session_planned', 1),
		'#description' => t('Should the final registration page display the days on which the logged in participant is planned for a session?'),
	);

	$form['date_close_bank_transfer'] = array(
		'#type' => 'date',
		'#title' => t('Closing date for bank transfers'),
		'#default_value' => variable_get('date_close_bank_transfer', ''),
		'#description' => t('Enter the final day bank transfers are still possible.'),
	);

	$form['date_close_final_registration'] = array(
		'#type' => 'date',
		'#title' => t('Closing date for final registration'),
		'#default_value' => variable_get('date_close_final_registration', ''),
		'#description' => t('Enter the final day final registration is still possible.'),
	);

	return system_settings_form($form);
}
