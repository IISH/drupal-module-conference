<?php 
/**
 * TODOEXPLAIN
 */
function preregister_session_participant_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 5 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">Add participant</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	// als zelf ingevuld dan wel wijzigen
	if ( $_SESSION['storage']['preregistersession_participantid'] > 0 ) {
		$arrParticipant = getDetailsAsArray('SELECT * FROM users WHERE user_id=' . $_SESSION['storage']['preregistersession_participantid'], array('added_by'));
		if ( $arrParticipant["added_by"] == getIdLoggedInUser() ) {
			$emailReadonly = array();
		} else {
			$emailReadonly = array('readonly' => 'readonly');
		}
	} else {
		$emailReadonly = array();
	}

	$form['addparticipantemail'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#required' => TRUE,
		'#size' => 40,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantemail'] ) ? trim($_SESSION['storage']['preregistersession_participantemail']) : NULL, 
		'#attributes' => $emailReadonly,
		);

	$types_options = getArrayOfParticipantTypes( getSetting('event_id') );

	$form['addparticipanttype'] = array(
		'#title' => 'Type',
		'#type' => 'select',
		'#size' => 5,
		'#required' => TRUE,
		'#options' => $types_options,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#multiple' => TRUE, 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participanttype'] ) ? $_SESSION['storage']['preregistersession_participanttype'] : NULL, 
		);

	$form['addparticipantfirstname'] = array(
		'#type' => 'textfield',
		'#title' => 'First name',
		'#required' => TRUE,
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantfirstname'] ) ? $_SESSION['storage']['preregistersession_participantfirstname'] : NULL, 
		'#attributes' => $emailReadonly,
		);

	$form['addparticipantlastname'] = array(
		'#type' => 'textfield',
		'#title' => 'Last name',
		'#required' => TRUE,
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantlastname'] ) ? $_SESSION['storage']['preregistersession_participantlastname'] : NULL, 
		'#attributes' => $emailReadonly,
		);

	$form['addparticipantstudent'] = array(
		'#type' => 'checkboxes',
		'#options' => array(
						'y' => 'Please check if participant is a (PhD) student',
						),
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantstudent'] ) ? $_SESSION['storage']['preregistersession_participantstudent'] : array(), 
		);

	$form['addparticipantpapertitle'] = array(
		'#type' => 'textfield',
		'#title' => 'Paper title',
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantpapertitle'] ) ? $_SESSION['storage']['preregistersession_participantpapertitle'] : NULL, 
		);

	$form['addparticipantpaperabstract'] = array(
		'#type' => 'textarea',
		'#title' => 'Paper abstract',
		'#description' => '<em>(max. 500 words)</em>',
		'#rows' => 3,
		'#default_value' => isset( $_SESSION['storage']['preregistersession_participantpaperabstract'] ) ? $_SESSION['storage']['preregistersession_participantpaperabstract'] : NULL, 
		);

	if ( SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1 ) {

		$form['addparticipantcv'] = array(
			'#type' => 'textarea',
			'#title' => 'Curriculum Vitae',
			'#description' => '<em>(max. 200 words)</em>',
			'#rows' => 2,
			'#default_value' => isset( $_SESSION['storage']['preregistersession_participantcv'] ) ? $_SESSION['storage']['preregistersession_participantcv'] : NULL, 
			);

	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	$form['submit_cancel'] = array(
		'#type' => 'submit',
		'#value' => 'Back',
		'#submit' => array('preregister_session_participant_form_cancel'),
		'#limit_validation_errors' => array(),
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit_next'] = array(
		'#type' => 'submit',
		'#value' => 'Save participant',
		);

	if ( $_SESSION['storage']['preregistersession_participantid'] > 0 ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '&nbsp; &nbsp; &nbsp;',
			);

		$form['submit_remove'] = array(
			'#type' => 'submit',
			'#value' => 'Remove participant',
			'#limit_validation_errors' => array(),
			);
	}

	return $form;
}

/**
* Custom cancel button callback.
*/
function preregister_session_participant_form_cancel($form, &$form_state) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$form_state['storage']['step'] = 'preregister_session_edit_form';
}

/**
 * TODOEXPLAIN
 */
