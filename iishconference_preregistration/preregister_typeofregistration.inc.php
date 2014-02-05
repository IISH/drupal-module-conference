<?php 
/**
 * TODOEXPLAIN
 */
function preregister_typeofregistration_form( $form, &$form_state ) {
	$ct = 0;
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 4 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	// show / hide options
	$options = array();
	if ( getSetting('hide_add_single_paper_after') > date("Y-m-d") ) {
		$options['paper'] = 'I would like to propose a SINGLE paper<br><em>(If you have multiple papers, please register one paper and then contact the congress secretary)</em><br><br>';
	}

	// 
	$oUser = new class_conference_user( getIdLoggedInUser() );

	if ( !$oUser->hasPaperWithoutSession() ) {
		$options['spectator'] = 'I would like to register as a spectator<br><br>';
	}

	if ( getSetting('hide_add_session_after') > date("Y-m-d") ) {
		$options['session'] = 'I\'m an organizer and I would like to propose a session (including MULTIPLE participants and papers!)<br><br>';
	}

	// 
	if ( $oUser->hasPaperWithoutSession() ) {
		$options['skip'] = 'Go to step 7 (Chair / Discussant pool)<br><br>';
	}

	$form['what'] = array(
		'#type' => 'radios',
		'#title' => 'How would you like to pre-register?',
		'#options' => $options,
		'#default_value' => isset( $_SESSION['storage']['what'] ) ? $_SESSION['storage']['what'] : NULL,
		);

	if ( getSetting('hide_add_single_paper_after') <= date("Y-m-d") ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<font color=red>It is no longer possible to pre-register a paper.<br>You can still pre-register for the conference as listener.</font>',
			);
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// 
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
function preregister_typeofregistration_form_validate( $form, &$form_state ) {
	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit'] ) {
		if ( $form_state['values']['what'] == '' ) {
			form_set_error('what', 'Please select how you would like to pre-register?.');
		}
	}
}

/**
 * TODOEXPLAIN
 */
function preregister_typeofregistration_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	$_SESSION['storage']['what'] = $form_state['values']['what'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		$form_state['storage']['step'] = 'preregister_personalinfo_preview_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] ) {

		$_SESSION['storage']['choice'] = $form_state['values']["what"];

		if ( $form_state['values']['what'] == 'paper' ) {
			$form_state['storage']['step'] = 'preregister_registerpaper_edit_form';
		} elseif ( $form_state['values']['what'] == 'spectator' || $form_state['values']['what'] == 'skip' ) {
			$form_state['storage']['step'] = 'preregister_chairdiscussantpool_edit_form';
		} elseif ( $form_state['values']['what'] == 'session' ) {
			// save/update naw/papers/participantdate
			saveUpdateParticipant();
			saveUpdateParticipantDate();

			$form_state['storage']['step'] = 'preregister_session_list_form';
		} else {
			die('ERROR 415174: unknown choice ' . $form_state['values']['what']);
		}
	} else {
		die('ERROR 464174: unknown button ' . $form_state['clicked_button']['#value']);
	}
}

