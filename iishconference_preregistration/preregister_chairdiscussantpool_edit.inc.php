<?php 
/**
 * TODOEXPLAIN
 */
function preregister_chairdiscussantpool_edit_form( $form, &$form_state ) {
	$ct=0;

	// CHAIR DISCUSSANT POOL
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 7 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Chair / discussant pool</span>',
		);

	$form['volunteerchair'] = array(
		'#type' => 'checkboxes',
		'#options' => array(
						'y' => 'I would like to volunteer as Chair',
						),
		'#prefix' => '<div class="container-inline"><span style="vertical-align:top;">', 
		'#default_value' => isset( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair'] ) ? $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair'] : array(), 
		);

if ( SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1 ) {

	// netwerken
	$list_of_networks = getArrayOfNetworks();

	$form['volunteerchair_networks'] = array(
		'#type' => 'select',
		'#options' => $list_of_networks,
		'#prefix' => '</span>', 
		'#suffix' => '</div>', 
		'#multiple' => TRUE, 
		'#size' => 3, 
		'#description' => '<i>Use CTRL key to select multiple networks.</i>',

		'#states' => array(
			'visible' => array(
					':input[name="volunteerchair[y]"]' => array('checked' => TRUE),
				),
			),

		'#default_value' => isset( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks'] ) ? $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks'] : NULL, 
		);

} else {

		$form['volunteerchair_networks'] = array(
			'#type' => 'textfield',
			'#title' => 'Proposed network',
			'#prefix' => '</span><div style="display:none;">', 
			'#suffix' => '</div></div>', 
			'#default_value' => SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID),
			);

}

	$form['volunteerdiscussant'] = array(
		'#type' => 'checkboxes',
		'#options' => array(
						'y' => 'I would like to volunteer as Discussant',
						),
		'#prefix' => '<div class="container-inline"><span style="vertical-align:top;">', 
		'#default_value' => isset( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant'] ) ? $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant'] : array(), 
		);

if ( SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1 ) {

	$form['volunteerdiscussant_networks'] = array(
		'#type' => 'select',
		'#options' => $list_of_networks,
		'#prefix' => '</span>', 
		'#suffix' => '</div>', 
		'#multiple' => TRUE,
		'#size' => 3,
		'#description' => '<i>Use CTRL key to select multiple networks.</i>',

		'#states' => array(
			'visible' => array(
					':input[name="volunteerdiscussant[y]"]' => array('checked' => TRUE),
				),
			),

		'#default_value' => isset( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks'] ) ? $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks'] : NULL, 
		);

} else {

		$form['volunteerdiscussant_networks'] = array(
			'#type' => 'textfield',
			'#title' => 'Proposed network',
			'#prefix' => '</span><div style="display:none;">', 
			'#suffix' => '</div></div>', 
			'#default_value' => SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID),
			);

}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + +

	// ENGLISH LANGUAGE COACH

if ( SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1 ) {

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">English Language Coach</span>',
		);

	$form['coachpupil'] = array(
		'#type' => 'radios',
		'#options' => getArrayOfLanguageCoach(),
		'#default_value' => ( isset( $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] ) ) ? $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] : 2,
		'#prefix' => '<div class="container-inline" style="float:left;width:46%;">', 
		'#suffix' => '</div>', 
		);

	$form['coachpupil_networks'] = array(
		'#type' => 'select',
		'#options' => $list_of_networks,
		'#multiple' => TRUE, 
		'#size' => 3, 
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#description' => '<br><i>Use CTRL key to select multiple networks.</i>',

		'#states' => array(
							'invisible' => array( ':input[name="coachpupil"]' => array('value' => 2 ), ),
						),
		'#default_value' => isset( $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks'] ) ? $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks'] : NULL, 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

} else {

		$form['coachpupil'] = array(
			'#type' => 'textfield',
			'#title' => 'English Language Coach',
			'#prefix' => '<div style="display:none;">', 
			'#suffix' => '</div>', 
			'#default_value' => 2, 
			);

}

	// + + + + + + + + + + + + +

	// SUBMIT BUTTONS

	$form['submit_back'] = array(
		'#type' => 'submit',
		'#value' => 'Back',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Next',
		);

	// EXISTING USERS
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br><br><br><br>',
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_chairdiscussantpool_edit_form_validate( $form, &$form_state ) {
	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit'] ) {

		// controle als men chair aanklikt ook echt een netwerk wordt geselecteerd
		if ($form_state['values']["volunteerchair"]['y'] === 'y') {
			if ( count($form_state['values']["volunteerchair_networks"]) == 0 ) {
				form_set_error('volunteerchair', 'Please select a network or uncheck the field \'I would like to volunteer as Chair\'.');
			}
		}

		// controle als men discussant aanklikt ook echt een netwerk wordt geselecteerd
		if ($form_state['values']["volunteerdiscussant"]['y'] === 'y') {
			if ( count($form_state['values']["volunteerdiscussant_networks"]) == 0 ) {
				form_set_error('volunteerdiscussant', 'Please select a network or uncheck the field \'I would like to volunteer as Discussant\'.');
			}
		}

		// controle als men language aanklikt ook echt een netwerk wordt geselecteerd
		if ($form_state['values']["coachpupil"] != 2 ) {
			if ( count($form_state['values']["coachpupil_networks"]) == 0 ) {
				form_set_error('coachpupil', 'Please select a network or select \'not applicable\' at English language coach.');
			}
		}

	}
}

/**
 * TODOEXPLAIN
 */
function preregister_chairdiscussantpool_edit_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair'] = $form_state['values']["volunteerchair"];
	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks'] = $form_state['values']["volunteerchair_networks"];

	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant'] = $form_state['values']["volunteerdiscussant"];
	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks'] = $form_state['values']["volunteerdiscussant_networks"];

	$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] = $form_state['values']["coachpupil"];
	$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks'] = $form_state['values']["coachpupil_networks"];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		if ( $_SESSION['storage']['what'] == 'spectator' ) {
			$form_state['storage']['step'] = 'preregister_typeofregistration_form';
		} elseif ( $_SESSION['storage']['what'] == 'session' ) {
			$form_state['storage']['step'] = 'preregister_session_list_form';
		} else {
			$form_state['storage']['step'] = 'preregister_registerpaper_preview_form';
		}
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] ) {
		$form_state['storage']['step'] = 'preregister_chairdiscussantpool_preview_form';
	} else {
		die('ERROR 325684: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}