function preregister_session_participant_form_validate( $form, &$form_state ) {
	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_next'] ) {

		$email_existing_users_trimmed = trim($form_state['values']['addparticipantemail']);
		// EMAIL IS VERPLICHT
		if ( $email_existing_users_trimmed == '' ) {
			form_set_error('addparticipantemail', 'E-mail field is required.');
		// CHECK EMAIL IS VALID
		} elseif ( !valid_email_address( $email_existing_users_trimmed ) ) {
			form_set_error('addparticipantemail', 'The e-mail address appears to be invalid.');
		// CONTROLEER OF EMAIL AL BESTAAT (uitgezonderd huidige record)
		} elseif ( $_SESSION['storage']['preregistersession_participantid'] != 0 && checkIfEmailAlreadyExists( $form_state['values']['addparticipantemail'], $_SESSION['storage']['preregistersession_participantid'] ) > 0 ) {
			form_set_error('addparticipantemail', 'E-mail already exists in the database.');
		// controleer of userid niet al een andere sessie zit als author
		} elseif ( getSetting('multiple_papers_per_author') == 0 && checkIfParticipantIsMultipleAuthor($_SESSION['storage']['preregistersession_participantid'], $form_state['values']['addparticipanttype'], $_SESSION['storage']['preregistersession_sessionid']) ) {
			form_set_error('addparticipanttype', 'Participant is already an author in another session. It is only allowed to be author in one session.');
		}

		// controler niet toegestane kombinatie types
		if ( isAllowedCombination( $form_state['values']['addparticipanttype'] ) == 0 ) {
			form_set_error('addparticipanttype', '(co)Author is not allowed in combination with Chair, Discussant and Co-author.');
		}

		// check if paper is required
		if ( isPaperRequired( $form_state['values']['addparticipanttype'] ) ) {
			// papertitle required if author or co-author
			if ( trim($form_state['values']['addparticipantpapertitle']) == '' ) {
				form_set_error('addparticipantpapertitle', 'Paper title is required when (co)author.');
			}

			// paperabstract required if author or co-author
			if ( trim($form_state['values']['addparticipantpaperabstract']) == '' ) {
				form_set_error('addparticipantpaperabstract', 'Paper abstract is required when (co)author.');
			}
		}
	}
}

/**
 * TODOEXPLAIN
 */
function preregister_session_participant_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( !isset($form_state['values']["addparticipantemail"]) ) {
		$form_state['values']["addparticipantemail"] = '';
	}

	$_SESSION['storage']['preregistersession_participantemail'] = trim($form_state['values']["addparticipantemail"]);

	if ( !isset($form_state['values']["addparticipanttype"]) ) {
		$form_state['values']["addparticipanttype"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participanttype'] = $form_state['values']["addparticipanttype"];

	if ( !isset($form_state['values']["addparticipantfirstname"]) ) {
		$form_state['values']["addparticipantfirstname"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participantfirstname'] = trim($form_state['values']["addparticipantfirstname"]);

	if ( !isset($form_state['values']["addparticipantlastname"]) ) {
		$form_state['values']["addparticipantlastname"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participantlastname'] = trim($form_state['values']["addparticipantlastname"]);

	if ( !isset($form_state['values']["addparticipantpapertitle"]) ) {
		$form_state['values']["addparticipantpapertitle"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participantpapertitle'] = trim($form_state['values']["addparticipantpapertitle"]);

	if ( !isset($form_state['values']["addparticipantpaperabstract"]) ) {
		$form_state['values']["addparticipantpaperabstract"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participantpaperabstract'] = $form_state['values']["addparticipantpaperabstract"];

	$_SESSION['storage']['preregistersession_participantstudent'] = isset($form_state['values']["addparticipantstudent"]) ? $form_state['values']["addparticipantstudent"] : NULL;

	if ( !isset($form_state['values']["addparticipantcv"]) ) {
		$form_state['values']["addparticipantcv"] = NULL;
	}
	$_SESSION['storage']['preregistersession_participantcv'] = $form_state['values']["addparticipantcv"];

	if ( !isset($values['submit_next']) ) {
		$values['submit_next'] = '';
	}

	if ( !isset($values['submit_remove']) ) {
		$values['submit_remove'] = '';
	}

	if ( $form_state['clicked_button']['#value'] == $values['submit_next'] ) {
		// SAVE PARTICIPANT
		saveParticipant();

		$form_state['storage']['step'] = 'preregister_session_edit_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_remove'] ) {
		// REMOVE PARTICIPANT
		$form_state['storage']['step'] = 'preregister_session_participant_remove_form';
	} else {
		die('ERROR 2514785: unknown button clicked ' . $form_state['clicked_button']['#id'] . ' - ' . $form_state['clicked_button']['#value']);
	}
}

