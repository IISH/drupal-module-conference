<?php

/**
 * Implements hook_form()
 */
function preregister_personalinfo_edit_form($form, &$form_state) {
	/**
	 * TODO:
	 * <style>
	 * <!--
	 * #edit-email {
	 * border: 0px;
	 * }
	 * // -->
	 * </style>
	 */

	$form['personal_info'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Personal info'),
	);

	$form['personal_info']['firstname'] = array(
		'#type'          => 'textfield',
		'#title'         => 'First name',
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_firstname']) ?
				$form_state['storage']['preregister_personalinfo_firstname'] : null,
	);

	$form['personal_info']['lastname'] = array(
		'#type'          => 'textfield',
		'#title'         => 'Last name',
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_lastname']) ?
				$form_state['storage']['preregister_personalinfo_lastname'] : null,
	);

	$form['personal_info']['gender'] = array(
		'#title'         => 'Gender',
		'#type'          => 'select',
		'#options'       => ConferenceMisc::getGenders(),
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_gender']) ?
				$form_state['storage']['preregister_personalinfo_gender'] : null,
	);

	$form['personal_info']['organisation'] = array(
		'#type'          => 'textfield',
		'#title'         => 'Organisation',
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_organisation']) ?
				$form_state['storage']['preregister_personalinfo_organisation'] : null,
	);

	$form['personal_info']['department'] = array(
		'#type'          => 'textfield',
		'#title'         => 'Department',
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_department']) ?
				$form_state['storage']['preregister_personalinfo_department'] : null,
	);

	$form['personal_info']['email'] = array(
		'#type'          => 'textfield',
		'#title'         => 'E-mail',
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $form_state['pre-registration']['user_email'],
		'#attributes'    => array('readonly' => 'readonly'),
	);

	$form['personal_info']['student'] = array(
		'#type'          => 'checkboxes',
		'#options'       => array(
			'y' => t('Please check if you are a (PhD) student'),
		),
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_student']) ?
				$form_state['storage']['preregister_personalinfo_student'] : array(),
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$form['personal_info']['cv'] = array(
			'#type'          => 'textarea',
			'#title'         => 'Curriculum Vitae',
			'#description'   => '<em>' . t('(max. 200 words)') . '</em>',
			'#rows'          => 2,
			'#default_value' => isset($form_state['storage']['preregister_personalinfo_cv']) ?
					$form_state['storage']['preregister_personalinfo_cv'] : null,
		);
	}

	$form['address'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Address'),
	);

	$form['address']['city'] = array(
		'#type'          => 'textfield',
		'#title'         => 'City',
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_city']) ?
				$form_state['storage']['preregister_personalinfo_city'] : null,
	);

	$form['address']['country'] = array(
		'#title'         => 'Country',
		'#type'          => 'select',
		'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getCountries()),
		'#required'      => true,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_country']) ?
				$form_state['storage']['preregister_personalinfo_country'] : null,
	);

	$form['communication_means'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Communication Means'),
	);

	$form['communication_means']['phone'] = array(
		'#type'          => 'textfield',
		'#title'         => 'Phone number',
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_phone']) ?
				$form_state['storage']['preregister_personalinfo_phone'] : null,
	);

	$form['communication_means']['mobile'] = array(
		'#type'          => 'textfield',
		'#title'         => 'Mobile number',
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => isset($form_state['storage']['preregister_personalinfo_mobile']) ?
				$form_state['storage']['preregister_personalinfo_mobile'] : null,
	);

	$form['communication_means']['extra_info'] = array(
		'#type'   => 'markup',
		'#markup' => '<span class="extra_uitleg">' .
			t('Please enter international numbers (including country prefix etc.)') .
			'</span>',
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit'] = array(
		'#type'  => 'submit',
		'#value' => 'Next'
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_personalinfo_edit_form_submit($form, &$form_state) {
	$form_state['storage']['preregister_personalinfo_firstname'] = $form_state['values']["firstname"];
	$form_state['storage']['preregister_personalinfo_lastname'] = $form_state['values']["lastname"];
	$form_state['storage']['preregister_personalinfo_gender'] = $form_state['values']["gender"];
	$form_state['storage']['preregister_personalinfo_organisation'] = $form_state['values']["organisation"];
	$form_state['storage']['preregister_personalinfo_department'] = $form_state['values']["department"];
	$form_state['storage']['preregister_personalinfo_city'] = $form_state['values']["city"];
	$form_state['storage']['preregister_personalinfo_country'] = $form_state['values']["country"];
	$form_state['storage']['preregister_personalinfo_phone'] = $form_state['values']["phone"];
	$form_state['storage']['preregister_personalinfo_mobile'] = $form_state['values']["mobile"];
	$form_state['storage']['preregister_personalinfo_student'] = $form_state['values']["student"];
	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$form_state['storage']['preregister_personalinfo_cv'] = $form_state['values']["cv"];
	}
}

