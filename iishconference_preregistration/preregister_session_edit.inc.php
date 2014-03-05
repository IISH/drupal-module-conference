<?php 
/**
 * TODOEXPLAIN
 */
function preregister_session_edit_form( $form, &$form_state ) {
	$ct=0;

	// SESSION LIST
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 5 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">Session info</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['sessionname'] = array(
		'#type' => 'textfield',
		'#title' => 'Session name',
		'#size' => 40,
		'#required' => TRUE,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_sessionname'] ) ? $_SESSION['storage']['preregistersession_sessionname'] : NULL, 
		);

	$form['sessionabstract'] = array(
		'#type' => 'textarea',
		'#title' => 'Abstract',
		'#description' => '<em>(max. 1.000 words)</em>',
		'#rows' => 3,
		'#required' => TRUE,
		'#default_value' => isset( $_SESSION['storage']['preregistersession_sessionabstract'] ) ? $_SESSION['storage']['preregistersession_sessionabstract'] : '', 
		);

	// 
	$list_of_networks = getArrayOfNetworks();

	if ( SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1 ) {
		$form['sessioninnetwork'] = array(
			'#title' => 'Network ' . getSetting('required'),
			'#type' => 'select',
			'#options' => $list_of_networks,
			'#prefix' => '<div class="container-inline">', 
			'#suffix' => '</div>', 
			'#size' => 4, 
			'#default_value' => isset( $_SESSION['storage']['preregistersession_sessioninnetwork'] ) ? $_SESSION['storage']['preregistersession_sessioninnetwork'] : NULL, 
		);

	} else {
		// NO NETWORK

		$form['sessioninnetwork'] = array(
			'#type' => 'textfield',
			'#title' => 'Proposed network',
			'#prefix' => '<div style="display:none;">', 
			'#suffix' => '</div>', 
			'#default_value' => SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID),
			);

	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Participants:</span>',
		);

	$counter = 0;
	foreach (getArrayOfSessionParticipants( $_SESSION['storage']['preregistersession_sessionid'] ) as $nr => $name) {
		if ( $counter == 0 ) {

			$form['submit_participant_' . $nr] = array(
				'#type' => 'submit',
				'#value' => $name,
				'#suffix' => '<br><br>', 
				'#prefix' => '', 
			);

		} else {

			if ( $counter == 1) {
				$prefix = ' &nbsp;or<br><br>';
			} else {
				$prefix = '<br>';
			}

			$form['submit_participant_' . $nr] = array(
				'#name' => 'submit_participant_' . $nr,
				'#type' => 'submit',
				'#value' => 'Edit',
				'#prefix' => $prefix, 
//				'#suffix' => '', 
				'#suffix' => ' ' . $name . '<br><br>',
			);

//			$form['remove_participant_' . $nr] = array(
//				'#name' => 'remove_participant_' . $nr,
//				'#type' => 'submit',
//				'#value' => 'Remove',
//				'#prefix' => '',
//				'#suffix' => ' ' . $name . '<br><br>',
//			);

		}

		$counter++;
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	$form['submit_cancel'] = array(
		'#type' => 'submit',
		'#value' => 'Back to list of sessions',
		'#submit' => array('preregister_session_edit_form_cancel'),
		'#limit_validation_errors' => array(),
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit_back'] = array(
		'#type' => 'submit',
		'#value' => 'Save session',
		);

	if ( $_SESSION['storage']['preregistersession_sessionid'] > 0 ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '&nbsp; &nbsp; &nbsp;',
			);

		$form['submit_nextstep'] = array(
			'#type' => 'submit',
			'#value' => 'Go to step 7 (Chair / Discussant pool)',
			);

		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<br>&nbsp;<br>&nbsp;<br>',
			);

		$form['submit_remove'] = array(
			'#type' => 'submit',
			'#value' => 'Remove session',
			'#limit_validation_errors' => array(),
			);
	}

	return $form;
}

/**
* Custom cancel button callback.
*/
function preregister_session_edit_form_cancel($form, &$form_state) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$form_state['storage']['step'] = 'preregister_session_list_form';
}

/**
 * TODOEXPLAIN
 */
function preregister_session_edit_form_validate( $form, &$form_state ) {
	// check if a network is selected
	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_back'] ) {
		if ( $form_state['values']['sessioninnetwork'] == 0 ) {
			form_set_error('sessioninnetwork', 'The network field is required.');
		}
	} elseif ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_cancel'] ) {
		// no network check when cancel
	} else {
		$needle = 'edit-submit-participant-';
		if ( strpos( $form_state['clicked_button']['#id'], $needle ) !== false ) {
			if ( $form_state['values']['sessioninnetwork'] == 0 ) {
				form_set_error('sessioninnetwork', 'The network field is required.');
			}
		}
	}

}

/**
 * TODOEXPLAIN
 */
function preregister_session_edit_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];
	$_SESSION['storage']['preregistersession_sessionname'] = isset($form_state['values']["sessionname"]) ? $form_state['values']["sessionname"] : '';
	$_SESSION['storage']['preregistersession_sessionabstract'] = isset($form_state['values']["sessionabstract"]) ? $form_state['values']["sessionabstract"] : '';
	$_SESSION['storage']['preregistersession_sessioninnetwork'] = isset($form_state['values']["sessioninnetwork"]) ? $form_state['values']["sessioninnetwork"] : NULL;

	if ( !isset($values['submit_back']) ) {
		$values['submit_back'] = '';
	}
	if ( !isset($values['submit_remove']) ) {
		$values['submit_remove'] = '';
	}
	if ( !isset($values['submit_nextstep']) ) {
		$values['submit_nextstep'] = '';
	}

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// save session
		saveSessionData($_SESSION['storage']['preregistersession_sessionid']);

		// BACK
		$form_state['storage']['step'] = 'preregister_session_list_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_remove'] ) {
		// REMOVE
		$form_state['storage']['step'] = 'preregister_session_remove_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_nextstep'] ) {
		// NEXT STEP
		$form_state['storage']['step'] = 'preregister_chairdiscussantpool_edit_form';
	} else {
		$needleEdit = 'edit-submit-participant-';

		if ( strpos($form_state['clicked_button']['#id'], $needleEdit) !== false ) {
			// save session
			saveSessionData($_SESSION['storage']['preregistersession_sessionid']);

			$participantId = $form_state['clicked_button']['#id'];
			$participantId = str_replace($needleEdit, '', $participantId);

			// SET PARTICIPANT ID
			$_SESSION['storage']['preregistersession_participantid'] = $participantId;

			// PRELOAD PARTICIPANT VALUES
			loadParticipantData($participantId);

			// go to session edit form
			$form_state['storage']['step'] = 'preregister_session_participant_form';
		} else {
			die('ERROR 85695874: unknown button clicked ' . $form_state['clicked_button']['#id'] . ' - ' . $form_state['clicked_button']['#value']);
		}
	}
}

