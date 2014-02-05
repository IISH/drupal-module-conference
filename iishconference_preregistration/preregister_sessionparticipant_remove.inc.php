<?php 
/**
 * TODOEXPLAIN
 */
function preregister_session_participant_remove_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">Please confirm deletion of participant</span>',
		);

	$arrParticipant = getDetailsAsArray('SELECT * FROM users WHERE user_id=' . $_SESSION['storage']['preregistersession_participantid'], array("firstname", "lastname"));
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => 'Participant: ' . $arrParticipant["firstname"] . ' ' . $arrParticipant["lastname"],
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br><br><em>(If the participant was added to the database by someone else or this participant is you, then the participant will only be removed from the session).</em>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br>',
		);

	$form['submit_back'] = array(
		'#type' => 'submit',
		'#value' => 'Back',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit_remove'] = array(
		'#type' => 'submit',
		'#value' => 'Delete',
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_session_participant_remove_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_session_participant_form';

		// PRELOAD PARTICIPANT VALUES
		loadParticipantData( $_SESSION['storage']['preregistersession_participantid'] );

	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_remove'] ) {
		// REMOVE PARTICIPANT

		removeParticipantAddedBy( $_SESSION['storage']['preregistersession_participantid'], getIdLoggedInUser(), $_SESSION['storage']['preregistersession_sessionid'] );

		$form_state['storage']['step'] = 'preregister_session_edit_form';
	}
}

